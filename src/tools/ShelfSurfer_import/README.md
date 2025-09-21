# Price Importer

This tool imports price data from `places.yml` into food files in the bundle directory.

## Prerequisites

Make sure you have Composer installed and the dependencies are installed:

```
composer install
```

## How It Works

The price importer:

1. Scans for food files in the bundle directory
2. Finds matching products based on keywords
3. Updates prices and maintains price history
4. Updates the lastPriceUpd date

## Running the Importer

```
php src/import/runImport.php
```

## File Structure

- `priceImporter.php`: Main implementation of the price importer
- `runImport.php`: Simple script to run the importer
- `places.yml`: Source file containing price data to import

## How Matching Works

The importer matches food files to price data by comparing keywords in files.
If a keyword from the food file matches a keyword in the places.yml file, the price data is imported.

## Price History

When updating a price, the old price is stored in the `prices` or `dealPrices` field with the date from `lastPriceUpd`.
This maintains a history of price changes over time. 
