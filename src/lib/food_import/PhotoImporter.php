<?php

require_once 'lib/food_import/FoodParserBase.php';
require_once 'lib/food_import/GeminiVisionClient.php';
require_once 'lib/food_import/NutritionSanity.php';

/*

Reads a food off pictures of its packaging (import option C).

Everything vendor parsers get from markup is printed on the pack itself, so a
vision model is the parser here. The answer is constrained by a response schema,
which is what keeps this class free of text parsing: the model can only fill in
the fields below, and `null` is its only legal way out of a value it can not read.

Two schema fields are not food data and never reach the record:

  basis  which column of the nutrition table was read. Listed before the numbers,
         so the model has to commit to a column before it writes a single value
  notes  what the model could not read - surfaced to the user as a warning

Nothing here is saved: the food goes into the new-entry form, and the user
reviews it before pressing "Add entry".

*/
class PhotoImporter
{

  const PROMPT_FILE     = 'data/food_import/photo_prompt.md';
  const DEFAULT_MODEL   = 'gemini-3.6-flash';   // must support generateContent, the Live model does not

  const MIME_ALLOWED    = ['image/jpeg', 'image/png', 'image/webp'];
  const MAX_IMAGE_BYTES = 4194304;   // 4 MB per picture, decoded
  const MAX_TOTAL_BYTES = 8388608;   // 8 MB per request

  const NUTRIENTS       = ['fat', 'saturatedFat', 'carbs', 'sugar', 'fibre', 'amino', 'salt'];


  // Returns ['food' => <food record>, 'warnings' => [...]]. Throws with a
  // user-facing message if the pictures or the answer are unusable

  public static function fromImages( array $base64Images ) : array
  {
    $images = self::decodeImages( $base64Images );
    $count  = count( $images );

    $data = GeminiVisionClient::extract(
      config::get('photoImport.model') ?: self::DEFAULT_MODEL,
      file_get_contents( self::PROMPT_FILE ),
      "Extract the product data from these $count pictures of one product.",
      $images,
      self::responseSchema());

    if( config::get('photoImport.debug'))
      error_log('PhotoImporter: ' . json_encode( $data ));

    return self::fromAnswer( $data );
  }


  /* Model answer -> ['food' => ..., 'warnings' => ...].

     Public because it is the seam the tests use: a recorded answer replays the
     whole mapping without calling the model (see tools/test_photo_import.php). */

  public static function fromAnswer( array $data ) : array
  {
    $food = self::mapFood( $data );

    return ['food' => $food, 'warnings' => self::warnings( $data, $food )];
  }


  // Decode and check the pictures before a single byte goes to google

  private static function decodeImages( array $base64Images ) : array
  {
    $maxImages = (int) (config::get('photoImport.maxImages') ?: 3);

    if( count($base64Images) > $maxImages )
      throw new Exception("Too many pictures (max $maxImages).");

    $images = [];
    $total  = 0;

    foreach( $base64Images as $base64 )
    {
      $base64 = preg_replace('#^data:image/[a-z]+;base64,#i', '', trim( (string) $base64));
      $bytes  = base64_decode( $base64, true);   // strict: junk is rejected here, not by google

      if( $bytes === false || $bytes === '')
        throw new Exception('One of the pictures could not be read.');

      $total += strlen( $bytes );

      if( strlen($bytes) > self::MAX_IMAGE_BYTES || $total > self::MAX_TOTAL_BYTES )
        throw new Exception('The pictures are too large together. Take fewer or smaller ones.');

      $info = getimagesizefromstring( $bytes );   // also keeps non-image payloads out

      if( $info === false || ! in_array( $info['mime'], self::MIME_ALLOWED))
        throw new Exception('Only jpeg, png and webp pictures can be read.');

      if( config::get('photoImport.debug'))
        error_log('PhotoImporter: image ' . round( strlen($bytes) / 1024) . ' kB, ' . $info[0] . 'x' . $info[1]);

      $images[] = ['mime' => $info['mime'], 'data' => $bytes];
    }

    if( ! $images )
      throw new Exception('Take at least one picture.');

    return $images;
  }


  /* What the model may answer, in gemini's OpenAPI subset.

     Every field is required AND nullable: together that is what makes "unknown
     -> null" reliable, because the model must emit the key and null is then its
     only legal alternative to inventing a value. */

