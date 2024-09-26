<?php

if( ! is_dir('debug/backup'))
  mkdir('debug/backup', 0755, true);

makeDebug('debug/source');
makeDebug('debug/dest');

echo 'Done';

function makeDebug($baseDir)
{
  if( ! is_dir($baseDir))
    mkdir($baseDir, 0755, true);

  $dirs = [
    "$baseDir/folder1",
    "$baseDir/folder2",
    "$baseDir/folder3",
    "$baseDir/folder1/subfolder1",
    "$baseDir/folder2/subfolder2",
    "$baseDir/folder1/bootstrap-icons-1.11.3",  // This matches your $keep array
    "$baseDir/days",                            // This matches your $keep array
  ];

  foreach( $dirs as $dir )
    if( ! is_dir($dir))
      mkdir($dir, 0755, true);

  // create some files
  
  $files = [
    "$baseDir/file1.txt" => "This is file 1",
    "$baseDir/file2.php" => "<?php echo 'This is file 2'; ?>",
    "$baseDir/folder1/file3.txt" => "This is file 3 in folder1",
    "$baseDir/folder1/subfolder1/file4.txt" => "This is file 4 in subfolder1",
    "$baseDir/folder2/file5.html" => "<html><body>This is file 5</body></html>",
    "$baseDir/folder2/subfolder2/file6.css" => "body { color: blue; }",
    "$baseDir/folder3/file7.js" => "console.log('This is file 7');",
    "$baseDir/folder1/bootstrap-icons-1.11.3/icon.svg" => "<svg>...</svg>",
    "$baseDir/days/day1.txt" => "Content for day 1",
  ];

  foreach( $files as $file => $content)
    file_put_contents($file, $content);
}

?>
