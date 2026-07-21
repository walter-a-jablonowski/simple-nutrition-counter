<?php

require_once 'lib/food_import/FoodParser.php';

/*

Parser for REWE product detail pages (www.rewe.de/shop/p/...).

REWE embeds the full product data as JSON inside the page HTML. We read the
nutrient values from the `nutritionFacts` block (GS1 nutrient codes, per 100 g
basis) and the remaining metadata from individual JSON fields. A German nutrient
table in the HTML serves as a fallback if the JSON block is missing.

*/
class ReweParser implements FoodParser
{

  // GS1 nutrient code -> our nutritionalValues key

  const NUTRIENT_MAP =
  [
    'FAT'    => 'fat',
    'FASAT'  => 'saturatedFat',
    'CHOAVL' => 'carbs',
    'SUGAR-' => 'sugar',
    'FIBTG'  => 'fibre',
    'PRO-'   => 'amino',
    'SALTEQ' => 'salt',
  ];

  // German table label -> our key (HTML fallback)

  const LABEL_MAP =
  [
    'Fett'                              => 'fat',
    'davon gesättigte Fettsäuren'       => 'saturatedFat',
    'Kohlenhydrate'                     => 'carbs',
    'davon Zucker'                      => 'sugar',
    'Ballaststoffe'                     => 'fibre',
    'Eiweiß'                            => 'amino',
    'Salz'                              => 'salt',
  ];


  public function matches( string $html, ?string $url ) : bool
  {
    if( $url && stripos($url, 'rewe.de') !== false )
      return true;

    return stripos($html, 'rewe-static.de') !== false || stripos($html, '"nutritionFacts"') !== false;
  }


  public function parse( string $html, ?string $url ) : array
  {
    $productName = $this->jsonString($html, 'productName');

    if( $productName === null )
      throw new Exception('Could not find product data in the REWE page. Please paste the full page HTML.');

    // Short display name (used as record name / file name): prefer the regulated
    // product name (e.g. "Linseneintopf"), fall back to the full title

    $name = $this->jsonString($html, 'regulatedProductName') ?: $productName;

    // Ingredients often bundle a "Kann Spuren von ... enthalten" allergen note,
    // which belongs in mayContain

    [$ingredients, $mayContain] = $this->splitIngredients( $this->detailValue($html, 'ingredientStatement') ?? '');

    $food =
    [
      'name'         => $name,
      'productName'  => $productName,
      'vendor'       => 'Rewe',
      'url'          => $url ?: ($this->jsonString($html, 'url') ?? ''),
      'certificates' => $this->parseCertificates($html),
      'ingredients'  => $ingredients,
      'mayContain'   => $mayContain,
      'price'        => $this->parsePrice($html),
      'weight'       => $this->jsonString($html, 'grammage') ?? '',
      'nutritionalValues' => [],
    ];

    // Nutrients (per 100 g), calories separate

    $nutrients = $this->parseNutrients($html);

    $food['calories']          = $nutrients['calories'] ?? null;
    $food['nutritionalValues'] = $nutrients['values'];

    return $food;
  }


  // --- nutrients ---------------------------------------------------------

  private function parseNutrients( string $html ) : array
  {
    $facts = $this->extractJsonArray($html, 'nutritionFacts');

    if( $facts !== null )
    {
      // Pick the per-100 g basis block

      foreach( $facts as $block )
      {
        if( ($block['servingSize']['value'] ?? null) == 100 )
          return $this->mapNutrientBlock($block);
      }
    }

    // Fallback: parse the German HTML nutrient table

    return $this->parseNutrientTable($html);
  }


  private function mapNutrientBlock( array $block ) : array
  {
    $result = ['calories' => null, 'values' => []];

    foreach( $block['nutrientInformation'] ?? [] as $info )
    {
      $code  = $info['nutrientType']['code'] ?? '';
      $value = $info['quantityContained']['value'] ?? null;
      $unit  = $info['quantityContained']['uomShortText'] ?? '';

      if( $code === 'ENER-' && $unit === 'kcal' )
        $result['calories'] = $value;
      elseif( isset(self::NUTRIENT_MAP[$code]) )
        $result['values'][self::NUTRIENT_MAP[$code]] = $value;
    }

    return $result;
  }


