```
Add one food default to this project.

Type:  [ FOOD ]
Foods: [ FOOD_FILE_1 ], [ FOOD_FILE_2 ]

Read these first:

- src/data/food_defaults/_GLM_defaults/recipe.md  the instructions, follow them step by step
- src/data/food_defaults/_blank_ai.yml            the template to copy
- src/data/food_defaults/Lentils.yml              a finished example
- the food files under src/data/bundles/Default_*/foods, one per name in "Foods"

Deliver:

1. the new file src/data/food_defaults/[ FOOD ].yml
2. the line "type:              [ FOOD ]" added to each food listed above

Get every number from USDA FoodData Central, per 100 g. Take no value from memory,
and don't estimate - a value the source doesn't have is 0 with the comment
"# missing in source". Match the state of the source entry (raw, cooked, canned,
dry) to the kcal of the foods.

Most food files don't state their state, they only have vendor, weight and the
pack values. Then don't claim one: compare all entries of the food - the states
sit next to each other in the id range, so look at the neighbours of your id too -
take the fuller one, and write in the header comment and in your answer that the
food doesn't say it and which entries you weighed.

Read the entry from one of these two urls only:

  https://api.nal.usda.gov/fdc/v1/food/<id>?api_key=DEMO_KEY   (rate limited)
  https://fdc.nal.usda.gov/portal-data/external/<id>           (no key, no limit)

On http 429 switch to the portal url. It names the amount "value", the api names
it "amount". Take no number from any other website, also not from sites that
republish USDA data - if you can't reach these two urls, stop and say so.
https://fdc.nal.usda.gov/food-details/<id>/nutrients is only the form for citing
the source inside the file, fetching it gives 404.

Watch the units, they are per field, instead of per group: in minerals only Salt,
Potassium and Calcium are grams, the remaining eleven are milligrams. Amino acids
are milligrams, the source has grams. The key is "Fibre" instead of "Fiber".

When done, run

  php src/tools/data_verification/verify_food_defaults.php --file=[ FOOD ]

and paste the output. Then tell me which source entry you used and which values
the source didn't have.
```
