<?php

chdir('../../src');

// modify sources key from string to array
// (ai generated)

$source  = 'data/bundles/Default_JaneDoe@example.com-24080101000000/foods.yml';
$content = file_get_contents($source);  // use preg_replace_callback to replace the sources
$modifiedContent = preg_replace_callback('/sources:\s*"(.*?)"/', 'replace_sources', $content);

file_put_contents('foods_new.yml', $modifiedContent);

function replace_sources($matches)
{
  $keys = ['macroNutrients', 'nutrients', 'price'];
  $sources = explode(',', $matches[1]);
  $sourcesArray = [];

  foreach( $sources as $index => $source)
  {
    if( isset($keys[$index]))
      $sourcesArray[] = '"' . $keys[$index] . '": "' . trim($source) . '"';
  }

  return 'sources: {' . implode(', ', $sourcesArray) . '}';
}

?>
