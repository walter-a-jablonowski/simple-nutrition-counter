# Simple nutrition counter

(in development)

Counts nutrients as simple as possible (one tab per ingredient). Helps improving your daily nutrient intake to improve general health and brain function. It also calculates partially used ingredients.

```
composer install walter-a-jablonowski/simple-nutrition-counter
```

**Long term goal:** Handle all minimal daily logging in a single app as simple as possible (with as few clicks as possible on a daily basis). Isn't neccessarily nutrition only but only all that can't be handled easier (most likely nutrition and daily expenses = actual consumption).

- [Possible future extensions](#possible-future-extensions)
- [Usage](#usage)
- [License](#license)

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

- Manual entering values: current solution is page reload on save
- We include at least some supplements like magnesium to make sure we get the required amount. For some it may be enough to just take it regularly with no tracking.

### Nutrients file usage

(advanced feature)

```
B 12 (methyl):

  type:                 methylcobalamin
  amountMale:           
  amountFemale: 
  increasedNeedMale:
  increasedNeedFemale:
  unit:                 mg
  comment:              ""
  lower:                5          <-- percent, summary view still is green progress if within these bounds
  upper:                5
```

### Foods file usage

Currently we enter the values once in data/foods.yml. Easy when the number of ingredients usually used in a day is limited, and simpler than doing manual calculations (partially used ingredients).

```
My food S Bio:                     <-- S = short for store if needed (save some space)

  comment:      "..."              <-- misc comment

  unit:         pack|piece|pieces  <-- pack e.g. brokkoli
  weight:       100                <-- grams
  pieces:                          <-- number of pieces in a pack
  usedAmounts:  [1, 2, 3, 4, 5]    <-- for pack (default 1/4 - 1) and pieces (default 1 - 3)

  calories:     
  fat:          100
  saturatedFat: 
  carbs:        
  sugar:        
  fibre:        
  amino:        
  salt:         1.0

  calcium:                         <-- same unit as in nutrients.yml
  ... some imp ...

  sources:      Web|Pack           <-- comment on from where the infomation is
  lastUpd:      2024-02-18
```


LICENSE
----------------------------------------------------------

Copyright (C) Walter A. Jablonowski 2024, free for non-commercial use currenlty under MIT [License](LICENSE), \
commercial licensees must support the development

This library is build upon PHP and free software (license see [credits](credits.md)).

[Privacy](https://walter-a-jablonowski.github.io/privacy.html) | [Legal](https://walter-a-jablonowski.github.io/imprint.html)
