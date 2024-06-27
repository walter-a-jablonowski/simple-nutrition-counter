# Usage

- [Install](install)
- [Common usage](#common-usage)
- [Settings file](#settings-file-settingsyml)
- [Foods file](#foods-file-foodsyml)
- [Supplements file](#supplements-file-foodsyml)
- [Recipes file](#recipes-file-recipesyml)
- [Layout file](#layout-file-layoutyml)
- [Misc (misc.yml)](#misc-counters-miscyml)
- [Nutrient files](#nutrient-files-substancesyml-and-groupsyml)


Install
----------------------------------------------------------

```
composer install walter-a-jablonowski/simple-nutrition-counter
```


Common usage
----------------------------------------------------------

- Manual entering values: current solution is page reload on save
- We include at least some supplements like magnesium to make sure we get the required amount. For some it may be enough to just take it regularly with no tracking.

Data files below see /data


Settings file
----------------------------------------------------------


Foods file
----------------------------------------------------------

Currently we enter the values once in data/foods.yml. Simpler than doing manual calculations (partially used ingredients).

```yaml

My food S Bio:                           # (required) display name (also used as id)
                                         #   S = short for store if needed (save some space)
  productName:        "..."              # precise product name
  vendor:             My vendor          
  url:                "..."         

  acceptable:         less|occasionally  # highlight less good foods in UI
  comment:            "My comment"       # all misc comments (we can use html here)
  properties:         { bio: true, vegan: true, NutriScore: A, oekotest: "sehr gut" }  # misc attributes
  ingredients:        "..."              # ingredients that you want to be aware of
                                         #   (food info will derive badges for gluten ...)
  origin:             "..."              # country of origin, if you want to be aware of

  cookingInstrutions: |                  # (we can use html here)

    First ...

  price:              1.00               # (required) may be null
  weight:             100g               # (required) of whole pack in case of pieces, unit g or ml is optional
  pieces:             6                  # number of pieces in a pack (if any)
  usedAmounts:        ["1/4", ...]       # enter fraction, pieces or precise (you can't mix these, chosse one)
                                         # - default  "1/4", "1/3", 1 * weight            if pieces unset
                                         # -      or  1, 1pc, 2, 3    * (weight / pieces) if pieces set
                                         # - or use   10g or 50ml
                                         # consumed as a single piece: use 1

  calories:                              # (required) per 100g or 100ml in grams or ml (depends on weight)
  nutritionalValues:                     # (required) usually from food packaging

    fat:              100                # (required)
    saturatedFat:
    monoUnsaturated:                     # a few foods have that data
    polyUnsaturated:                   
    carbs:                               # (required)
    sugar:                               # (required)
    sugarAlcohol:                   
    fibre:        
    amino:                               # (required)
    salt:             1.0                # (required) TASK: technically is a single substance

  fattyAcids:

    # ...

  aminoAcids:
  
    # ...

  vitamins:

    # ...
  
  minerals:

    calcium:                             # same unit as in /nutrients

  secondary:                             # secondary plant substances

    # ...
                                         # source is (required)
  sources:            { nutriVal: "web|pack (information on packaging may differ slightly)", nutrients: "...", price: "..." }
  lastUpd:            2024-02-18         # (required)
  lastPriceUpd:       2024-03-23
```


Supplements file
----------------------------------------------------------

nearly same as foods


Recipes file
----------------------------------------------------------

```yaml

My recipe:
                     # amount:
  Chick R Bio:  100g # < 0 (1/2)    is percent of pack
  Brokkoli R:        # > 0 (e.g. 2) is piece(s)
  Olivenöl:          # or with "g" (100g), "ml" (100ml)
```

Layout file
----------------------------------------------------------

- for upper part of nutrients list
- left over recipes nutrients will be attached below
- you can add each food multiple times

```yaml

-- Data --------     -- Layout --------
                      ___________________    
My nutrient group:   | My nutrient group     # nutrient names are from recipes.yml, foods.yml
  list:              |-------------------    # or misc.yml (use multiple times possible)
    - Recipe         | Recipe   |
    - or nutrient    | Nutrient | 1/4 | ...  < amounts as defined in
    - ...            |          |              foods.yml

My nutrient group (color:#e0e0e0):  # Visually group with color
  (i):                              # Comment (i) switch in UI
  list:
    - ...
```


Misc (misc.yml)
----------------------------------------------------------

all misc counters

- water
- beverages
- misc expenses that are no food


Nutrient files
----------------------------------------------------------

(advanced feature)

```yaml

# Nutrient group data

short:                         # short name used as short unique id in daily files (file name is alternative id used in code)
name:                          # display name
unit:      mg
per:       day
comment:   "Increased need if doing sports"
amounts:

  # same as below


substances:                      # (required)

  B 12:                          # display name

    type:       methylcobalamin  # sub type
    short:      B12              # short name is used as unique id over all files
    unit:       mg

    per:        day
    times:      morning, ...
    how:                         # bio availability
    limit:                       # take how long
    break:                       # min break time

    comment:    # Advantage
    careful:    false
    interactions:      
    sideEffects:      

    keeping:    "keep cool"

    amounts:

      # value mean all >= this, height might be used to fix weight
      - criteria: { gender: male , age: 40 , height: "*" , weight: 70, workout: false }
        amount:
        lower:  4%  # summary view still is green progress if within these bounds
        upper:  8   # percent for some defaut value, number for precise (max is added to amount)
```
