<?php

trait UpdateUnpreciseTimeHeaderAjaxController
{

  public function updateUnpreciseTimeHeader( $request )
  {
    $config = config::instance();

    $date = $request['date'];
    $isUnpreciseTime = $request['unpreciseTime'];
    
    // Read existing file
    $filePath = 'data/users/' . $config->get('defaultUser') . "/days/$date.tsv";
    $existingContent = @file_get_contents($filePath) ?: '';
    $parsedFile = parse_data_file($existingContent);
    
    // Update headers
    $headers = $parsedFile['headers'];
    
    if( $isUnpreciseTime )
    {
      $headers['unpreciseTime'] = true;
    }
    else
    {
      // Remove the unpreciseTime header when turned off
      unset($headers['unpreciseTime']);
    }
    
    // Combine updated headers with existing data
    $headersString = format_headers_to_string($headers);
    $finalContent = $headersString . $parsedFile['data'];
    
    if( ! file_put_contents($filePath, $finalContent) )
      return ['result' => 'error', 'message' => 'Error saving file'];

    return ['result' => 'success'];
  }
}

?>
