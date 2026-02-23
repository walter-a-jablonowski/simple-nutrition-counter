
( alternative for quickly upd a price on the go without bulk import )

maybe this is a bit too much, just use the function in the app or import?

  --

In the folder src\tools\price_upd make a new tool that I can use to update food prices.

Food files: src\data\bundles\Default_JaneDoe@example.com-24080101000000\foods

Food files structure:

Most foods in the destination folder are in yml files. The file name is the food name. Some are in sub folders. In that case the folder name is the food name and the data is in a file called -this.yml.

A food data file may contain a single food or variants of a food (key variants).

UI:

- App Header with title
  - right aligned: save btn
- Toolbar
  - search input with Filter btn
- Scrollable list with foods, entries:
  - Food name
  - right aligened (contenteditable): Price (black) and Deal price (green)
    - show n/a if a value is missing

When the user enters oder edits a price or deal price and presses save update the food file.

Don't use yaml dump. You can use the function in src\tools\shared\variant_helper.php to correctly update prices.

Make it look good and responsive.
