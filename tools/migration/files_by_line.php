<?php

// chdir('../../src');

/*

AI: Make a PHP tool that

- loops all tsv files in a folder
- reads the trimmed content
- splits the content by line break
- each line: prepend the string "F  "
- save the modified file

Use scandir() for reading the files and indent all lines with 2 spaces.

*/

// $dir = 'data/users/JaneDoe@example.com-24080101000000/days';
$dir = 'days';

foreach( scandir($dir) as $file )
{
  if( pathinfo($file, PATHINFO_EXTENSION) !== 'tsv')
    continue;

  $content = trim( file_get_contents("$dir/$file"));
  $lines   = explode("\n", $content);

  $modifiedLines = array_map( function($line) {
    // return 'F   ' . $line;
    return '00:00:00  ' . $line;  // TASK: if needed also [] => {}
  }, $lines);

  file_put_contents("$dir/$file", implode("\n", $modifiedLines));
}

echo 'Done';

?>
