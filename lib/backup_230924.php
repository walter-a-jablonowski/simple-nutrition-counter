<?php

/*

prefer cause we have on backup form for all dools
(would be hard 2 keep this consistent in all dools)

usage

if( ! backup_fil( $source ))  // prefer cause we have on backup form for all dools
  echo json_encode(['result' => 'error', 'message' => 'Error making backup']);

*/
function backup_fil( $source )
{
  $backupDir = dirname($source) . "/.sys/_backup";

  if( ! is_dir( $backupDir))
    if( ! mkdir( $backupDir, 0777, true))
      return false;

  $backupFil = pathinfo($source)['filename']
            . '_' . date('Ymd-His')
            . ( pathinfo($source)['extension'] ? '.' . pathinfo($source)['extension'] : '');

  if( ! file_put_contents("$backupDir/$backupFil", file_get_contents($source)))
    return false;
  
  return true;
}

?>
