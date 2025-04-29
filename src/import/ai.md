
Make me a tool in /import that I can use to import price data from

  src\import\places.yml

in a yml file that is in

  src\data\bundles\Default_JaneDoe@example.com-24080101000000\foods


### Identifying the destionation file

In /foods food records are in:

- yml files where the file name doens't start with underscore
- as well as files named "-this.yml" in sub directories

Look for the right file by comparing the fields in "keywords" of the source and the destionation file. If such a key exists in the desition file and one of the keywords from the source file is present then this is the right file.


### Importing price information

The import file has price information only in in some records, those that have the key "price" and or "dealPrice".

Import the new price or dealPrice into keys of the same name in the destionation file:

```
price:             8.99
dealPrice:         5.99
```

Before you update a price, add the old price or dealPrice (if any) to the key prices or dealPrices, sample:

```
prices:

  2024-07-01: 6.49  # for the key of the old price we use the date that was in lastPriceUpd
  2025-03-11: 6.99
```

When the update is done update lastPriceUpd with that date that you find in the source file (same key).


### Preseving the yml format

In the destionation file we want to preserve the text format, so we use no yaml dump.

- if "price" or "dealPrice" is missing in the destionation file, add it above the "weight" field
- if prices or dealPrices is missing in the destionation file, append it below
