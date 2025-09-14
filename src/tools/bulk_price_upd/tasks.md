
- [ ] try import with (only a few tryed see debug_import.yml)

  - missing price
  - price upd
  - add deal price
  - existing his
  - non existing his
  - food with file -this.yml

  - [x] some problems with indent

- [ ] remove import.yml when done

- [-] -this.yml already works?
- [x] 

  ```
  In moved to tool to this folder: src\tools\bulk_price_upd

  Please add a tool import.php that can be used to import prices from import.yml to src\data\bundles\Default_JaneDoe@example.com-24080101000000\foods

  In import.yml the key is the food name. Find the right destination file using the food name.

  Most foods in the destination folder are in yml files. The file name is the food name. Some are  in sub folders. In that case the folder name is the food name and the data is in a file called -this.yml.

  I want to preserve the formatting and comments in the destination yml files. This means we can't use yaml dump. Instead you must update the files manually (edit content line by line).

  Structure of food files: src\data\bundles\Default_JaneDoe@example.com-24080101000000\foods\_blank_food.yml

  1. Before adding new prices the old prices are saved in a history at the end:

  ```
  prices:

    2024-11-28: 2.19  # date is from lastPriceUpd key (add last price in first place)
    2024-10-23: 1.69

  dealPrices:

    ...
  ```

  2. Add the new price(s) (and add price keys if missing):

  ```
  price:             
  dealPrice:         
  ```

  3. Update lastPriceUpd key
  ```

  ```
  I made a sample import file: src\tools\bulk_price_upd\debug_import.yml

  For "Lyoner R Bio" there is no dealPrice field added. According to @_blank_food.yml it must below the price field.

  Also the content of dealPrices is the same as for prices:

  dealPrices:

    2024-11-28: 2.19
    2024-10-23: 1.69

  Please try to keep the indentation of the values
  ```