  private static function responseSchema() : array
  {
    $nutrients = [
      'fat'          => ['type' => 'number', 'nullable' => true],
      'saturatedFat' => ['type' => 'number', 'nullable' => true, 'description' => 'davon gesättigte Fettsäuren'],
      'carbs'        => ['type' => 'number', 'nullable' => true],
      'sugar'        => ['type' => 'number', 'nullable' => true, 'description' => 'davon Zucker'],
      'fibre'        => ['type' => 'number', 'nullable' => true, 'description' => 'Ballaststoffe'],
      'amino'        => ['type' => 'number', 'nullable' => true, 'description' => 'Eiweiß / Protein'],
      'salt'         => ['type' => 'number', 'nullable' => true, 'description' => 'Salz in g, never sodium in mg'],
    ];

    $properties = [
      'productName' => ['type' => 'string',  'nullable' => true, 'description' => 'Full product title exactly as printed on the front'],
      'name'        => ['type' => 'string',  'nullable' => true, 'description' => 'Short everyday name, brand plus kind, max 30 characters'],
      'vendor'      => ['type' => 'string',  'nullable' => true, 'description' => 'Brand or store brand as printed'],
      'weight'      => ['type' => 'string',  'nullable' => true, 'description' => 'Net quantity with unit as printed: 800g, 330ml, 0,75l'],
      'pieces'      => ['type' => 'integer', 'nullable' => true, 'description' => 'Piece count of a multipack'],
      'packaging'   => ['type' => 'string',  'nullable' => true, 'description' => 'Material in contact with the food, outer to inner, comma separated: cardboard, alu, plastic, glass, rubber, none'],
      'basis'       => ['type' => 'string',  'nullable' => true, 'enum' => ['per100g', 'per100ml', 'perPortion'],
                        'description' => 'Which column of the nutrition table the values below were read from'],
      'energyKj'    => ['type' => 'number',  'nullable' => true, 'description' => 'Energy in kJ, same column as basis'],
      'calories'    => ['type' => 'number',  'nullable' => true, 'description' => 'Energy in kcal, same column as basis'],

      'nutritionalValues' => [
        'type'             => 'object',
        'propertyOrdering' => self::NUTRIENTS,
        'properties'       => $nutrients,
        'required'         => self::NUTRIENTS
      ],

      'certificates' => [
        'type'             => 'object',
        'propertyOrdering' => ['NutriScore', 'bio', 'vegan'],
        'properties'       => [
          'NutriScore' => ['type' => 'string',  'nullable' => true, 'enum' => ['A', 'B', 'C', 'D', 'E'], 'description' => 'Only if the Nutri-Score logo is visible'],
          'bio'        => ['type' => 'boolean', 'nullable' => true, 'description' => 'Only for a real organic mark'],
          'vegan'      => ['type' => 'boolean', 'nullable' => true, 'description' => 'V-Label, vegan or vegetarian claim'],
        ],
        'required' => ['NutriScore', 'bio', 'vegan']
      ],

      'ingredients' => ['type' => 'string', 'nullable' => true, 'description' => 'The "Zutaten:" list verbatim, one line'],
      'allergy'     => ['type' => 'string', 'nullable' => true, 'description' => 'The "Enthält: ..." sentence, verbatim'],
      'mayContain'  => ['type' => 'string', 'nullable' => true, 'description' => 'The "Kann Spuren von ..." sentence, verbatim'],
      'price'       => ['type' => 'number', 'nullable' => true, 'description' => 'Only if a price label is legible in a picture'],
      'notes'       => ['type' => 'string', 'nullable' => true, 'description' => 'What could not be read or was ambiguous'],
    ];

    return [
      'type'             => 'object',
      'propertyOrdering' => array_keys( $properties ),
      'properties'       => $properties,
      'required'         => array_keys( $properties )
    ];
  }


  // Model answer -> food record. Empty values are dropped, so the form keeps its
  // defaults and FoodYamlWriter omits the key

