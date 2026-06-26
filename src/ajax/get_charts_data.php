<?php

require_once 'lib/helper.php';

trait GetChartsDataAjaxController
{

  public function getChartsData( $request )
  {
    $config    = config::instance();
    $user      = $config->get('defaultUser');
    $sourceDir = "data/users/$user/days";

    $chartsConf = $config->get('charts') ?: [];
    $limits     = $chartsConf['limits']    ?? [];
    $avg        = $chartsConf['avg']       ?? 30;
    $movingAvg  = $chartsConf['movingAvg'] ?? 7;

    $data  = [];
    $flags = [];

    if( is_dir($sourceDir) )
    foreach( scandir($sourceDir) as $file )
    {
      if( $file === '.' || $file === '..' || ! str_ends_with($file, '.tsv') )
        continue;

      $date       = substr($file, 0, 10);
      $content    = file_get_contents("$sourceDir/$file");
      $parsedFile = parse_data_file($content);
      $lines      = explode("\n", $parsedFile['data']);

      // collect header flags for filtering in UI
      $hdrs = $parsedFile['headers'];
      $flags[$date] = [
        'unprecise'     => isset($hdrs['unprecise'])     && $hdrs['unprecise'],
        'unpreciseTime' => isset($hdrs['unpreciseTime']) && $hdrs['unpreciseTime']
      ];

      $daySum = [
        'calories'   => 0,
        'fat'        => 0,
        'carbs'      => 0,
        'amino'      => 0,
        'salt'       => 0,
        'price'      => 0,
        'eatingTime' => 0   // in minutes
      ];

      $firstTime = null;
      $lastTime  = null;

      foreach( $lines as $line )
      {
        if( empty( trim($line)) )  continue;

        // try to extract the time from the beginning of the line
        if( ! str_ends_with( trim($line), '--:--:--') )
          if( preg_match('/^(\d{2}:\d{2}(:\d{2})?)/', trim($line), $matches) )
          {
            $time = $matches[1];

            if( substr_count($time, ':') === 1 )
              $time .= ':00';

            if( $firstTime === null )
              $firstTime = $time;

            $lastTime = $time;
          }

        $columns = preg_split('/\s{2,}/', $line);
        if( count($columns) >= 9 )
        {
          $daySum['calories'] += floatval($columns[3]);
          $daySum['fat']      += floatval($columns[4]);
          $daySum['carbs']    += floatval($columns[5]);
          $daySum['amino']    += floatval($columns[6]);
          $daySum['salt']     += floatval($columns[7]);
          $daySum['price']    += floatval($columns[8]);
        }
      }

      // eating time in minutes
      if( $firstTime !== null && $lastTime !== null )
      {
        list($h1, $m1, $s1) = array_map('intval', explode(':', $firstTime));
        list($h2, $m2, $s2) = array_map('intval', explode(':', $lastTime));

        $diffSeconds = ($h2 * 3600 + $m2 * 60 + $s2) - ($h1 * 3600 + $m1 * 60 + $s1);
        if( $diffSeconds < 0 )
          $diffSeconds += 24 * 3600;

        $daySum['eatingTime'] = round($diffSeconds / 60);
      }

      $data[$date] = $daySum;
    }

    ksort($data);
    ksort($flags);

    return [
      'result' => 'success',
      'data'   => [
        'data'   => $data,
        'flags'  => $flags,
        'limits' => $limits,
        'config' => [
          'avg'       => $avg,
          'movingAvg' => $movingAvg
        ]
      ]
    ];
  }
}

?>
