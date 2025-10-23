<?php

// Layout file

// TASK: mov some in models/functions
// TASK: (advanced) make reusable (maybe recursive via AI)
// TASK: (advanced) we could add a skip keys arg for (first_entries) is we use this
function parse_layout_attribs( string $attribsKey, array $largeAttribKeys, array $array)  // TASK: maybe some arg for first_entries
{
  $r = [];

  foreach( $array as $key => $val )
  {
    $attribs = [];

    // Key attribs
    // TASK: use last ( ) in string, so we can use () in text or use curly
    
    if( preg_match('/\(([^)]+)\)/', $key, $a)            && $key != '(first_entries)')
    // if( preg_match('/\(([^:]+:[^)]+)\)/', $key, $a))  // alternative: with : in the middle
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

    if( $key != '(first_entries)')
      $key = trim( preg_replace('/\([^)]+\)/', '', $key));

    $r[$key] = $val;
    
    if( $attribs )
      $r[$key][$attribsKey] = $attribs;
  }
  
  return $r;
}

// Data files

/**
 * Parse file headers from TSV content
 * Headers are name-value pairs separated from data by an empty line
 */
function parse_data_file( $fileContent )
{
  $lines     = explode("\n", $fileContent);
  $headers   = [];
  $dataStart = 0;
  
  foreach( $lines as $i => $line )
  {
    $line = trim($line);
    
    if( $line === '' )
    {
      $dataStart = $i + 1;
      break;
    }
    
    if( strpos($line, ':') !== false && ! preg_match('/^(\d{2}:|--:)/', $line))
    {
      [$key, $value] = explode(':', $line, 2);
      $headers[trim($key)] = parse_header_value(trim($value));
    }
    else
    {
      // Not a header, start data from here
      break;
    }
  }
  
  $dataLines = array_slice($lines, $dataStart);

  return [
    'headers' => $headers,
    'data' => trim( implode("\n", $dataLines), "\n")
  ];
}

/**
 * Parse header value to appropriate type (int, float, bool, string)
 */
function parse_header_value( $value )
{
  $lower = strtolower($value);
  if( $lower === 'true' )   return true;
  if( $lower === 'false' )  return false;
  if( is_numeric($value) )  return strpos($value, '.') !== false ? (float)$value : (int)$value;
  return $value;
}

function parse_tsv( $entriesTxt, $header )
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
      $entry = array_combine($header, $entry);

      $r[] = $entry;
    }
  }

  return $r;
}

/**
 * Format headers back to string format for saving
 */
function format_headers_to_string( $headers )
{
  if( empty($headers) ) return '';
  
  $lines = [];
  foreach( $headers as $key => $value )
    $lines[] = "$key: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value);
  
  return implode("\n", $lines) . "\n\n";
}

/*@

- compatibility: YML needs more blanks (behind colon)
- empty array as js obj

*/
function dump_json( $array )  /*@*/
{
  // $r = json_encode( $array, JSON_FORCE_OBJECT);  // might also make num keys obj
  $r = str_replace('[]', '{}', json_encode( $array ));
  $r = preg_replace('/([:,])(?! )/', '$1 ', $r);

  return $r;
}

?>
