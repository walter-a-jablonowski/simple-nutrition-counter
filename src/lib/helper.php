<?php

// TASK: (advanced) make reusable (maybe recursive via AI)
// TASK: (advanced) we could add a skip keys arg for (first_entries) is we use this

function parse_attribs( string $attribsKey, array $largeAttribKeys, array $array)
{
  $r = [];

  foreach( $array as $key => $val )
  {
    $attribs = [];

    // Key attribs
    // TASK: use last ( ) in string, so we can use () in text or use curly
    
    if( preg_match('/\(([^)]+)\)/', $key, $a))  // && $key != '(first_entries)')
    {
      foreach( explode(',', $a[1]) as $attr )
      {
        [$left, $right] = explode(':', $attr);

        $right = in_array( $right, ['true', 'false'])
               ? boolval($right)
               : trim($right);

        $attribs[trim($left)] = $right;
      }
    }

    // Single attrib key (larger content)

    if( $attribsKey && isset($val[$attribsKey]))
    {
      $attribs = array_merge( $attribs, $val[$attribsKey]);
      unset($val[$attribsKey]);
    }
    
    // Multiple attrib keys (larger content)

    foreach( $largeAttribKeys as $key2 )
    {
      if( isset($val[$key2]))
      {
        $attribs = array_merge( $attribs, [$key2 => $val[$key2]]);
        unset($val[$key2]);
      }
    }

    //if( $key != '(first_entries)')
    // $key = trim( str_replace("($key)", '', $key));
    $key = trim( preg_replace('/\([^)]+\)/', '', $key));

    $r[$key] = $val;
    
    if( $attribs )
      $r[$key][$attribsKey] = $attribs;
  }
  
  return $r;
}

function parse_tsv( $entriesTxt )
{
  $r = [];

  if( $entriesTxt )
  {
    $lines = explode("\n", $entriesTxt);

    foreach( $lines as $line )
    {
      // $line = preg_replace('/ {2,}/', ';', trim($line));
      $line  = preg_replace('/(?<=\S) {2,}(?=\S)/', ';', $line);  // added ignore spaces at bol (cause we are using str_pad() for amounts)
      $entry = explode(';', $line);
      $r[] = $entry;
    }
  }

  return $r;
}

/*@

TASK: mov in some lib

- compatibility: YML needs more blanks (behind colon)
- empty array as js obj

*/
function dump_json( $array )  /**/
{
  // $r = json_encode( $array, JSON_FORCE_OBJECT);  // might also make num keys obj
  $r = str_replace('[]', '{}', json_encode( $array ));
  $r = preg_replace('/([:,])(?! )/', '$1 ', $r);

  return $r;
}

?>
