<?php

require_once 'lib/food_import/FoodParserBase.php';

/*

Parser for EDEKA product detail pages (www.edeka.de/unsere-marken/produkte/...).

EDEKA is a Next.js site and ships the complete product record as one JSON blob in
the `__NEXT_DATA__` script tag, so we decode it properly instead of scraping the
markup. Nutrient figures come from the `nutrition` block, whose `norm` basis is
the per-100 g/ml one we store. The rendered nutrient table serves as a fallback
if that block ever disappears.

These pages are product information, not a shop: they carry no price and no
NutriScore, so those fields stay empty.

*/
class EdekaParser extends FoodParserBase
{

  // Path to the product record inside the __NEXT_DATA__ blob

  const DATA_PATH = ['props', 'pageProps', 'moduleProps', 'props', 'data', 'data'];

  // EDEKA nutrition key -> our nutritionalValues key

  const NUTRIENT_MAP =
  [
    'fat'          => 'fat',
    'fatSaturated' => 'saturatedFat',
    'carb'         => 'carbs',
    'carbSugar'    => 'sugar',
    'fibre'        => 'fibre',
    'protein'      => 'amino',
    'salt'         => 'salt',
  ];

  // German table label -> our key (HTML fallback). Labels are whole cell values
  // with their " in g" / " in kcal" unit suffix already stripped

  const LABEL_MAP =
  [
    'Fett'                              => 'fat',
    'Fett, davon gesättigte Fettsäuren' => 'saturatedFat',
    'Kohlenhydrate'                     => 'carbs',
    'Kohlenhydrate, davon Zucker'       => 'sugar',
    'Ballaststoffe'                     => 'fibre',
    'Eiweiß'                            => 'amino',
    'Salz'                              => 'salt',
  ];


  public function matches( string $html, ?string $url ) : bool
  {
    if( $url && stripos($url, 'edeka.de') !== false )
      return true;

    return stripos($html, 'gcProductDetailsGeneral') !== false
        || stripos($html, 'cdn.produkte.edeka') !== false;
  }


  public function parse( string $html, ?string $url ) : array
  {
    $page = $this->pageJson($html);
    $data = $this->dig($page, self::DATA_PATH);

    if( ! is_array($data) || ! isset($data['pageHeading']))
      throw new Exception('Could not find product data in the EDEKA page. Please paste the full page HTML.');

    $name   = $this->cleanText($data['pageHeading']);
    $weight = preg_replace('/\s+/u', '', $data['volume']['sellingContent'] ?? '');  // "300 g" -> "300g"

    // Brand + heading + pack size reads like the vendor's own product title

    $productName = trim("{$this->brand($data)} $name $weight");

    // The ingredient text often ends in a "Das Produkt kann Spuren von ... enthalten"
    // note; the structured traces list below is preferred when EDEKA provides it

    $ingredientText = preg_replace('/^\s*Zutaten:\s*/ui', '', $data['ingredients']['ingredients'] ?? '');

    [$ingredients, $mayContain] = $this->splitIngredients($ingredientText);

    $food =
    [
      'name'         => $name,
      'productName'  => $productName,
      'vendor'       => 'Edeka',
      'url'          => $this->parseUrl($page, $url),
      'certificates' => $this->parseCertificates($data),
      'ingredients'  => $ingredients,
      'allergy'      => $this->cleanText($data['ingredients']['allergensDescription'] ?? ''),
      'mayContain'   => $this->parseTraces($data) ?? $mayContain,
      // no price on these pages: they are product information, not a shop listing
      'price'        => null,
      'weight'       => $weight,
      'pieces'       => $this->parsePieces($productName),
      'nutritionalValues' => [],
    ];

    // Nutrients (per 100 g/ml), calories separate

    $nutrients = $this->parseNutrients($data, $html);

    // Neither the JSON nutrition block nor the HTML table fallback yielded any
    // figures although the product itself parsed. That means EDEKA's page format
    // changed; fail loudly instead of importing an empty food record

    $this->assertNutrientsFound($nutrients);

    $food['calories']          = $nutrients['calories'];
    $food['nutritionalValues'] = $nutrients['values'];

    return $food;
  }


  // Guard against silent partial format changes: a valid EDEKA food must carry
  // calories and the core macronutrients. Anything less means our extraction no
  // longer matches the page

