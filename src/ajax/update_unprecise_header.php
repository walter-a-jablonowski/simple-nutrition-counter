<?php

trait UpdateUnpreciseHeaderAjaxController
{
  public function updateUnpreciseHeader( $request )
  {
    $config = config::instance();

    $date = $request['date'];
    $flag = $request['flag'];
    $on   = $request['on'];

    // Whitelist: the flag comes from the client and is written into the day file as is

    if( ! in_array( $flag, ['unprecise', 'unpreciseTime', 'unprecisePrice', 'cheatday']))
      return ['result' => 'error', 'message' => "Unknown unprecise flag '$flag'"];

    // Read existing file

    $filePath        = 'data/users/' . $config->get('defaultUser') . "/days/$date.tsv";
    $existingContent = @file_get_contents($filePath) ?: '';
    $parsedFile      = parse_data_file($existingContent);

    // Update headers: the flag is only present while it is set

    $headers = $parsedFile['headers'];

    if( $on )
      $headers[$flag] = true;
    else
      unset($headers[$flag]);

    // Combine updated headers with existing data

    $finalContent = format_headers_to_string($headers) . $parsedFile['data'];

    // === false, not falsy: clearing the last flag of an empty day writes 0 bytes

    if( file_put_contents($filePath, $finalContent) === false )
      return ['result' => 'error', 'message' => 'Error saving file'];

    return ['result' => 'success'];
  }
}

?>
