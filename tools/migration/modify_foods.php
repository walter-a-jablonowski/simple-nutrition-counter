<?php

// AI generated: sources key from string to array

$content = file_get_contents('foods.yml');

function replace_sources($matches) {
  $keys = ["macroNutrients", "nutrients", "price"];
  $sources = explode(',', $matches[1]);
  $sourcesArray = [];
  foreach ($sources as $index => $source) {
    if (isset($keys[$index])) {
      $sourcesArray[] = '"' . $keys[$index] . '": "' . trim($source) . '"';
    }
  }
  return 'sources: {' . implode(', ', $sourcesArray) . '}';
}

// Use preg_replace_callback to replace the sources
$pattern = '/sources:\s*"(.*?)"/';
$modifiedContent = preg_replace_callback($pattern, 'replace_sources', $content);

file_put_contents('foods_new.yml', $modifiedContent);

?>
