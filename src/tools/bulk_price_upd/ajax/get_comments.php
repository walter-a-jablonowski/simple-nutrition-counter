<?php

function handle_get_comments() : array
{
  // comments.md is located one level up from this ajax/ folder
  $commentsFile = dirname(__DIR__) . '/comments.md';

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
