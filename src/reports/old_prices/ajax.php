<?php

chdir('../..');

header('Content-Type: application/json; charset=utf-8');

// Composer autoload (project root vendor)
require_once 'vendor/autoload.php';

// Only JSON POST for now
if( $_SERVER['REQUEST_METHOD'] !== 'POST')
{
  http_response_code(405);
  echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
  exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if( ! is_array($payload))
{
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
  exit;
}

$action = isset($payload['action']) ? (string)$payload['action'] : '';

try {

  if( $action === 'save_import')
  {
    require_once __DIR__ . '/ajax/save_import.php';
    $result = handle_save_import($payload);
    echo json_encode($result);
    exit;
  }
  
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
}
catch( Throwable $e ) {
  http_response_code(500);
  echo json_encode(['status' => 'error', 'message' => 'Server error']);
}
