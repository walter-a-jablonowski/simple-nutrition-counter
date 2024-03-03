# Simple nutrition counter

(in development)

Enables improving your daily nutrient intake to improve general health and brain function systematically. Counts nutrients as simple as possible (one tab per ingredient). It also calculates partially used ingredients.

```
composer install walter-a-jablonowski/simple-nutrition-counter
```

**Possible future extensions**

- track more substances like magnesium
- input dialog for rarely used ingredients
- also track cost of used ingredients
- track more daily cost data within the same app
- improve js code

and maybe (advanced)

- add buttons with amounts in the food list if helpful (less entries)
- multi user
- use some nutrient database with quality data (if available) ins of manually entering the value
  - maybe use barcode scanner


![Alt text](misc/img.png)


## Foods file usage

Currently we enter the values once in data/foods.yml, which is much simpler than doing manual calculations for partially used ingredients on a daily basis. The number of ingredients usually used in a day is limited...

```
My food S Bio:

  comment:      ""
  unit:         pack
  amount:       _
  usedAmounts:  [1, 2, 3, 4, 5]
  weight:       _
  calories:     _
  fat:          _
  saturatedFat: _
  carbs:        _
  sugar:        _
  fibre:        _
  amino:        _
  salt:         _
  calcium:      # mg
  ... some imp ...

  sources:      Web|Pack
  STAT:         some
  lastUpd:      2024-02-18
```

- key:           S = short for store if needed (save some space)
- unit:          pack e.g. brokkoli, or piece e.g. riegel, pieces (pack with units)
- amount:        if pieces
- used amounts:  for pack (default 1/4 - 1) and pieces (default 1 - 3)
- sources:       Web|Pack, calcium also from vendors page online
- STAT:          some|all from packaging


## LICENSE

Copyright (C) Walter A. Jablonowski 2024, MIT [License](LICENSE)

This library is build upon PHP and free software (license see [credits](credits.md)).

[Privacy](https://walter-a-jablonowski.github.io/privacy.html) | [Legal](https://walter-a-jablonowski.github.io/imprint.html)
