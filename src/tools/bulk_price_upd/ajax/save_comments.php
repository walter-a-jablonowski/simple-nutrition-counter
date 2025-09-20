<?php

function handle_save_comments( array $payload ) : array
{
  $commentsFile = dirname(__DIR__) . '/data/comments.md';

  $content = isset($payload['content']) ? (string)$payload['content'] : null;
  if( $content === null ) {
    return [ 'status' => 'error', 'message' => 'Missing content' ];
  }

  try {
    // Ensure folder exists (it should, but be safe)
    $dir = dirname($commentsFile);
    if( ! is_dir($dir) ) {
      if( ! mkdir($dir, 0777, true) ) {
        return [ 'status' => 'error', 'message' => "Can't create dir" ];
      }
    }

    if( file_put_contents($commentsFile, $content) === false ) {
      return [ 'status' => 'error', 'message' => 'Failed to write file' ];
    }

    return [ 'status' => 'success' ];
  }
  catch( Throwable $e ) {
    return [ 'status' => 'error', 'message' => 'Unable to save comments' ];
  }
}
