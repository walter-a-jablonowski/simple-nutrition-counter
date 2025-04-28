<?php

// Include the library with the findRewePrice function
require_once __DIR__ . '/lib.php';

// Output file for the results
$outputFile = __DIR__ . '/rewe_prices.txt';

// Initialize the output file with a header
$timestamp = date('Y-m-d H:i:s');
file_put_contents($outputFile, "REWE Price Check - Started at $timestamp\n\n", FILE_APPEND);

/**
 * Process a YAML file to check for REWE products
 * 
 * @param string $filePath Path to the YAML file
 * @return void
 */
function processYamlFile( $filePath )
{
  global $outputFile;
  
  // Skip files that start with underscore
  $filename = basename($filePath);
  if( substr($filename, 0, 1) === '_' )
    return;
  
  // Parse YAML file
  try {
    $yaml = yaml_parse_file($filePath);
  }
  catch( Exception $e ) {
    file_put_contents($outputFile, "Error parsing $filePath: {$e->getMessage()}\n", FILE_APPEND);
    return;
  }
  
  // Check if vendor is REWE
  if( isset($yaml['vendor']) && $yaml['vendor'] === 'Rewe' )
  {
    $productName = isset($yaml['name']) ? $yaml['name'] : basename($filePath, '.yml');
    
    // Log that we're checking this product
    echo "Checking REWE product: $productName\n";
    
    // Get the price from REWE.de
    $result = findRewePrice($productName);
    
    // Format the result for the output file
    $resultStr = date('Y-m-d H:i:s') . " | $productName | ";
    
    if( $result[0] === 'success' )
    {
      $price = $result[1];
      $isDealPrice = $result[2] ? 'DEAL' : 'regular';
      $resultStr .= "PRICE: €$price ($isDealPrice)";
    }
    else
    {
      $resultStr .= "STATUS: {$result[0]}";
      if( isset($result[1]) )
        $resultStr .= " | REASON: {$result[1]}";
    }
    
    // Append to the output file
    file_put_contents($outputFile, $resultStr . "\n", FILE_APPEND);
    
    // Wait for a random time between requests to avoid being blocked
    $waitTime = rand(2000, 5000); // 2-5 seconds
    echo "Waiting for $waitTime ms before next request...\n";
    usleep($waitTime * 1000);
  }
}

/**
 * Recursively scan a directory for YAML files
 * 
 * @param string $dir Directory to scan
 * @return void
 */
function scanDirectory( $dir )
{
  $files = scandir($dir);
  
  foreach( $files as $file )
  {
    if( $file === '.' || $file === '..' )
      continue;
    
    $path = $dir . '/' . $file;
    
    if( is_dir($path) )
    {
      // Check subdirectories for "-this.yml" files
      $subFiles = scandir($path);
      foreach( $subFiles as $subFile )
      {
        if( substr($subFile, -8) === '-this.yml' )
          processYamlFile($path . '/' . $subFile);
      }
    }
    elseif( substr($file, -4) === '.yml' )
    {
      // Process regular YAML files
      processYamlFile($path);
    }
  }
}

// Main loop - run endlessly
echo "Starting REWE price checker...\n";
echo "Results will be appended to: $outputFile\n";
echo "Press Ctrl+C to stop the script\n\n";

// Use the correct path with forward slashes
$foodsDir = dirname(dirname(dirname(__FILE__))) . '/src/data/bundles/Default_JaneDoe@example.com-24080101000000/foods';

// Make sure the path exists
if( ! is_dir($foodsDir) )
  exit("Error: Foods directory missing at: $foodsDir\n");

try
{
  // Run in an endless loop
  while( true )
  {
    echo "Starting new scan cycle at " . date('Y-m-d H:i:s') . "\n";
    
    // Scan the foods directory
    scanDirectory($foodsDir);
    
    // Wait between full scans
    $waitTime = rand(10000, 30000); // 10-30 seconds
    echo "\nCompleted scan cycle. Waiting $waitTime ms before next cycle...\n\n";
    usleep($waitTime * 1000);
  }
}
catch( Exception $e )
{
  file_put_contents($outputFile, "Error: {$e->getMessage()}\n", FILE_APPEND);
  echo "Error: {$e->getMessage()}\n";
}
