<?php

/**
 * Reads secrets from the project root .env (api keys and such). Kept out of
 * config.yml because that file is committed.
 *
 * The .env sits in the app folder (src), so it IS inside the doc root. The .htaccess
 * next to it denies dotfiles - without that the key would be readable at /.env.
 *
 * @param string $key
 * @param mixed  $default  returned when the key or the file is missing
 * @return mixed
 */
function env_get( $key, $default = null )
{
  static $vars = null;

  if( $vars === null )
  {
    $vars = [];
    $file = __DIR__ . '/../.env';

    if( is_readable($file) )
    foreach( file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line )
    {
      $line = trim($line);

      if( $line === '' || $line[0] === '#' || ! str_contains( $line, '=') )
        continue;

      list( $name, $value) = explode('=', $line, 2);

      $vars[trim($name)] = trim( trim($value), '"\'');   // value may be quoted
    }
  }

  return $vars[$key] ?? $default;
}

?>
