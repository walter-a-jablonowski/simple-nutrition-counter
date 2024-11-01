<?php

// chdir('../../src');

// $dir = 'data/users/JaneDoe@example.com-24080101000000/days';
$dir = 'days';

foreach( scandir($dir) as $file )
{
  if( pathinfo($file, PATHINFO_EXTENSION) !== 'tsv')
    continue;

  $content = trim( file_get_contents("$dir/$file"));
  $lines   = explode("\n", $content);

  $modifiedLines = array_map( function($line) {
    // return '00:00:00  ' . $line;
    return '00:00:00  ' . $line;
  }, $lines);

  file_put_contents("$dir/$file", implode("\n", $modifiedLines));
}

echo 'Done';

?>
