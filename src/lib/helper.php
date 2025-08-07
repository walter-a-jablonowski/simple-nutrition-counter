<?php

// Layout file

// TASK: mov in some lib
// TASK: (advanced) make reusable (maybe recursive via AI)
// TASK: (advanced) we could add a skip keys arg for (first_entries) is we use this
function parse_attribs( string $attribsKey, array $largeAttribKeys, array $array)  // TASK: maybe some arg for first_entries
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
 * Returns array with 'headers' and 'data' keys
 */
function parse_file_with_headers( $fileContent )
{
  $lines = explode("\n", $fileContent);
  $headers = [];
  $dataStartIndex = 0;
  $foundEmptyLine = false;
  
  // Look for empty line that separates headers from data
  for( $i = 0; $i < count($lines); $i++ )
  {
    $line = trim($lines[$i]);
    
    if( $line === '' )
    {
      $foundEmptyLine = true;
      $dataStartIndex = $i + 1;
      break;
    }
    
    // Check if line looks like a header (contains colon and doesn't look like TSV data)
    if( strpos($line, ':') !== false && !looks_like_tsv_data($line) )
    {
      list($key, $value) = explode(':', $line, 2);
      $key = trim($key);
      $value = trim($value);
      
      // Convert value to appropriate type
      $headers[$key] = parse_header_value($value);
    }
    else
    {
      // No colon found or looks like TSV data, assume this is data, not headers
      $dataStartIndex = 0;
      break;
    }
  }
  
  // If we found headers but no empty line separator, it's probably not headers
  if( !empty($headers) && !$foundEmptyLine )
  {
    $headers = [];
    $dataStartIndex = 0;
  }
  
  // Extract data lines
  $dataLines = array_slice($lines, $dataStartIndex);
  $dataContent = implode("\n", $dataLines);
  
  return [
    'headers' => $headers,
    'data' => trim($dataContent, "\n")
  ];
}

/**
 * Check if a line looks like TSV data rather than a header
 * TSV data typically contains time stamps and multiple whitespace-separated values
 */
function looks_like_tsv_data( $line )
{
  // Check for time pattern (HH:MM:SS or --:--:--)
  if( preg_match('/^\d{2}:\d{2}:\d{2}/', $line) || preg_match('/^--:--:--/', $line) )
    return true;
    
  // Check for multiple consecutive spaces (typical TSV formatting)
  if( preg_match('/\s{2,}/', $line) )
    return true;
    
  return false;
}

/**
 * Parse header value to appropriate type (int, float, bool, string)
 */
function parse_header_value( $value )
{
  // Boolean values
  if( strtolower($value) === 'true' )
    return true;
  if( strtolower($value) === 'false' )
    return false;
    
  // Numeric values
  if( is_numeric($value) )
  {
    if( strpos($value, '.') !== false )
      return (float)$value;
    else
      return (int)$value;
  }
  
  // String value
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
  if( empty($headers) )
    return '';
    
  $headerLines = [];
  foreach( $headers as $key => $value )
  {
    if( is_bool($value) )
      $value = $value ? 'true' : 'false';
    $headerLines[] = "$key: $value";
  }
  
  return implode("\n", $headerLines) . "\n\n";
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
