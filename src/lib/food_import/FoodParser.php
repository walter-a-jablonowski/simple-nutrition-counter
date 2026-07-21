<?php

/*

Vendor parser interface for food import.

Each vendor (Rewe, and others added later) implements this. FoodImporter asks
every registered parser whether it `matches` a given page, then calls `parse` on
the first match to get a normalized food-data array (see ReweParser for the shape).

*/
interface FoodParser
{

  // True if this parser can handle the given page (checked against url and/or html)

  public function matches( string $html, ?string $url ) : bool;

  // Parse page into a normalized food-data array

  public function parse( string $html, ?string $url ) : array;
}

?>
