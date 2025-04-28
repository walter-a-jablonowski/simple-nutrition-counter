# REWE Price Scraper

This tool allows you to find food price data from rewe.de based on product searches. It's designed to work with the food database in the simple-nutrition-counter application.

## Files

- `lib.php` - Contains the core function `findRewePrice()` that queries REWE.de for product prices
- `try.php` - Script that loops through food files and checks prices for REWE products
- `composer.json` - Dependencies for the tool

## How to Use

### Setup

1. Make sure you have PHP installed with curl and yaml extensions
2. Install dependencies:
   ```
   cd tools/import_rewe
   composer install
   ```

### Running the Price Checker

Run the script from the command line:

```
php tools/import_rewe/try.php
```

The script will:
1. Scan all YAML files in the foods directory
2. Find products with vendor "Rewe"
3. Query REWE.de for current prices
4. Save results to `rewe_prices.txt`

The script runs in an endless loop with random delays between requests to avoid being blocked. Press Ctrl+C to stop it.

### Function Return Values

The `findRewePrice()` function returns an array with the following possible formats:

- `['success', price, dealPrice]` - Successfully found price (dealPrice is true/false)
- `['missing']` - No product found
- `['error', 'reason']` - Error occurred (with reason)

## Comments

- The tool adds random delays between requests to avoid being blocked by REWE.de
- It only processes products where there's exactly one match in the search results
- The script logs all results to `rewe_prices.txt` in the same directory
