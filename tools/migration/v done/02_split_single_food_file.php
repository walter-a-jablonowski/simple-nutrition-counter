<?php

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

chdir('../../src');

require_once 'vendor/autoload.php';

/*

Make a PHP tool that

- reads a yml file with Symfony yaml
- saves each entry in a file in a dest folder
- using the key as file name
- file extension: yml
- file content is the yml dump of the value

Use scandir(). Ident all codes with 2 spaces.

*/

$sourceFile = 'data/bundles/Default_JaneDoe@example.com-24080101000000/foods/-this.yml';
$destFolder = 'data/bundles/Default_JaneDoe@example.com-24080101000000/foods';

try {

  if( ! file_exists($sourceFile))
    throw new Exception("Source file does not exist: $sourceFile");

  // if( ! is_dir($destFolder))
  //   mkdir($destFolder, 0777, true);

  $content = file_get_contents($sourceFile);

  foreach( Yaml::parse($content) as $key => $value )
  {
    // file_put_contents("$destFolder/$key.yml", Yaml::dump($value));
    file_put_contents("$destFolder/$key.yml", extractYamlSection($content, $key));  // there is a problem when the key is like `Some (name)`, most likely sth with regex
    echo "<b>Added</b> $key.yml<br>\n";
    flush();
  }

  echo "<br>\n<b>Done</b>";

} catch( Exception $e ) {
  echo "Error: " . $e->getMessage();
}

function extractYamlSection($content, $key)
{
  $lines     = explode("\n", $content);
  $section   = [];
  $inSection = false;
  $indent    = null;

  foreach( $lines as $line )
  {
    if( preg_match("/^(\s*)$key:/", $line, $matches))
    {
      $inSection = true;
      $indent    = strlen($matches[1]) + 2;   // add 2 to account for the space after the colon
      continue;
    }
    elseif( $inSection )
    {
      if( strlen( ltrim($line)) === 0 )
        $section[] = $line;
      elseif( strspn($line, " ") < $indent)
        break;
      else
        $section[] = substr($line, $indent);  // unindent by removing the first $indent spaces
    }
  }

  return trim( implode("\n", $section)) . "\n";
}

?>
