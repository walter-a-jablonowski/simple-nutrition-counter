<?php

use Symfony\Component\Yaml\Yaml;

/*

Publishes the food data to the installation folder.

Change detection is by SHA-1, never by file time: the destination is a Google
Drive folder, and Drive rewrites timestamps on sync, so a time there says
nothing about the content. A manifest of the last publish (published.json, next
to this file) holds the hash of every file we sent, which keeps the normal run
from having to read the destination at all. When a path is missing from the
manifest we fall back to hashing the destination file, so a first run does not
re-copy everything.

*/
class Publisher
{
  private string $repoRoot;
  private string $destRoot;
  private string $backupRoot;
  private array  $sources;
  private array  $ignore;
  private string $manifestFile;

  private ?string $backupDir = null;   // created lazily, on the first file we save

  public function __construct( string $toolDir )
  {
    $config = Yaml::parseFile("$toolDir/config.yml");

    $this->repoRoot     = str_replace('\\', '/', dirname( $toolDir, 3));  // tools/publish_foods -> src -> repo
    $this->destRoot     = rtrim( str_replace('\\', '/', $config['destination']), '/');
    $this->backupRoot   = rtrim( str_replace('\\', '/', $config['backup'] ?? ''), '/');
    $this->sources      = $config['sources'] ?? [];
    $this->ignore       = $config['ignore']  ?? [];
    $this->manifestFile = "$toolDir/published.json";
  }

  /*@

  What a run would change: repo-relative paths per bucket, plus the number of
  files that are already up to date.

  */
  public function plan() : array /*@*/
  {
    $manifest = $this->readManifest();
    $current  = $this->scanSources();

    $plan = ['new' => [], 'changed' => [], 'deleted' => [], 'unchanged' => 0];

    foreach( $current as $path => $sha1 )
    {
      $known = $manifest[$path]['sha1'] ?? null;

      if( $known === null )                        // not published yet by this manifest
        $known = is_file("$this->destRoot/$path") ? sha1_file("$this->destRoot/$path") : null;

      if( $known === null )
        $plan['new'][] = $path;
      elseif( $known !== $sha1 )
        $plan['changed'][] = $path;
      else
        $plan['unchanged']++;
    }

    foreach( array_keys($manifest) as $path )      // published before, gone from the sources now
      if( ! isset($current[$path]) && is_file("$this->destRoot/$path"))
        $plan['deleted'][] = $path;

    sort($plan['new']);  sort($plan['changed']);  sort($plan['deleted']);

    return $plan;
  }

  /*@

  Applies a freshly built plan. Obsolete files are only removed when $delete is
  set; otherwise they stay and keep being reported.

  */
  public function run( bool $delete = false ) : array /*@*/
  {
    $plan   = $this->plan();
    $errors = [];
    $copied = 0;
    $erased = 0;

    $this->backupDir = null;   // one folder per run

    // New files have no destination version, so only changed ones need saving

    foreach( $plan['changed'] as $path )
      if( ! $this->backupFile($path, $errors))
        return ['plan' => $plan, 'copied' => 0, 'deleted' => 0, 'backupDir' => $this->backupDir,
                'errors' => array_merge($errors, ['Stopped before copying anything.'])];

    if( $delete )
      foreach( $plan['deleted'] as $path )
        if( ! $this->backupFile($path, $errors))
          return ['plan' => $plan, 'copied' => 0, 'deleted' => 0, 'backupDir' => $this->backupDir,
                  'errors' => array_merge($errors, ['Stopped before deleting anything.'])];

    foreach( array_merge($plan['new'], $plan['changed']) as $path )
      $this->copyFile($path, $errors) ? $copied++ : null;

    if( $delete )
      foreach( $plan['deleted'] as $path )
      {
        if( @unlink("$this->destRoot/$path"))
          $erased++;
        else
          $errors[] = "Could not delete $path";
      }

    $this->writeManifest( $this->scanSources(), $delete ? [] : $plan['deleted']);

    return ['plan'    => $plan,      'copied' => $copied, 'deleted' => $erased,
            'backupDir' => $this->backupDir, 'errors' => $errors];
  }

