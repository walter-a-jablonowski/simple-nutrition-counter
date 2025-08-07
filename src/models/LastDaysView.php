<?php

trait LastDaysView
{
  protected SimpleData $lastDaysView;
  protected array      $avg;


  private function makeLastDaysView()
  {
    $config   = config::instance();
    $settings = settings::instance();

    // Day list

    $this->lastDaysView = new SimpleData();
    $data = [];  $i = 1;

    foreach( scandir('data/users/' . $config->get('defaultUser') . '/days', SCANDIR_SORT_DESCENDING) as $file)
    {
      $dat = pathinfo($file, PATHINFO_FILENAME);

      if( pathinfo($file, PATHINFO_EXTENSION) !== 'tsv' || $dat === date('Y-m-d'))  // hide current day in last days tab
        continue;

      $i++;  if( $i > 30 )  break;  // leave here cause of first day hidden
      $fileContent = file_get_contents('data/users/' . $config->get('defaultUser') . "/days/$file");
      $parsedFile = parse_data_file($fileContent);
      $entries = parse_tsv( $parsedFile['data'], self::DAY_HEADERS);

      // foreach( $entries as $idx => $entry)  // TASK: for fibre
      //   $entries[$idx][7] = Yaml::parse( $entries[$idx][7] );

      $data[$dat] = $entries;

      $this->lastDaysView->set( $dat, [
        'calories' => ( ! $entries ? 0 : array_sum( array_column($entries, 'calories'))),
        'fat'      => ( ! $entries ? 0 : array_sum( array_column($entries, 'fat'))),
        'carbs'    => ( ! $entries ? 0 : array_sum( array_column($entries, 'carbs'))),
        'amino'    => ( ! $entries ? 0 : array_sum( array_column($entries, 'amino'))),
        'salt'     => ( ! $entries ? 0 : array_sum( array_column($entries, 'salt'))),
        'price'    => ( ! $entries ? 0 : array_sum( array_column($entries, 'price')))
      ]);
    }

    // Avg

    $currentDate = new DateTime();  // TASK: maybe also look if current date is in data so that we have current data
    $attributes  = ['price', 'calories', 'fat', 'carbs', 'amino', 'salt'];
    $sums = [];

    foreach([7, 15, 30] as $period )
    {
      $days = array_slice($data, 0, $period);

      foreach( $attributes as $attr )
      {
        if( ! isset($sums[$attr][$period]))
          $sums[$attr][$period] = 0;

        foreach( $days as $day )
          $sums[$attr][$period] += array_sum( array_column( $day, $attr));
      }
    }

    foreach( $attributes as $attr )
    {
      if( $attr === 'price' )

        $this->avg[$attr] = [
          'week'   => ! $sums[$attr][7]  ? 'n/a' : round($sums[$attr][7]  / 7, 2),
          '15days' => ! $sums[$attr][15] ? 'n/a' : round($sums[$attr][15] / 15, 2),
          '30days' => ! $sums[$attr][30] ? 'n/a' : round($sums[$attr][30] / 30, 2)
        ];

      else

        $this->avg[$attr] = [
          'week'   => ! $sums[$attr][7]  ? 'n/a' : round($sums[$attr][7]  / 7),
          '15days' => ! $sums[$attr][15] ? 'n/a' : round($sums[$attr][15] / 15),
          '30days' => ! $sums[$attr][30] ? 'n/a' : round($sums[$attr][30] / 30)
        ];
    }
  }
}

?>