  private function parseNutrientTable( string $html ) : array
  {
    $result = ['calories' => null, 'values' => []];

    // Rows look like: <td ...Highlight">LABEL</td><td>VALUE UNIT</td>

    if( preg_match_all('/Highlight">([^<]+)<\/td><td>([^<]*)<\/td>/', $html, $matches, PREG_SET_ORDER))
    {
      foreach( $matches as $row )
      {
        $label = trim($row[1]);
        $cell  = trim($row[2]);

        if( str_contains($label, 'Energie') )
        {
          if( str_contains($cell, 'kcal') )
            $result['calories'] = $this->num($cell);

          continue;
        }

        foreach( self::LABEL_MAP as $needle => $key )
          if( str_contains($label, $needle) && ! isset($result['values'][$key]) )
            $result['values'][$key] = $this->num($cell);
      }
    }

    return $result;
  }


  // --- metadata ----------------------------------------------------------

  // Split an ingredient statement into [ingredients, mayContain]; collapses
  // internal whitespace/newlines so each part is a clean single line

  private function splitIngredients( string $text ) : array
  {
    $mayContain = '';

    // Pull out the trailing "Kann Spuren von ... enthalten" note

    if( preg_match('/(Kann Spuren.*)/su', $text, $m))
    {
      $mayContain = $m[1];
      $text       = str_replace($m[1], '', $text);
    }

    $clean = fn($s) => trim(preg_replace('/\s+/u', ' ', $s), " .\t\n\r");

    return [$clean($text), $clean($mayContain)];
  }


  private function parseCertificates( string $html ) : array
  {
    $certs = [];

    $nutriScore = $this->jsonString($html, 'nutriScore');
    if( $nutriScore )
      $certs['NutriScore'] = $nutriScore;

    if( $this->jsonBool($html, 'bio') )
      $certs['bio'] = true;

    $flags = $this->detailValue($html, 'CustomProductFlags') ?? '';
    if( stripos($flags, 'Vegan') !== false )
      $certs['vegan'] = true;

    return $certs;
  }


  private function parsePrice( string $html ) : ?float
  {
    // price is in cents inside a "listing" object: "grammage":"800g","price":189

    if( preg_match('/"grammage":"[^"]*","price":(\d+)/', $html, $m))
      return round($m[1] / 100, 2);

    return null;
  }


  // --- JSON extraction helpers ------------------------------------------

  // Extract a JSON string value for `"key":"..."` (handles escaped chars)

  private function jsonString( string $html, string $key ) : ?string
  {
    if( preg_match('/"' . preg_quote($key, '/') . '":"((?:[^"\\\\]|\\\\.)*)"/', $html, $m))
      return json_decode('"' . $m[1] . '"');

    return null;
  }


  private function jsonBool( string $html, string $key ) : bool
  {
    return (bool) preg_match('/"' . preg_quote($key, '/') . '":true/', $html);
  }


  // Extract value of a { "name":"NAME", ... "value":"..." } detail entry

  private function detailValue( string $html, string $name ) : ?string
  {
    $pattern = '/"name":"' . preg_quote($name, '/') . '"[^}]*?"value":"((?:[^"\\\\]|\\\\.)*)"/';

    if( preg_match($pattern, $html, $m))
      return json_decode('"' . $m[1] . '"');

    return null;
  }


  // Extract and decode a balanced JSON array that follows `"key":`

  private function extractJsonArray( string $html, string $key ) : ?array
  {
    $pos = strpos($html, '"' . $key . '":[');

    if( $pos === false )
      return null;

    $start = strpos($html, '[', $pos);
    $depth = 0;

    for( $i = $start; $i < strlen($html); $i++ )
    {
      $ch = $html[$i];

      if( $ch === '[' )
        $depth++;
      elseif( $ch === ']' )
      {
        $depth--;

        if( $depth === 0 )
          return json_decode( substr($html, $start, $i - $start + 1), true);
      }
    }

    return null;
  }


  // Parse the leading number from a German-formatted cell like "10,2 g"

  private function num( string $cell ) : ?float
  {
    $cell = str_replace(['.', ','], ['', '.'], trim($cell));  // 1.168 -> 1168 ; 10,2 -> 10.2

    if( preg_match('/-?\d+(\.\d+)?/', $cell, $m))
      return (float) $m[0];

    return null;
  }
}

?>
