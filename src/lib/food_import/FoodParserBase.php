<?php

require_once 'lib/food_import/FoodParser.php';

/*

Common ground for food imports: text clean-up and the small parsing routines
that are the same no matter where the text comes from ("Kann Spuren ..."
allergen notes, "N Stück" pack counts).

They are static because they are pure text functions — the photo import uses the
same three on the text a vision model reads off the packaging.

Anything vendor-specific — how a page is recognized, where its data sits and in
which number format it is written — belongs in the concrete parser.

*/
abstract class FoodParserBase implements FoodParser
{

  // Collapse whitespace/newlines into a single line and strip surrounding punctuation

  public static function cleanText( string $text ) : string
  {
    return trim( preg_replace('/\s+/u', ' ', $text), " .\t\n\r");
  }


  // Split an ingredient statement into [ingredients, mayContain]. Vendors append
  // the trace note to the ingredient text in slightly different wordings
  // ("Kann Spuren ...", "Das Produkt kann Spuren von ... enthalten")

  public static function splitIngredients( string $text ) : array
  {
    $mayContain = '';

    if( preg_match('/((?:Das Produkt kann|Kann) Spuren.*)/sui', $text, $m))
    {
      $mayContain = $m[1];
      $text       = str_replace($m[1], '', $text);
    }

    return [ self::cleanText($text), self::cleanText($mayContain)];
  }


  // Pack piece count from the full product title, e.g. "... 300g, 5 Stück".
  // Vendors have no reliable structured field for this, so the title is the source

  public static function parsePieces( string $productName ) : ?int
  {
    if( preg_match('/(\d+)\s*Stück/ui', $productName, $m))
      return (int) $m[1];

    return null;
  }
}

?>
