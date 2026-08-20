
( new prompt, unneeded )

Make a quick deploy script in tools/deploy.php that can be used to update version of the app in the installation folder with the current version.

$source_fld = '.';
$dest_fld   = '...';

$backup = [
  // files or folder to backup first in dest folder
];

$backup_fld = '_backup';

// backup format:
// - file:   _backup/sub/path/my_file_YYYYMMDD_HHMMSS.ext
// - folder: _backup/sub/path/my_file_YYYYMMDD_HHMMSS (recursive if we backup a folder)

$exclude = [
  // files or folder to exclude, each entry is a relative path with base source/dest folder
];
