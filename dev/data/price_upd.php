<?php

// automating price upd is hard cause prices usually loaded dynamically via js

echo fetchPrice('');

function fetchPrice($url)
{
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

  $html = curl_exec($ch);
  curl_close($ch);

  // file_put_contents('out.html', $html);
  // exit();

  if( ! $html )
    return "Failed to fetch page";

  preg_match('//', $html, $matches);

  if( isset($matches[1]))
    return trim($matches[1]);
  else 
    return "Price missing";
}

?>
