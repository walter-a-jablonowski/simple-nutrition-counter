<?php

// --- Filesystem helpers (throw on failure so the deploy can report real success) ---

function fs_mkdir( $dir )
{
  if( is_dir($dir) )
    return;

  if( ! @mkdir($dir, 0755, true) )
    throw new RuntimeException("Could not create directory: $dir");
}

function fs_copy( $src, $dest )
{
  if( ! @copy($src, $dest) )
    throw new RuntimeException("Could not copy: $src to $dest");
}

function fs_unlink( $file )
{
  if( ! @unlink($file) )
    throw new RuntimeException("Could not remove file: $file");
}

function fs_rmdir( $dir )
{
  if( ! @rmdir($dir) )
    throw new RuntimeException("Could not remove directory: $dir");
}


// --- Path helpers ---

function normalize_path( $path )
{
  return str_replace('\\', '/', $path);
}

function get_relative_path( $path, $base )
{
  $path = normalize_path($path);
  $base = rtrim( normalize_path($base), '/');

  if( $path === $base )
    return '';

  if( str_starts_with($path, "$base/") )
    return substr( $path, strlen($base) + 1 );

  return $path;
}


// --- Backup ---

function backup( $sources, $backupDir, $base, $zip = false, $python = 'python' )
{
  // keep only items that actually exist (file or folder), never desktop.ini
  $sources = array_filter( $sources, fn( $src ) =>
    strtolower( basename($src)) !== 'desktop.ini' && ( is_dir($src) || is_file($src))
  );

  if( ! $sources )
    return;                          // nothing to back up: create no empty folder

  $stamp = date('ymd_His');          // with seconds: two runs in the same minute
                                     // would share a name and overwrite

  if( $zip )
  {
    fs_mkdir($backupDir);
    backup_to_zip( $sources, "$backupDir/$stamp.zip", $base, $python );
    return;
  }

  $destDir = "$backupDir/$stamp";
  fs_mkdir($destDir);

  foreach( $sources as $source )
  {
    $sub = get_relative_path( $source, $base );

    if( is_dir($source) )
    {
      fs_mkdir("$destDir/$sub");
      cp_recursive( $source, $destDir, $base );
    }
    else  // file
    {
      fs_mkdir( dirname("$destDir/$sub"));
      fs_copy( $source, "$destDir/$sub");
    }
  }
}

function cp_recursive( $dir, $destDir, $base )
{
  foreach( scandir($dir) as $fil )
  {
    if( in_array( $fil, ['.', '..']))
      continue;

    if( strtolower($fil) === 'desktop.ini' )
      continue;

    $sub = get_relative_path( $dir, $base );

    if( is_dir("$dir/$fil") )
    {
      fs_mkdir("$destDir/$sub/$fil");
      cp_recursive("$dir/$fil", $destDir, $base );
    }
    else
      fs_copy("$dir/$fil", "$destDir/$sub/$fil");
  }
}

// Pack all backup items into one archive. zip_helper.py does the zipping, it gets
// the item list as a temporary json manifest: a command line with many long paths
// containing spaces would be fragile, and the list grows with DEPLOY_BACKUP.

function backup_to_zip( $sources, $zipFile, $base, $python )
{
  $items = [];
  foreach( $sources as $source )
    $items[] = [
      'source' => normalize_path($source),
      'arc'    => get_relative_path( $source, $base )
    ];

  $manifest = ['zip' => normalize_path($zipFile), 'items' => $items];

  $manifestFile = tempnam( sys_get_temp_dir(), 'deploy_zip_');
  if( $manifestFile === false )
    throw new RuntimeException('Could not create the zip manifest');

  file_put_contents( $manifestFile,
    json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
  );

  // $python stays unescaped: it is a plain command name from config.php, and on
  // Windows a fully quoted command line is mangled by cmd.exe

  $cmd = "$python " . escapeshellarg(__DIR__ . '/zip_helper.py')
       . ' ' . escapeshellarg($manifestFile);

  exec("$cmd 2>&1", $output, $status );
  @unlink($manifestFile);

  if( $status !== 0 )
    throw new RuntimeException("Could not create the zip archive: $zipFile\n"
      . implode("\n", $output)
    );

  echo "  Backup archive: $zipFile\n";
}

?>