  /*@

  The monospace report shown in the dialog and printed by the CLI.

  */
  public function reportLines( array $plan, bool $delete = false ) : array /*@*/
  {
    $lines = [];

    foreach( $plan['new'] as $path )      $lines[] = "NEW  $path";
    foreach( $plan['changed'] as $path )  $lines[] = "CHG  $path";

    foreach( $plan['deleted'] as $path )
      $lines[] = ($delete ? 'DEL  ' : 'OBS  ') . $path;

    if( ! $lines )
      $lines[] = 'Nothing to publish, everything is up to date.';

    $lines[] = '';
    $lines[] = sprintf('%d new, %d changed, %d obsolete, %d unchanged',
                 count($plan['new']), count($plan['changed']), count($plan['deleted']), $plan['unchanged']);

    return $lines;
  }

  // Every source file with its hash, keyed by repo-relative path

  private function scanSources() : array
  {
    $files = [];

    foreach( $this->sources as $source )
    {
      $full = "$this->repoRoot/$source";

      if( is_file($full))
        $files[$source] = sha1_file($full);
      elseif( is_dir($full))
        $this->scanDir($full, $source, $files);
    }

    return $files;
  }

  private function scanDir( string $full, string $relative, array &$files )
  {
    foreach( scandir($full) as $entry )
    {
      if( $entry === '.' || $entry === '..' || in_array($entry, $this->ignore, true))
        continue;

      is_dir("$full/$entry") ? $this->scanDir("$full/$entry", "$relative/$entry", $files)
                             : $files["$relative/$entry"] = sha1_file("$full/$entry");
    }
  }

  /*@

  Saves the destination's current version of a file before we overwrite or
  delete it, keeping its sub path inside the run's backup folder. A failure here
  aborts the whole run: publishing without the safety net is worse than not
  publishing. Returns true when there was nothing to save.

  */
  private function backupFile( string $path, array &$errors ) : bool /*@*/
  {
    if( $this->backupRoot === '' || ! is_file("$this->destRoot/$path"))
      return true;

    if( $this->backupDir === null && ! $this->makeBackupDir($errors))
      return false;

    $target = "$this->backupDir/$path";

    if( ! is_dir( dirname($target)) && ! @mkdir( dirname($target), 0777, true))
    {
      $errors[] = "Could not create the backup folder for $path";
      return false;
    }

    if( @copy("$this->destRoot/$path", $target))
      return true;

    $errors[] = "Could not back up $path";
    return false;
  }

  // Next free "YYYY-MM-DD_NN" for today

  private function makeBackupDir( array &$errors ) : bool
  {
    if( ! is_dir($this->backupRoot) && ! @mkdir($this->backupRoot, 0777, true))
    {
      $errors[] = "Backup folder $this->backupRoot is not available";
      return false;
    }

    $today = date('Y-m-d');
    $used  = 0;

    foreach( scandir($this->backupRoot) as $entry )
      if( preg_match('/^' . preg_quote($today, '/') . '_(\d+)$/', $entry, $m))
        $used = max($used, (int) $m[1]);

    $dir = sprintf('%s/%s_%02d', $this->backupRoot, $today, $used + 1);

    if( ! @mkdir($dir, 0777, true))
    {
      $errors[] = "Could not create the backup folder $dir";
      return false;
    }

    $this->backupDir = $dir;
    return true;
  }

  private function copyFile( string $path, array &$errors ) : bool
  {
    $target = "$this->destRoot/$path";
    $dir    = dirname($target);

    if( ! is_dir($dir) && ! @mkdir($dir, 0777, true))
    {
      $errors[] = "Could not create folder for $path";
      return false;
    }

    if( @copy("$this->repoRoot/$path", $target))
      return true;

    $errors[] = "Could not copy $path";
    return false;
  }

  private function readManifest() : array
  {
    if( ! is_file($this->manifestFile))
      return [];

    $data = json_decode( file_get_contents($this->manifestFile), true);

    return $data['files'] ?? [];
  }

  // $keep: obsolete files we did not delete, so they stay known and keep being reported

  private function writeManifest( array $files, array $keep )
  {
    $manifest = [];

    foreach( $files as $path => $sha1 )
      $manifest[$path] = ['sha1' => $sha1, 'size' => filesize("$this->repoRoot/$path")];

    $old = $this->readManifest();

    foreach( $keep as $path )
      $manifest[$path] = $old[$path] ?? ['sha1' => '', 'size' => 0];

    ksort($manifest);

    file_put_contents( $this->manifestFile,
      json_encode(['lastRun' => date('Y-m-d H:i:s'), 'files' => $manifest], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
  }
}

?>
