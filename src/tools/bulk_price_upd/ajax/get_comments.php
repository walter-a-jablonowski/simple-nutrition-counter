<?php

function handle_get_comments() : array
{
  // comments.md is now stored under the data/ subfolder
  $commentsFile = dirname(__DIR__) . '/data/comments.md';

  try {
    $content = '';
    if( is_file($commentsFile) ) {
      $content = file_get_contents($commentsFile);
      if( $content === false ) $content = '';
    }
    return [
      'status' => 'success',
      'data' => [ 'content' => $content ]
    ];
  }
  catch( Throwable $e ) {
    return [
      'status' => 'error',
      'message' => 'Unable to load comments'
    ];
  }
}
