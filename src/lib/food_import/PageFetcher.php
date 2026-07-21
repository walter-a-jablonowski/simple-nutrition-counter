<?php

/*

Fetches a product page over HTTP (import option A: paste a URL).

Vendors like REWE block obvious non-browser requests (a default curl User-Agent
returns 403), so we send a browser-like User-Agent. If the request is still
blocked, the caller should fall back to option B (paste the page HTML).

*/
class PageFetcher
{

  const BROWSER_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 '
                   . '(KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';


  // Returns the page HTML, or throws with a user-facing message on failure

  public static function fetch( string $url ) : string
  {
    if( ! preg_match('#^https?://#i', $url))
      throw new Exception('Please enter a valid http(s) URL.');

    $ch = curl_init($url);

    curl_setopt_array($ch,
    [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_MAXREDIRS      => 5,
      CURLOPT_TIMEOUT        => 20,
      CURLOPT_USERAGENT      => self::BROWSER_UA,
      CURLOPT_ENCODING       => '',   // accept + transparently decode gzip/br
      CURLOPT_HTTPHEADER     =>
      [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
        'Accept-Language: de-DE,de;q=0.9,en;q=0.8',
        'Upgrade-Insecure-Requests: 1',
        'Sec-Fetch-Dest: document',
        'Sec-Fetch-Mode: navigate',
        'Sec-Fetch-Site: none',
        'Sec-Fetch-User: ?1',
      ],
    ]);

    $html = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);

    curl_close($ch);

    if( $html === false )
      throw new Exception("Could not reach the page ($err). Try pasting the page HTML instead.");

    if( $code === 403 || $code === 429 )
      throw new Exception("The vendor blocked the request (HTTP $code). Please open the page in your browser and paste its HTML instead.");

    if( $code >= 400 )
      throw new Exception("The page returned HTTP $code. Please check the URL or paste the page HTML instead.");

    return $html;
  }
}

?>
