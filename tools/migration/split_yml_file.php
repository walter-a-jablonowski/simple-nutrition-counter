<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

require_once 'vendor/autoload.php';

chdir('../../src');

/*

Make a PHP tool that

- reads a yml file with Symfony yaml
- saves each entry in a file in a dest folder
- using the key as file name
- file extension: yml
- file content is the yml dump of the value

Use scandir(). Ident all codes wit 2 spaces.

*/

$sourceFile = 'src/data/bundles/Default_JaneDoe@example.com-24080101000000/foods/-this.yml';
$destFolder = 'src/data/bundles/Default_JaneDoe@example.com-24080101000000/foods';

try {

  if( ! file_exists($sourceFile))
    throw new Exception("Source file does not exist: $sourceFile");

  // if( ! is_dir($destFolder))
  //   mkdir($destFolder, 0777, true);

  foreach( Yaml::parseFile($sourceFile) as $key => $value )
  {
    file_put_contents("$destFolder/$key.yml", Yaml::dump($value));
    echo "Added $key.yml\n";
  }

} catch( Exception $e ) {
  echo "Error: " . $e->getMessage() . "\n";
}

?>
