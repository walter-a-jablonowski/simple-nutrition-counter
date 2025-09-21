<?php

/**
 * Simple variant helper functions for bulk price update tool
 * Self-contained implementation without dependencies on main models
 */

use Symfony\Component\Yaml\Yaml;

/**
 * Expands a food with variants into separate entries for the bulk tool
 * 
 * @param string $baseName The base food name
 * @param array $foodData The food data
 * @return array Array of expanded foods [name => data]
 */
function bulk_expand_variants( $baseName, $foodData )
{
  // If no variants, return the base food
  if( ! isset($foodData['variants']) || ! is_array($foodData['variants']))
    return [$baseName => $foodData];

  $expandedFoods = [];
  $baseData = $foodData;
  
  // Remove variants from base data
  unset($baseData['variants']);

  // Expand each variant
  foreach( $foodData['variants'] as $variantName => $variant )
  {
    if( ! is_array($variant))
      continue;

    // Start with base data and override with variant data
    $variantData = array_merge($baseData, $variant);
    $expandedFoods[$variantName] = $variantData;
  }

  return $expandedFoods;
}

/**
 * Finds which file contains a specific food (including variants)
 * 
 * @param string $foodName The food name to find
 * @param string $userId The user ID
 * @return array|null ['file' => path, 'isVariant' => bool, 'variantName' => name|null]
 */
function bulk_find_food_source( $foodName, $userId )
{
  $dir = "data/bundles/Default_$userId/foods";
  
  if( ! is_dir($dir))
    return null;

  foreach( scandir($dir) as $file )
  {
    if( in_array( $file, ['.', '..']) || $file[0] === '_')
      continue;
      
    if( pathinfo($file, PATHINFO_EXTENSION) !== 'yml' && ! is_dir("$dir/$file"))
      continue;

    $baseName = is_dir("$dir/$file") ? $file : pathinfo($file, PATHINFO_FILENAME);
    $filePath = is_file("$dir/$file") ? "$dir/$file" : "$dir/$file/-this.yml";
    
    // Check if this is the base food
    if( $baseName === $foodName )
    {
      return [
        'file' => $filePath,
        'isVariant' => false,
        'variantName' => null
      ];
    }
    
    // Check variants
    if( file_exists($filePath))
    {
      try {
        $foodData = Yaml::parseFile($filePath);
        
        if( isset($foodData['variants']) && is_array($foodData['variants']))
        {
          foreach( $foodData['variants'] as $variantName => $variant )
          {
            if( $variantName === $foodName )
            {
              return [
                'file' => $filePath,
                'isVariant' => true,
                'variantName' => $variantName
              ];
            }
          }
        }
      }
      catch( Exception $e )
      {
        // Skip files that can't be parsed
        continue;
      }
    }
  }
  
  return null;
}

/**
 * Updates a price in a YAML file (supports both regular foods and variants)
 * 
 * @param string $foodName The food name
 * @param float $newPrice The new price
 * @param string $userId The user ID
 * @return bool Success status
 */
function bulk_update_price( $foodName, $newPrice, $userId )
{
  $sourceInfo = bulk_find_food_source( $foodName, $userId );
  
  if( ! $sourceInfo )
    return false;
    
  $filePath = $sourceInfo['file'];
  
  if( ! file_exists($filePath))
    return false;
    
  $content = file_get_contents($filePath);
  
  if( ! $sourceInfo['isVariant'])
  {
    // Regular food - update price directly
    $updatedContent = bulk_replace_yaml_value( $content, 'price', $newPrice );
  }
  else
  {
    // Variant food - update specific variant
    $variantName = $sourceInfo['variantName'];
    $updatedContent = bulk_replace_variant_price( $content, $variantName, $newPrice );
  }
  
  return file_put_contents( $filePath, $updatedContent ) !== false;
}

/**
 * Simple YAML value replacement for regular foods
 * 
 * @param string $yamlContent The YAML content
 * @param string $key The key to update
 * @param mixed $newValue The new value
 * @return string Updated YAML content
 */
function bulk_replace_yaml_value( $yamlContent, $key, $newValue )
{
  $lines = explode("\n", $yamlContent);
  
  foreach( $lines as $i => $line )
  {
    if( preg_match('/^' . preg_quote($key, '/') . '(\s*:\s*)/', $line, $matches))
    {
      $lines[$i] = $key . $matches[1] . $newValue;
      break;
    }
  }
  
  return implode("\n", $lines);
}

/**
 * Updates a price within a specific variant in YAML content
 * 
 * @param string $yamlContent The YAML content
 * @param string $variantName The variant name
 * @param float $newPrice The new price
 * @return string Updated YAML content
 */
function bulk_replace_variant_price( $yamlContent, $variantName, $newPrice )
{
  $lines = explode("\n", $yamlContent);
  $inVariants = false;
  $foundVariant = false;
  
  for( $i = 0; $i < count($lines); $i++ )
  {
    $line = $lines[$i];
    
    // Check if we're entering variants section
    if( preg_match('/^variants:\s*$/', $line))
    {
      $inVariants = true;
      continue;
    }
    
    if( $inVariants )
    {
      // Look for our specific variant
      if( preg_match('/^\s*(["\']?)' . preg_quote($variantName, '/') . '\1:\s*$/', $line))
      {
        $foundVariant = true;
        continue;
      }
      
      // If we found another variant after ours, we're done
      if( $foundVariant && preg_match('/^\s*(["\']?)[^-\s].*?\1:\s*$/', $line))
      {
        break;
      }
      
      // Update price in our variant
      if( $foundVariant && preg_match('/^(\s+)price(\s*:\s*)/', $line, $matches))
      {
        $indentation = $matches[1];
        $keyAndColon = $matches[2];
        $lines[$i] = $indentation . 'price' . $keyAndColon . $newPrice;
        break;
      }
      
      // If we hit a non-indented line, we're done with variants
      if( $foundVariant && preg_match('/^[a-zA-Z]/', $line))
        break;
    }
  }
  
  return implode("\n", $lines);
}

?>
