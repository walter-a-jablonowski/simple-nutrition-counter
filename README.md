# Simple nutrition counter

(in development)

Counts nutrients as simple as possible (one tab per ingredient). Helps improving your daily nutrient intake to improve general health and brain function. It also calculates partially used ingredients.

Most people are deficient in one or more than one substance e.g. zinc, vitamin D, iron, ... What if you could have perfect supply? The problem with supplements: B12 cyanocobalamin isn't the real B12 but a cheap sythetic form, dosage while production often goes wrong and you are missing unexplored plant substances.

```
composer install walter-a-jablonowski/simple-nutrition-counter
```

**Long term goal:** Handle all minimal daily logging in a single app as simple as possible (with as few clicks as possible on a daily basis). Isn't neccessarily nutrition only but only all that can't be handled easier (most likely nutrition and daily expenses = actual consumption).

- [Usage](misc/usage.md)
- [Possible future extensions](#possible-future-extensions)
- [Developer information](misc/dev_info.md)
  - [Model](misc/dev_info.md#model)
- [License](#license)

This kind of project might be AI proof because AI solution would be: it watches you while cooking and counts the calories. Do you want that?

<table>
  <tr>
    <td>Current</td>
    <td>Plan</td>
  </tr>
  <tr>
    <td>
      <img src="misc/img.png" width="280">
    </td>
    <td>
      <img src="misc/design_1.png" width="280">
    </td>
  </tr>
</table>


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


LICENSE
----------------------------------------------------------

Copyright (C) Walter A. Jablonowski 2024, free for non-commercial use currenlty under MIT [License](LICENSE), \
commercial licensees must support the development

This library is build upon PHP and free software (license see [credits](credits.md)).

[Privacy](https://walter-a-jablonowski.github.io/privacy.html) | [Legal](https://walter-a-jablonowski.github.io/imprint.html)
