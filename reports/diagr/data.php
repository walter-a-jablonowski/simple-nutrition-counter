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
    $data = [];
    
    foreach( scandir($this->sourceDir) as $file )
    {
      if( $file === '.' || $file === '..' || ! str_ends_with($file, '.tsv')) 
        continue;
      
      $date    = substr($file, 0, 10);
      $content = file_get_contents( $this->sourceDir . '/' . $file);
      $lines   = explode("\n", $content);
      
      $daySum = [
        'calories'   => 0,
        'fat'        => 0,
        'carbs'      => 0,
        'amino'      => 0,
        'salt'       => 0,
        'price'      => 0,
        'eatingTime' => 0  // Initialize eating time in minutes
      ];
      
      $firstTime = null;
      $lastTime  = null;
      
      foreach( $lines as $line )
      {
        if( empty( trim($line)))  continue;

        // First, try to extract the time from the beginning of the line

        if( ! str_ends_with( trim($line), '--:--:--'))
          if( preg_match('/^(\d{2}:\d{2}(:\d{2})?)/', trim($line), $matches) ) {

            $time = $matches[1];
            
            // Add seconds if not present
            if( substr_count($time, ':') === 1 )
              $time .= ':00';
            
            if( $firstTime === null )
              $firstTime = $time;

            $lastTime = $time;
          }
        
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
      
      // Calculate eating time in minutes
      if( $firstTime !== null && $lastTime !== null ) {
        list($hours1, $minutes1, $seconds1) = array_map('intval', explode(':', $firstTime));
        list($hours2, $minutes2, $seconds2) = array_map('intval', explode(':', $lastTime));
        
        $diffSeconds = ($hours2 * 3600 + $minutes2 * 60 + $seconds2) - ($hours1 * 3600 + $minutes1 * 60 + $seconds1);
        if( $diffSeconds < 0 ) {
          $diffSeconds += 24 * 3600;
        }
        
        // Convert to minutes for the chart
        $daySum['eatingTime'] = round($diffSeconds / 60);
      }
      
      $data[$date] = $daySum;
    }
    
    ksort($data);

    return [
      'data' => $data,
      'limits' => isset($this->config['limits']) ? $this->config['limits'] : [],
      'config' => [
        'avg'       => isset($this->config['avg']) ? $this->config['avg'] : 30,
        'movingAvg' => isset($this->config['movingAvg']) ? $this->config['movingAvg'] : 7
      ]
    ];
  }
}
