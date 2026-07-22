<?php

/*

Validates a food record before it is saved. Central place for the required-field
contract (see misc/inline_help/foods.yml) so any save/import path enforces the
same minimum data. Auto-filled meta (sources, lastUpd) is not checked here.

*/
class FoodValidator
{

  // Required fields per foods.yml. Nutrient fields live under nutritionalValues

  const REQUIRED_TOP      = ['name', 'weight', 'calories'];
  const REQUIRED_NUTRIENT = ['fat', 'carbs', 'sugar', 'amino', 'salt'];


  // Return the missing required fields (empty array = valid). A value of 0 is
  // valid; only null or a blank string counts as missing

  public static function missingRequired( array $food ) : array
  {
    $missing = [];

    foreach( self::REQUIRED_TOP as $field )
      if( self::isBlank( $food[$field] ?? null))
        $missing[] = $field;

    foreach( self::REQUIRED_NUTRIENT as $field )
      if( self::isBlank( $food['nutritionalValues'][$field] ?? null))
        $missing[] = $field;

    return $missing;
  }


  private static function isBlank( $value ) : bool
  {
    return $value === null || ( is_string($value) && trim($value) === '');
  }
}

?>
