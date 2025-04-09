<?php

/*@

Prints yml formatted

file_put_contents('out.yml',  Printer::run('my.yml', [
  'title' => 'Hello World'
]));

*/
class YMLPrinter /*@*/
{

  public static function run( string $fil, array $data ) : string
  {
    ob_start();
    require $fil;
    return ob_get_clean();
  }


  /*@

  Append a string
  from damn-small-engine (append() simplified)

  like print() adds space before

  */
  public static function append( $s ) /*@*/
  {
    // $s = self::print( $arg1, $arg2, $arg3 );

    if( $s )  return " $s";
    else      return '';
  }


  /*@

  iif
  from standard/dev

  - valid are non-false and 0, 0.0, "0"

  */
  public static function iif( $if, $true, $false = '' ) /*@*/
  {
    if( $if || $if === 0 || $if === 0.0 || $if === "0" )
      return $true;
    else
      return $false;
  }

  /*@

  list

  */
  public static function list( $array, $item, $trim = ', ') /*@*/
  {
    $r = '';
    
    foreach( $array as $key => $value)
    {
      $value = ! is_bool($value) ? $value : ( $value ? 'true' : 'false');
      $r .= str_replace('[key]', $key, str_replace('[value]', $value, $item));
    }

    return trim($r, $trim);
  }
}

?>
