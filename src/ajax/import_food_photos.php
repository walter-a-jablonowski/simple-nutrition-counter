<?php

require_once 'lib/food_import/FoodImporter.php';

/*

Ajax: import food data from pictures of the packaging (import option C).

Input:  { images } base64 jpeg/png/webp, already downscaled by the browser
Output: { food } normalized food data for the import form (nothing is saved yet)
        { warnings } what the user should check before saving

*/
trait ImportFoodPhotosAjaxController
{

  public function importFoodPhotos( $request )
  {
    if( ! config::get('photoImport.enabled'))
      return ['result' => 'error', 'data' => ['message' => 'Photo import is switched off in the config.']];

    $images = $request['images'] ?? [];

    if( ! is_array($images) || ! $images )
      return ['result' => 'error', 'data' => ['message' => 'Take at least one picture.']];

    set_time_limit( 180 );   // reading three pictures can take longer than php's default

    try {
      $import = FoodImporter::fromImages( $images );
    }
    catch( Exception $e ) {
      return ['result' => 'error', 'data' => ['message' => $e->getMessage()]];
    }

    return ['result' => 'success', 'data' => $import];
  }
}

?>
