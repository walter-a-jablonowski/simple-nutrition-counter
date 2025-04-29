<?php

require_once '../../vendor/autoload.php';
require_once 'PriceImporter.php';

try
{
  echo "Starting price import...\n";
  
  $importer = new PriceImporter('places.yml', '../../../data/bundles/Default_JaneDoe@example.com-24080101000000/foods');
  $result = $importer->run();
  
  echo "Price import completed successfully.\n";
  echo "Files updated: {$result['updatedFiles']}\n";
  echo "Prices updated: {$result['updatedPrices']}\n";
}
catch( Exception $e )
{
  echo "Error: " . $e->getMessage() . "\n";
  exit(1);
}
