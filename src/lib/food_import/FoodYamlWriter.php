<?php

/*

Renders a normalized food-data array as a food record YAML string, matching the
hand-made records in /foods: values vertically aligned at column 19, only
non-empty fields emitted, sections separated by a blank line.

*/
class FoodYamlWriter
{

  const VALUE_COL = 19;

  // nutritionalValues keys in canonical order

  const NUTRIENT_ORDER =
    ['fat', 'saturatedFat', 'monoUnsaturated', 'polyUnsaturated',
     'carbs', 'sugar', 'sugarAlcohol', 'fibre', 'amino', 'salt'];


  public static function toYaml( array $food ) : string
  {
    $sections = [];

    // Identity and source

    $s = [];
    self::add($s, 'productName', self::str($food['productName'] ?? ''));
    self::add($s, 'vendor',      self::plain($food['vendor'] ?? ''));
    self::add($s, 'url',         self::str($food['url'] ?? ''));
    $sections[] = $s;

    // Quality and info

    $s = [];
    if( ! empty($food['certificates']))
      self::add($s, 'certificates', self::certificates($food['certificates']));
    self::add($s, 'ingredients', self::str($food['ingredients'] ?? ''));
    self::add($s, 'mayContain',  self::str($food['mayContain'] ?? ''));
    $sections[] = $s;

    // Commercial

    $s = [];
    self::add($s, 'price',  self::num($food['price']  ?? null));
    self::add($s, 'weight', self::plain($food['weight'] ?? ''));
    $sections[] = $s;

    // Nutrition

    $s = [];
    self::add($s, 'calories', self::num($food['calories'] ?? null));

    $values = $food['nutritionalValues'] ?? [];

    if( array_filter($values, fn($v) => $v !== null && $v !== ''))
    {
      $s[] = 'nutritionalValues:';
      $s[] = '';

      foreach( self::NUTRIENT_ORDER as $key )
        if( isset($values[$key]) && $values[$key] !== '')
          self::add($s, $key, self::num($values[$key]), '  ');
    }

    $sections[] = $s;

    // Source references and dates

    $s = [];
    self::add($s, 'sources',      self::sources($food['sources'] ?? ['nutriVal' => 'web']));
    self::add($s, 'lastUpd',      self::plain($food['lastUpd'] ?? ''));
    self::add($s, 'lastPriceUpd', self::plain($food['lastPriceUpd'] ?? ''));
    $sections[] = $s;

    // Join non-empty sections with a blank line between them

    $out = [];

    foreach( $sections as $section )
    {
      if( ! $section )
        continue;

      if( $out )
        $out[] = '';

      $out = array_merge($out, $section);
    }

    return implode("\n", $out) . "\n";
  }


  // --- line building -----------------------------------------------------

  // Append an aligned `key: value` line, unless the value is null (skipped)

  private static function add( array &$lines, string $key, ?string $value, string $indent = '') : void
  {
    if( $value === null )
      return;

    $prefix = "$indent$key:";
    $pad    = max(self::VALUE_COL, strlen($prefix) + 1);

    $lines[] = str_pad($prefix, $pad) . $value;
  }


  // --- value rendering ---------------------------------------------------

  // Double-quoted string, or null when empty (so the field is dropped)

  private static function str( $value ) : ?string
  {
    $value = trim((string) $value);

    if( $value === '')
      return null;

    return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
  }


  // Unquoted scalar (vendor, weight, dates), or null when empty

  private static function plain( $value ) : ?string
  {
    $value = trim((string) $value);

    return $value === '' ? null : $value;
  }


  // Number without trailing zeros (0.70 -> "0.7", 4.0 -> "4"), or null

  private static function num( $value ) : ?string
  {
    if( $value === null || $value === '')
      return null;

    return rtrim(rtrim(sprintf('%.4f', (float) $value), '0'), '.');
  }


  private static function certificates( array $certs ) : string
  {
    $parts = [];

    foreach( $certs as $key => $value )
    {
      if( $value === true )
        $parts[] = "$key: true";
      elseif( $value !== false && $value !== null && $value !== '')
        $parts[] = "$key: $value";
    }

    return '{ ' . implode(', ', $parts) . ' }';
  }


  private static function sources( array $sources ) : string
  {
    $parts = [];

    foreach( $sources as $key => $value )
      $parts[] = "$key: \"$value\"";

    return '{ ' . implode(', ', $parts) . ' }';
  }
}

?>
