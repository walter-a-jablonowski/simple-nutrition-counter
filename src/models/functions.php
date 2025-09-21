<?php

use Symfony\Component\Yaml\Yaml;

require_once 'lib/functions.php';

/**
 * Expands a food with variants into multiple separate food entries
 * 
 * @param string $baseName The original food name (from file/folder name)
 * @param array $foodData The food data array
 * @return array Array of expanded foods where key is the food name and value is the food data
 */
function expand_food_variants( $baseName, $foodData )
{
  // If no variants are defined, return the original food
  if( ! isset($foodData['variants']) || ! is_array($foodData['variants']))
    return [$baseName => $foodData];

  $expandedFoods = [];
  $baseData = $foodData;
  
  // Remove variants from base data to avoid duplication
  unset($baseData['variants']);

  foreach( $foodData['variants'] as $variant )
  {
    if( ! is_array($variant) || ! isset($variant['variantName']))
      continue;

    $variantName = $variant['variantName'];
    $variantData = $baseData; // Start with base data as default

    // Override base data with variant-specific values
    foreach( $variant as $key => $value )
    {
      if( $key === 'variantName')
        continue;

      // Handle array fields that need nested merging (like sources)
      if( is_array($value) && isset($variantData[$key]) && is_array($variantData[$key]))
      {
        // For associative arrays, merge recursively
        if( is_assoc_array($value) && is_assoc_array($variantData[$key]))
          $variantData[$key] = array_merge($variantData[$key], $value);
        else
          // For indexed arrays, replace completely
          $variantData[$key] = $value;
      }
      else
      {
        // For non-array values, simply override
        $variantData[$key] = $value;
      }
    }

    $expandedFoods[$variantName] = $variantData;
  }

  return $expandedFoods;
}

/**
 * Finds the source file and variant information for a given food name
 * 
 * @param string $foodName The name of the food to find
 * @param string $userId The user ID
 * @return array|null Returns array with 'file', 'isVariant', 'variantIndex' or null if not found
 */
function find_food_source( $foodName, $userId )
{
  $dir = "data/bundles/Default_$userId/foods";
  
  if( ! is_dir($dir))
    return null;

  foreach( scandir($dir) as $file )
  {
    if( in_array( $file, ['.', '..']) || in_array( $file[0], ['_']) || ( pathinfo($file, PATHINFO_EXTENSION) !== 'yml' && ! is_dir("$dir/$file")))
      continue;

    $baseName = is_dir("$dir/$file")  ?  $file  :  pathinfo($file, PATHINFO_FILENAME);
    $filePath = is_file("$dir/$file") ? "$dir/$file" : "$dir/$file/-this.yml";
    
    // Check if this is the base food name
    if( $baseName === $foodName )
    {
      return [
        'file' => $filePath,
        'isVariant' => false,
        'variantIndex' => null
      ];
    }
    
    // Check if this food has variants that match the food name
    if( file_exists($filePath))
    {
      try {
        $foodData = Yaml::parse( file_get_contents($filePath));
        
        if( isset($foodData['variants']) && is_array($foodData['variants']))
        {
          foreach( $foodData['variants'] as $index => $variant )
          {
            if( isset($variant['variantName']) && $variant['variantName'] === $foodName )
            {
              return [
                'file' => $filePath,
                'isVariant' => true,
                'variantIndex' => $index
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
 * Updates a price for a food, handling both regular foods and variants
 * 
 * @param string $foodName The name of the food
 * @param string $newPrice The new price value
 * @param string $userId The user ID
 * @return bool Success status
 */
function update_food_price( $foodName, $newPrice, $userId )
{
  $sourceInfo = find_food_source( $foodName, $userId );
  
  if( ! $sourceInfo )
    return false;
    
  $filePath = $sourceInfo['file'];
  
  if( ! file_exists($filePath))
    return false;
    
  $content = file_get_contents($filePath);
  
  if( ! $sourceInfo['isVariant'])
  {
    // Regular food - update price directly
    $updatedContent = yml_replace_value( $content, 'price', $newPrice );
  }
  else
  {
    // Variant food - need to update the specific variant
    $variantIndex = $sourceInfo['variantIndex'];
    $updatedContent = yml_replace_variant_value( $content, $variantIndex, 'price', $newPrice );
  }
  
  return file_put_contents( $filePath, $updatedContent ) !== false;
}

/**
 * Updates a value within a specific variant in YAML content
 * 
 * @param string $yamlContent The YAML content
 * @param int $variantIndex The index of the variant to update
 * @param string $key The key to update
 * @param string $newValue The new value
 * @return string Updated YAML content
 */
function yml_replace_variant_value( $yamlContent, $variantIndex, $key, $newValue )
{
  // This is a simplified approach - in a production environment, 
  // you might want to use a proper YAML parser/writer
  
  $lines = explode("\n", $yamlContent);
  $inVariants = false;
  $currentVariantIndex = -1;
  $foundVariant = false;
  
  for( $i = 0; $i < count($lines); $i++ )
  {
    $line = $lines[$i];
    
    // Check if we're entering the variants section
    if( preg_match('/^variants:\s*$/', $line))
    {
      $inVariants = true;
      continue;
    }
    
    // If we're in variants and find a new variant (starts with - )
    if( $inVariants && preg_match('/^\s*-\s/', $line))
    {
      $currentVariantIndex++;
      if( $currentVariantIndex === $variantIndex )
        $foundVariant = true;
      elseif( $foundVariant )
        break; // We've moved past our target variant
    }
    
    // If we're in the target variant and find the key to update
    if( $foundVariant && preg_match('/^(\s+)' . preg_quote($key, '/') . '(\s*:\s*)/', $line, $matches))
    {
      // Replace the value on this line, preserving indentation and key
      $indentation = $matches[1];
      $keyAndColon = $matches[2];
      $lines[$i] = $indentation . $key . $keyAndColon . $newValue;
      break;
    }
    
    // If we hit a non-indented line after being in variants, we're done
    if( $inVariants && $foundVariant && preg_match('/^[a-zA-Z]/', $line))
      break;
  }
  
  return implode("\n", $lines);
}

?>
