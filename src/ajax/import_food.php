<?php

require_once 'lib/food_import/FoodImporter.php';

/*

Ajax: import food data from a product page.

Input:  { url } to fetch server-side (option A), or { html } pasted by the user
        (option B). If both are given, html wins and url is kept as the source.
Output: { food } normalized food data for the import form (nothing is saved yet).

*/
trait ImportFoodAjaxController
{

  public function importFood( $request )
  {
    $url  = trim( $request['url']  ?? '');
    $html = $request['html'] ?? '';

    try {

      if( trim($html) !== '')
        $food = FoodImporter::fromHtml( $html, $url ?: null );
      elseif( $url !== '')
        $food = FoodImporter::fromUrl( $url );
      else
        return ['result' => 'error', 'data' => ['message' => 'Enter a URL or paste the page HTML.']];
    }
    catch( Exception $e ) {
      return ['result' => 'error', 'data' => ['message' => $e->getMessage()]];
    }

    return ['result' => 'success', 'data' => ['food' => $food]];
  }
}

?>
