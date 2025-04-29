<?php

use Symfony\Component\Yaml\Yaml;

class PriceImporter
{
  private $sourcePath;
  private $destinationPath;
  private $sourceData;
  private $updatedFilesCount = 0;
  private $updatedPricesCount = 0;

  public function __construct( $sourcePath, $destinationPath )
  {
    $this->sourcePath = $sourcePath;
    $this->destinationPath = $destinationPath;
    
    if( ! file_exists($this->sourcePath) )
      throw new Exception("Source file {$this->sourcePath} missing");
      
    if( ! is_dir($this->destinationPath) )
      throw new Exception("Destination directory {$this->destinationPath} does missing");
  }

  public function run() : array
  {
    $this->loadSourceData();
    $this->processDestinationFiles();
    
    return [
      'updatedFiles' => $this->updatedFilesCount,
      'updatedPrices' => $this->updatedPricesCount
    ];
  }

  private function loadSourceData()
  {
    $this->sourceData = Yaml::parseFile($this->sourcePath);
  }

  private function processDestinationFiles()
  {
    $foodFiles = $this->getFoodFiles();
    
    foreach( $foodFiles as $file )
      $this->processFile($file);
  }

  private function getFoodFiles() : array
  {
    $files = [];
    $dirContents = scandir($this->destinationPath);
    
    foreach( $dirContents as $item )
    {
      $path = $this->destinationPath . '/' . $item;
      
      // Only process yml files that don't start with underscore
      if( is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'yml' && $item[0] !== '_' )
        $files[] = $path;
      
      // Process subdirectories for "-this.yml" files
      if( is_dir($path) && $item !== '.' && $item !== '..' )
      {
        $subDirContents = scandir($path);
        foreach( $subDirContents as $subItem )
        {
          if( $subItem === '-this.yml' )
            $files[] = $path . '/' . $subItem;
        }
      }
    }
    
    return $files;
  }
  
  private function processFile( $filePath )
  {
    // Read the destination file without parsing it to preserve formatting
    $fileContent = file_get_contents($filePath);
    $foodData = Yaml::parse($fileContent);
    
    if( ! isset($foodData['keywords']) )
      return;  // Skip files without keywords
    
    $matchingSourceItems = $this->findMatchingSourceItems($foodData['keywords']);
    if( empty($matchingSourceItems) )
      return;  // No matching items found
      
    $updated = false;
    
    foreach( $matchingSourceItems as $sourceItem )
    {
      if( ! isset($sourceItem['lastPriceUpd']) )
        continue;
        
      $updated = $this->updatePrice($fileContent, $foodData, $sourceItem, 'price') || $updated;
      $updated = $this->updatePrice($fileContent, $foodData, $sourceItem, 'dealPrice') || $updated;
      
      if( $updated )
      {
        // Update lastPriceUpd in the file content if source has a newer date
        if( isset($sourceItem['lastPriceUpd']) && 
            ( ! isset($foodData['lastPriceUpd']) || $sourceItem['lastPriceUpd'] > $foodData['lastPriceUpd'] ) )
          $fileContent = $this->replaceYamlValue($fileContent, 'lastPriceUpd', $sourceItem['lastPriceUpd']);
        
        file_put_contents($filePath, $fileContent);
        $this->updatedFilesCount++;
        break;  // We only need to update from one matching source item
      }
    }
  }
  
  private function findMatchingSourceItems( $keywords )
  {
    if( ! is_array($keywords) )
      $keywords = [$keywords];
      
    $matchingItems = [];
    
    // Recursively search through the source data for items with matching keywords
    $this->findMatchesRecursive($this->sourceData, $keywords, $matchingItems);
    
    return $matchingItems;
  }
  
  private function findMatchesRecursive( $data, $targetKeywords, &$matches )
  {
    if( ! is_array($data) )
      return;
      
    foreach( $data as $key => $value )
    {
      if( is_array($value) && isset($value['keywords']) )
      {
        $sourceKeywords = $value['keywords'];
        if( ! is_array($sourceKeywords) )
          $sourceKeywords = [$sourceKeywords];
          
        foreach( $targetKeywords as $targetKeyword )
        {
          if( in_array($targetKeyword, $sourceKeywords) )
          {
            $matches[] = $value;
            break;
          }
        }
      }
      else if( is_array($value) )
        $this->findMatchesRecursive($value, $targetKeywords, $matches);
    }
  }
  
  private function updatePrice( &$fileContent, $foodData, $sourceItem, $priceKey )
  {
    if( ! isset($sourceItem[$priceKey]) || $sourceItem[$priceKey] === '' )
      return false;
      
    $newPrice = $sourceItem[$priceKey];
    $historyKey = $priceKey . 's';  // "price" -> "prices", "dealPrice" -> "dealPrices"
    
    // Add old price to history if it exists
    if( isset($foodData[$priceKey]) && $foodData[$priceKey] !== $newPrice )
    {
      $date = isset($foodData['lastPriceUpd']) ? $foodData['lastPriceUpd'] : date('Y-m-d');
      
      // Add the old price to the prices history
      if( isset($foodData[$historyKey]) )
      {
        // History already exists, append to it
        $fileContent = $this->addPriceToHistory($fileContent, $historyKey, $date, $foodData[$priceKey]);
      }
      else
      {
        // Create new history section
        $fileContent = $this->createPriceHistory($fileContent, $historyKey, $date, $foodData[$priceKey]);
      }
      
      // Update the current price
      $fileContent = $this->replaceYamlValue($fileContent, $priceKey, $newPrice);
      $this->updatedPricesCount++;
      return true;
    }
    else if( ! isset($foodData[$priceKey]) )
    {
      // Add price field if it doesn't exist (before weight field)
      $fileContent = $this->addPriceField($fileContent, $priceKey, $newPrice);
      $this->updatedPricesCount++;
      return true;
    }
    
    return false;
  }
  
  private function replaceYamlValue( $content, $key, $value )
  {
    $pattern = "/($key\s*:\s*)([^\n]*)/";
    return preg_replace($pattern, '$1' . $value, $content);
  }
  
  private function addPriceToHistory( $content, $historyKey, $date, $price )
  {
    $indent = "  ";
    $pattern = "/($historyKey\s*:\s*\n(\s*\n)?)/";
    $replacement = "$1$indent$date: $price  # added by price importer\n";
    
    if( preg_match($pattern, $content) )
    {
      return preg_replace($pattern, $replacement, $content);
    }
    
    // If pattern missing, try to find history section without trailing newline
    $pattern = "/($historyKey\s*:\s*[^\n]*\n)/";
    $replacement = "$1$indent$date: $price  # added by price importer\n";
    
    return preg_replace($pattern, $replacement, $content);
  }
  
  private function createPriceHistory( $content, $historyKey, $date, $price )
  {
    $newSection = "\n$historyKey:\n\n  $date: $price  # added by price importer\n";
    
    // Add it at the end of the file
    return $content . $newSection;
  }
  
  private function addPriceField( $content, $priceKey, $value )
  {
    // Add the price field before the weight field
    $pattern = "/(weight\s*:\s*[^\n]*\n)/";
    $replacement = "$priceKey:             $value\n$1";
    
    return preg_replace($pattern, $replacement, $content);
  }
}
