<?php

use Symfony\Component\Yaml\Yaml;

// Handler: reset_import
// Expects $payload = ['name' => string]
// Removes entire food entry from ../data/import.yml
function handle_reset_import( array $payload ) : array
{
  $name = isset($payload['name']) ? trim((string)$payload['name']) : '';
  if( $name === '')
    return ['status' => 'error', 'message' => 'Missing name'];

  $import_file = dirname(__DIR__) . '/data/import.yml';

  // Load existing
  $data = [];
  if( file_exists($import_file)) {
    try {
      $parsed = Yaml::parseFile($import_file);
      if( is_array($parsed)) $data = $parsed;
    }
    catch( \Throwable $e ) {
      // keep empty on parse error
    }
  }

  // Remove entry if exists
  if( isset($data[$name]))
    unset($data[$name]);

  // Ensure target directory exists
  $dir = dirname($import_file);
  if( ! is_dir($dir)) {
    if( ! mkdir($dir, 0777, true))
      return ['status' => 'error', 'message' => 'Cannot create data directory'];
  }

  // Dump YAML (indent 2)
  $yaml = Yaml::dump($data, 4, 2);
  // Ensure currency looks like a number with two decimals (no quotes)
  $yaml = preg_replace("/(^|\n)(\s*price: )'(\d+\.\d{2})'/", '$1$2$3', $yaml);
  $yaml = preg_replace("/(^|\n)(\s*dealPrice: )'(\d+\.\d{2})'/", '$1$2$3', $yaml);
  if( file_put_contents($import_file, $yaml) === false)
    return ['status' => 'error', 'message' => 'Failed to write file'];

  return ['status' => 'success'];
}