  private function assertNutrientsFound( array $nutrients ) : void
  {
    $core    = ['fat', 'carbs', 'amino'];
    $values  = $nutrients['values'];
    $hasCore = count( array_intersect(array_keys($values), $core)) === count($core);

    if( $nutrients['calories'] === null || ! $hasCore )
      throw new Exception('EDEKA page parsed but no nutrition data was found — the page format may have changed. Please paste the full page HTML, or report this if it keeps happening.');
  }


  // --- nutrients ---------------------------------------------------------

  private function parseNutrients( array $data, string $html ) : array
  {
    $nutrition = $data['nutrition'] ?? [];

    if( $nutrition )
      return $this->mapNutritionBlock($nutrition);

    // Fallback: parse the rendered German nutrient table

    return $this->parseNutrientTable($html);
  }


  // Read the `norm` (per 100 g/ml) figures. A nutrient EDEKA has no value for is
  // an empty object; a `portion` figure must never stand in for it

  private function mapNutritionBlock( array $nutrition ) : array
  {
    $result = ['calories' => $nutrition['kcal']['norm']['value'] ?? null, 'values' => []];

    foreach( self::NUTRIENT_MAP as $code => $key )
    {
      $value = $nutrition[$code]['norm']['value'] ?? null;

      if( $value !== null )
        $result['values'][$key] = $value;
    }

    return $result;
  }


  private function parseNutrientTable( string $html ) : array
  {
    $result = ['calories' => null, 'values' => []];

    // Rows look like: <td>Brennwert in kcal</td><td>512</td><td>0</td>

    if( preg_match_all('/<td>([^<]+)<\/td><td>([^<]*)<\/td>/u', $html, $matches, PREG_SET_ORDER))
    {
      foreach( $matches as $row )
      {
        // Split "Fett, davon gesättigte Fettsäuren in g" into label and unit

        if( ! preg_match('/^(.*?)\s+in\s+(kcal|kJ|g|mg|ml)$/u', trim($row[1]), $cell))
          continue;

        [, $label, $unit] = $cell;
        $value            = str_replace(',', '.', trim($row[2]));

        if( ! is_numeric($value))
          continue;

        if( $label === 'Brennwert' )
        {
          if( $unit === 'kcal' )
            $result['calories'] = (float) $value;
        }
        elseif( isset(self::LABEL_MAP[$label]) )
          $result['values'][ self::LABEL_MAP[$label] ] = (float) $value;
      }
    }

    return $result;
  }


  // --- metadata ----------------------------------------------------------

  private function brand( array $data ) : string
  {
    return $this->cleanText($data['brand']['name'] ?? '');
  }


  private function parseCertificates( array $data ) : array
  {
    // EDEKA product pages publish neither NutriScore nor a vegan flag. The organic
    // line is only recognizable by its brand name, e.g. "EDEKA Bio"

    if( preg_match('/\bBio\b/ui', $this->brand($data)))
      return ['bio' => true];

    return [];
  }


  private function parseTraces( array $data ) : ?string
  {
    $traces = $data['ingredients']['additives']['traces'] ?? [];

    if( ! $traces )
      return null;

    return implode('; ', array_map([$this, 'cleanText'], $traces));
  }


  // Use the passed-in source URL; when HTML was pasted without one, rebuild the
  // product URL from the route slug embedded in the page

  private function parseUrl( ?array $page, ?string $url ) : string
  {
    if( $url )
      return $url;

    $slug = $page['query']['slug'] ?? null;   // route slug, e.g. [unsere-marken, produkte, <product>]

    if( is_array($slug) && $slug )
      return 'https://www.edeka.de/' . implode('/', $slug) . '/';

    return '';
  }


  // --- JSON extraction helpers ------------------------------------------

  // Decode the __NEXT_DATA__ blob that carries the whole page state

  private function pageJson( string $html ) : ?array
  {
    if( ! preg_match('#<script id="__NEXT_DATA__"[^>]*>(.*?)</script>#s', $html, $m))
      return null;

    return json_decode($m[1], true);
  }


  // Walk a nested array along a key path, null if any step is missing

  private function dig( ?array $data, array $path )
  {
    foreach( $path as $key )
    {
      if( ! is_array($data) || ! isset($data[$key]))
        return null;

      $data = $data[$key];
    }

    return $data;
  }
}

?>
