# Simple nutrition counter

(in development)

Counts nutrients as simple as possible (one tab per ingredient). Helps improving your daily nutrient intake to improve general health and brain function. It also calculates partially used ingredients.

- [Possible future extensions](#possible-future-extensions)
- [Foods file usage](#foods-file-usage)

```
composer install walter-a-jablonowski/simple-nutrition-counter
```

![Alt text](misc/img.png)


Possible future extensions
----------------------------------------------------------

(duplicate)

- track more substances like magnesium
- input dialog for rarely used ingredients
- also track cost of used ingredients
- track all daily data in a single app: daily cost, add special buyings ...
- improve js code

and maybe (advanced)

- add buttons with amounts in the food list if helpful (less entries)
- multi user (new folder data/NAME manually)
- use some nutrient database with quality data (if available) ins of manually entering the value
  - maybe use barcode scanner (fill foods.yml)


Usage
----------------------------------------------------------

- Manual entering values: current solution is enter values => save => reload

### Foods file usage

Currently we enter the values once in data/foods.yml, which is much simpler than doing manual calculations for partially used ingredients on a daily basis. The number of ingredients usually used in a day is limited...

```
My food S Bio:                     <-- S = short for store if needed (save some space)

  comment:      "..."
  unit:         pack|piece|pieces  <-- pack e.g. brokkoli
  amount:                          <-- if pieces
  usedAmounts:  [1, 2, 3, 4, 5]    <-- for pack (default 1/4 - 1) and pieces (default 1 - 3)
  weight:       100 # g
  calories:     
  fat:          
  saturatedFat: 
  carbs:        
  sugar:        
  fibre:        
  amino:        
  salt:         1.0
  calcium:          # mg
  ... some imp ...

  sources:      Web|Pack           <-- calcium also from vendors page online
  STAT:         some|all           <-- from packaging
  lastUpd:      2024-02-18
```


LICENSE
----------------------------------------------------------

Copyright (C) Walter A. Jablonowski 2024, MIT [License](LICENSE)

This library is build upon PHP and free software (license see [credits](credits.md)).

[Privacy](https://walter-a-jablonowski.github.io/privacy.html) | [Legal](https://walter-a-jablonowski.github.io/imprint.html)
