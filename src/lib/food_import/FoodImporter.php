<?php

require_once 'lib/food_import/FoodParser.php';
require_once 'lib/food_import/ReweParser.php';
require_once 'lib/food_import/EdekaParser.php';
require_once 'lib/food_import/PageFetcher.php';
require_once 'lib/food_import/PhotoImporter.php';

/*

Orchestrates food import: takes a URL, pasted HTML or pictures of the packaging,
and returns a normalized food-data array ready for the import form.

Add a new vendor by implementing FoodParser and registering it in `parsers()`.

*/
class FoodImporter
{

  // Registered vendor parsers (first match wins)

  private static function parsers() : array
  {
    return [ new ReweParser(), new EdekaParser() ];
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

    throw new Exception('No importer matched this page. Supported vendors: REWE, EDEKA.');
  }


  /* Import from pictures of the packaging (option C). Returns { food, warnings }
     instead of a bare food record: only at runtime can the model tell what it
     could not read, and the user has to see that while reviewing the form. */

  public static function fromImages( array $images ) : array
  {
    $result = PhotoImporter::fromImages( $images );

    return ['food' => self::finalize( $result['food'], 'pack'), 'warnings' => $result['warnings']];
  }


  /* Add source metadata and the current date, drop empty values.

     $source says where everything on the record came from: a vendor page ("web")
     or the packaging itself ("pack"). The nutritional values get the warning
     because a web page is not necessarily in sync with the pack in hand.
     The price is only sourced when one was actually read - a photo import finds
     one only if a price label happens to be legible */

  private static function finalize( array $food, string $source = 'web') : array
  {
    $today = date('Y-m-d');

    $food['sources'] = ['nutriVal' => $source === 'web'
                                    ? 'web (information on packaging may differ slightly)'
                                    : $source];
    $food['lastUpd'] = $today;

    if( ! empty($food['price']))
    {
      $food['sources']['price'] = $source;
      $food['lastPriceUpd']     = $today;
    }

    // Preset "acceptable" for high-sugar foods (EU high-sugar threshold 22.5 g/100g)

    $sugar = $food['nutritionalValues']['sugar'] ?? null;

    if( $sugar !== null && $sugar > 22.5 )
      $food['acceptable'] = 'less';

    return $food;
  }
}

?>
