<?php

require_once 'lib/food_import/FoodParser.php';
require_once 'lib/food_import/ReweParser.php';
require_once 'lib/food_import/PageFetcher.php';

/*

Orchestrates food import: takes a URL or pasted HTML, picks the matching vendor
parser, and returns a normalized food-data array ready for the import form.

Add a new vendor by implementing FoodParser and registering it in `parsers()`.

*/
class FoodImporter
{

  // Registered vendor parsers (first match wins)

  private static function parsers() : array
  {
    return [ new ReweParser() ];
  }


  // Import from a page URL (option A). May throw if the vendor blocks the request.

  public static function fromUrl( string $url ) : array
  {
    return self::fromHtml( PageFetcher::fetch($url), $url );
  }


  // Import from pasted page HTML (option B)

  public static function fromHtml( string $html, ?string $url = null ) : array
  {
    if( trim($html) === '')
      throw new Exception('No page content to import.');

    foreach( self::parsers() as $parser )
      if( $parser->matches($html, $url))
        return self::finalize( $parser->parse($html, $url));

    throw new Exception('No importer matched this page. Supported vendors: REWE.');
  }


  // Add source metadata and the current date, drop empty values

  private static function finalize( array $food ) : array
  {
    $today = date('Y-m-d');

    $food['sources']      = ['nutriVal' => 'web'];
    $food['lastUpd']      = $today;

    if( ! empty($food['price']))
      $food['lastPriceUpd'] = $today;

    return $food;
  }
}

?>
