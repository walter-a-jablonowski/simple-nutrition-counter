<?php

use Symfony\Component\Yaml\Yaml;

class DiagramController 
{
  private $config;
  private $sourceDir;
  
  public function __construct() 
  {
    $this->config    = Yaml::parseFile('config.yml');
    $this->sourceDir = $this->config['source'];
  }

  public function getData()
  {
    $files = scandir($this->sourceDir);
    $data = [];
    
    foreach($files as $file)
    {
      if( $file === '.' || $file === '..' || ! str_ends_with($file, '.tsv')) 
        continue;
        
      $date    = substr($file, 0, 10);
      $content = file_get_contents($this->sourceDir . '/' . $file);
      $lines   = explode("\n", $content);
      
      $daySum = [
        'calories' => 0,
        'fat'      => 0,
        'carbs'    => 0,
        'amino'    => 0,
        'salt'     => 0,
        'price'    => 0
      ];
      
      foreach( $lines as $line )
      {
        if( empty( trim($line)))  continue;
        
        $columns = preg_split('/\s{2,}/', $line);
        if( count($columns) >= 9)
        {
          $daySum['calories'] += floatval($columns[3]);
          $daySum['fat']      += floatval($columns[4]);
          $daySum['carbs']    += floatval($columns[5]);
          $daySum['amino']    += floatval($columns[6]);
          $daySum['salt']     += floatval($columns[7]);
          $daySum['price']    += floatval($columns[8]);
        }
      }
      
      $data[$date] = $daySum;
    }
    
    ksort($data);

    return [
      'data' => $data,
      'limits' => isset($this->config['limits']) ? $this->config['limits'] : [],
      'config' => [
        'movingAvg' => isset($this->config['movingAvg']) ? $this->config['movingAvg'] : 7
      ]
    ];
  }
}
