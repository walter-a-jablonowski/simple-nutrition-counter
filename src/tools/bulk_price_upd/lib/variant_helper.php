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
 * @param string $dir The foods directory path
 * @return array|null ['file' => path, 'isVariant' => bool, 'variantName' => name|null]
 */
function bulk_find_food_source( $foodName, $dir )
{
  
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
 * @param string $dir The foods directory path
 * @return bool Success status
 */
function bulk_update_price( $foodName, $newPrice, $dir )
{
  return bulk_update_food_value( $foodName, 'price', $newPrice, $dir );
}

/**
 * Updates a dealPrice in a YAML file (supports both regular foods and variants)
 * 
 * @param string $foodName The food name
 * @param float $newDealPrice The new deal price
 * @param string $dir The foods directory path
 * @return bool Success status
 */
function bulk_update_deal_price( $foodName, $newDealPrice, $dir )
{
  return bulk_update_food_value( $foodName, 'dealPrice', $newDealPrice, $dir );
}

/**
 * Updates any value in a YAML file (supports both regular foods and variants)
 * 
 * @param string $foodName The food name
 * @param string $key The key to update (price, dealPrice, etc.)
 * @param mixed $newValue The new value
 * @param string $dir The foods directory path
 * @param bool $withHistory Whether to maintain price history (default: false for simple updates)
 * @return bool Success status
 */
function bulk_update_food_value( $foodName, $key, $newValue, $dir, $withHistory = false )
{
  $sourceInfo = bulk_find_food_source( $foodName, $dir );
  
  if( ! $sourceInfo )
    return false;
    
  $filePath = $sourceInfo['file'];
  
  if( ! file_exists($filePath))
    return false;
    
  $content = file_get_contents($filePath);
  
  if( ! $sourceInfo['isVariant'])
  {
    // Regular food - update value directly
    $updatedContent = bulk_replace_yaml_value( $content, $key, $newValue );
  }
  else
  {
    // Variant food - update specific variant
    $variantName = $sourceInfo['variantName'];
    
    if( $withHistory && ($key === 'price' || $key === 'dealPrice'))
    {
      // Handle price history for variants
      $updatedContent = bulk_update_variant_with_history( $content, $variantName, $key, $newValue );
    }
    else
    {
      // Simple update without history
      $updatedContent = bulk_replace_variant_value( $content, $variantName, $key, $newValue );
    }
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
 * Updates a value within a specific variant in YAML content
 * 
 * @param string $yamlContent The YAML content
 * @param string $variantName The variant name
 * @param string $key The key to update
 * @param mixed $newValue The new value
 * @return string Updated YAML content
 */
function bulk_replace_variant_value( $yamlContent, $variantName, $key, $newValue )
{
  $lines = explode("\n", $yamlContent);
  $inVariants = false;
  $foundVariant = false;
  $variantEndIndex = -1;
  
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
      
      // If we found another variant after ours, mark end of our variant
      if( $foundVariant && preg_match('/^\s*(["\']?)[^-\s].*?\1:\s*$/', $line))
      {
        $variantEndIndex = $i;
        break;
      }
      
      // Update the key in our variant if it exists
      if( $foundVariant && preg_match('/^(\s+)' . preg_quote($key, '/') . '(\s*:\s*)/', $line, $matches))
      {
        $indentation = $matches[1];
        $keyAndColon = $matches[2];
        $lines[$i] = $indentation . $key . $keyAndColon . $newValue;
        return implode("\n", $lines); // Found and updated, return immediately
      }
      
      // If we hit a non-indented line, mark end of variants section
      if( $foundVariant && preg_match('/^[a-zA-Z]/', $line))
      {
        $variantEndIndex = $i;
        break;
      }
    }
  }
  
  // If we found the variant but didn't find the key, add it in the right position
  if( $foundVariant )
  {
    $indentation = '    '; // 4 spaces for variant properties
    $newLine = $indentation . $key . ':         ' . $newValue;
    $insertIndex = -1;
    
    // Try to find a good position based on the key type
    if( $key === 'dealPrice' )
    {
      // Insert dealPrice right after price
      $insertIndex = bulk_find_insert_position_after( $lines, $variantName, 'price' );
    }
    
    if( $insertIndex > 0 )
    {
      // Insert at the calculated position
      array_splice($lines, $insertIndex, 0, [$newLine]);
    }
    elseif( $variantEndIndex > 0 )
    {
      // Insert before the next variant or section
      array_splice($lines, $variantEndIndex, 0, [$newLine]);
    }
    else
    {
      // Add at the end of the variant (end of file)
      $lines[] = $newLine;
    }
  }
  
  return implode("\n", $lines);
}

/**
 * Finds the best position to insert a new key after a specific key in a variant
 * 
 * @param array $lines The YAML lines
 * @param string $variantName The variant name
 * @param string $afterKey The key to insert after
 * @return int The line index to insert at, or -1 if not found
 */
function bulk_find_insert_position_after( $lines, $variantName, $afterKey )
{
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
      
      // If we found another variant, we're done
      if( $foundVariant && preg_match('/^\s*(["\']?)[^-\s].*?\1:\s*$/', $line))
      {
        break;
      }
      
      // Look for the afterKey in our variant
      if( $foundVariant && preg_match('/^(\s+)' . preg_quote($afterKey, '/') . '(\s*:\s*)/', $line))
      {
        return $i + 1; // Insert after this line
      }
      
      // If we hit a non-indented line, we're done with variants
      if( $foundVariant && preg_match('/^[a-zA-Z]/', $line))
      {
        break;
      }
    }
  }
  
  return -1; // Not found
}

/**
 * Updates a variant price with full history support
 * 
 * @param string $yamlContent The YAML content
 * @param string $variantName The variant name
 * @param string $key The key to update (price or dealPrice)
 * @param mixed $newValue The new value
 * @return string Updated YAML content
 */
function bulk_update_variant_with_history( $yamlContent, $variantName, $key, $newValue )
{
  // For now, let's implement a simplified version that just updates the value
  // and adds lastPriceUpd. Full history support would be quite complex.
  
  $today = (new DateTime())->format('Y-m-d');
  
  // Update the price/dealPrice
  $updatedContent = bulk_replace_variant_value( $yamlContent, $variantName, $key, $newValue );
  
  // Update lastPriceUpd
  $updatedContent = bulk_replace_variant_value( $updatedContent, $variantName, 'lastPriceUpd', $today );
  
  return $updatedContent;
}

?>
