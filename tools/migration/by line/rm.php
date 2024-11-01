<?php

chdir('../../../src');

// $dir = 'data/users/JaneDoe@example.com-24080101000000/days';
// $dir = 'days';
$dir = 'data/bundles/Default_JaneDoe@example.com-24080101000000/foods';
// $dir = 'foods';

foreach( scandir($dir) as $file )
{
  if( pathinfo($file, PATHINFO_EXTENSION) !== 'yml' || in_array( $file[0], ['_']))
    continue;

  $content = file_get_contents("$dir/$file");

  $r = [];

  foreach( explode("\n", $content) as $line )
  {
    if( strpos( $line, 'state:') === false )
      $r[] = $line;
  }

  file_put_contents("$dir/$file", trim( implode("\n", $r)) . "\n");
}

echo 'Done';

?>
