<?php

trait UpdateUnpreciseHeaderAjaxController
{

  public function updateUnpreciseHeader( $request )
  {
    $config = config::instance();

    $date = $request['date'];
    $isUnprecise = $request['unprecise'];
    
    // Read existing file
    $filePath = 'data/users/' . $config->get('defaultUser') . "/days/$date.tsv";
    $existingContent = @file_get_contents($filePath) ?: '';
    $parsedFile = parse_data_file($existingContent);
    
    // Update headers
    $headers = $parsedFile['headers'];
    
    if( $isUnprecise )
    {
      $headers['unprecise'] = true;
    }
    else
    {
      // Remove the unprecise header when turned off
      unset($headers['unprecise']);
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
