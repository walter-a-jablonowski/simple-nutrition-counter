<?php

chdir('..');

// we use no backup here, just start from scratch if error

// $data = file_get_contents('php://input');
$data = json_decode( file_get_contents('php://input'), true);

// file_put_contents( $fil, $data);

if( ! file_put_contents('data/days/' . date('Y-m-d') . '.tsv', $data['data']))
  echo json_encode(['result' => 'error', 'message' => 'Error saving']);

echo json_encode(['result' => 'success']);

?>