  private static function mapFood( array $data ) : array
  {
    $food = [];

    // Ocr text arrives with line breaks in it

    foreach( ['name', 'productName', 'vendor', 'packaging', 'allergy'] as $field )
    {
      $text = FoodParserBase::cleanText( (string) ($data[$field] ?? ''));

      if( $text !== '')
        $food[$field] = $text;
    }

    // The trace note belongs in mayContain even when the model left it glued to
    // the ingredients (same split the vendor parsers use)

    [$ingredients, $trace] = FoodParserBase::splitIngredients( (string) ($data['ingredients'] ?? ''));

    $mayContain = FoodParserBase::cleanText( (string) ($data['mayContain'] ?? '')) ?: $trace;

    if( $ingredients !== '')  $food['ingredients'] = $ingredients;
    if( $mayContain  !== '')  $food['mayContain']  = $mayContain;

    // "500 g ℮" -> "500g", so the form can split number and unit

    $weight = str_replace(['℮', ' ', "\u{00A0}"], '', (string) ($data['weight'] ?? ''));

    if( $weight !== '')  $food['weight'] = $weight;

    $pieces = (int) ($data['pieces'] ?? 0);

    if( $pieces > 0 )  $food['pieces'] = $pieces;

    $price = self::toFloat( $data['price'] ?? null);

    if( $price !== null )  $food['price'] = $price;

    // Some packs print kJ only: 1 kcal is 4.184 kJ

    $calories = self::toFloat( $data['calories'] ?? null);
    $energyKj = self::toFloat( $data['energyKj'] ?? null);

    if( $calories === null && $energyKj !== null )
      $calories = round( $energyKj / 4.184 );

    if( $calories !== null )  $food['calories'] = $calories;

    $nutrients = [];

    foreach( self::NUTRIENTS as $field )
    {
      $value = self::toFloat( $data['nutritionalValues'][$field] ?? null);

      if( $value !== null )
        $nutrients[$field] = $value;
    }

    if( $nutrients )  $food['nutritionalValues'] = $nutrients;

    // Only what the pack actually shows: a false must not land in the yml

    $certificates = [];
    $nutriScore   = strtoupper( trim( (string) ($data['certificates']['NutriScore'] ?? '')));

    if( preg_match('/^[A-E]$/', $nutriScore))     $certificates['NutriScore'] = $nutriScore;
    if( ! empty( $data['certificates']['bio']))   $certificates['bio']        = true;
    if( ! empty( $data['certificates']['vegan'])) $certificates['vegan']      = true;

    if( $certificates )  $food['certificates'] = $certificates;

    return $food;
  }


  // What the user should look at before saving, most important first

  private static function warnings( array $data, array $food ) : array
  {
    $out   = [];
    $basis = $data['basis'] ?? null;

    if( $basis === 'perPortion')
      $out[] = 'The pack showed per-portion values only, not per 100 g — check every number before saving.';

    // A per-100-ml table on a pack sold by weight (or the other way round)

    preg_match('/([a-z]+)$/i', $food['weight'] ?? '', $match);

    $unit   = strtolower( $match[1] ?? '');
    $liquid = $unit === 'ml' || $unit === 'l';

    if( $unit !== '')
    {
      if( $basis === 'per100ml' && ! $liquid )
        $out[] = "The table was read per 100 ml but the pack says $unit — check the values.";
      elseif( $basis === 'per100g' && $liquid )
        $out[] = "The table was read per 100 g but the pack is sold in $unit — check the values.";
    }

    // kJ and kcal are two readings of the same row, so they check each other

    $calories = self::toFloat( $data['calories'] ?? null);
    $energyKj = self::toFloat( $data['energyKj'] ?? null);

    if( $calories === null && $energyKj !== null )
      $out[] = 'Only kJ was printed, the kcal value was calculated from it.';
    elseif( $calories > 0 && $energyKj !== null && abs( $calories - $energyKj / 4.184) / $calories > 0.15 )
      $out[] = 'kJ and kcal on the label do not match — one of them was misread.';

    $out = array_merge( $out, NutritionSanity::warnings( $food ));

    $notes = FoodParserBase::cleanText( (string) ($data['notes'] ?? ''));

    if( $notes !== '')
      $out[] = "Could not read: $notes";

    return $out;
  }


  // The schema asks for numbers, but a model can still answer "10,2" or "< 0,5"

  private static function toFloat( $value ) : ?float
  {
    if( is_int($value) || is_float($value))
      return (float) $value;

    if( ! is_string($value) || ! preg_match('/-?\d+(?:[.,]\d+)?/', $value, $match))
      return null;

    return (float) str_replace(',', '.', $match[0]);
  }
}

?>
