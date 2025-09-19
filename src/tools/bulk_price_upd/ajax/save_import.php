<?php

use Symfony\Component\Yaml\Yaml;

// Handler: save_import
// Expects $payload = ['name' => string, 'price' => optional, 'dealPrice' => optional]
// Writes to ../import.yml (sibling of ajax/)
function handle_save_import( array $payload ) : array
{
  $name = isset($payload['name']) ? trim((string)$payload['name']) : '';
  if( $name === '')
    return ['status' => 'error', 'message' => 'Missing name'];

  $price     = array_key_exists('price', $payload) ? (string)$payload['price'] : '';
  $dealPrice = array_key_exists('dealPrice', $payload) ? (string)$payload['dealPrice'] : '';

  $import_file = dirname(__DIR__) . '/import.yml';

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

  // Currency formatter: always two decimals with dot
  $formatCurrency = function( $s ) {
    $s = trim((string)$s);
    if( $s === '' ) return '';
    // minimal locale normalization
    $s = str_replace(',', '.', $s);
    return is_numeric($s) ? sprintf('%.2f', (float)$s) : $s;
  };

  $entry = [];
  if( $price !== '' )     $entry['price'] = $formatCurrency($price);
  if( $dealPrice !== '' ) $entry['dealPrice'] = $formatCurrency($dealPrice);

  if( ! empty($entry)) {
    $entry['lastPriceUpd'] = (new DateTime())->format('Y-m-d');
    $data[$name] = $entry;
  }
  else {
    if( isset($data[$name])) unset($data[$name]);
  }

  // Dump YAML (indent 2)
  $yaml = Yaml::dump($data, 4, 2);
  // Ensure currency looks like a number with two decimals (no quo)
  $yaml = preg_replace("/(^|\n)(\s*price: )'(\d+\.\d{2})'/", '$1$2$3', $yaml);
  $yaml = preg_replace("/(^|\n)(\s*dealPrice: )'(\d+\.\d{2})'/", '$1$2$3', $yaml);
  if( file_put_contents($import_file, $yaml) === false)
    return ['status' => 'error', 'message' => 'Failed to write file'];

  return ['status' => 'success', 'data' => ($data[$name] ?? (object)[])];
}
