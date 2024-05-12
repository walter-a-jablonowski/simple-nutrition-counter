# Usage

- [Install](install)
- [Common usage](#common-usage)
- [Settings file (settings.yml)](#settings-file-settingsyml)
- [Foods file (foods.yml)](#foods-file-foodsyml)
- [Recipes file (recipes.yml)](#recipes-file-recipesyml)
- [Layout file (layout.yml)](#layout-file-layoutyml)
- [Misc counters (misc.yml)](#misc-counters-miscyml)
- [Nutrient files (nutrients.yml)](#nutrient-files-substancesyml-and-groupsyml)


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


Settings file (settings.yml)
----------------------------------------------------------


Foods file (foods.yml)
----------------------------------------------------------

Currently we enter the values once in data/foods.yml. Simpler than doing manual calculations (partially used ingredients).

```yaml

My food S Bio:                      # S = short for store if needed (save some space)

  productName:        "..."         # precise product name

  acceptable:         less|occasionally  # highlight less good foods, (i) switch in UI
  comment:            "..."         # all misc comments
  ingredients:        "..."         # ingredients that you want to be aware of
  origin:             "..."         # country of origin, if you want to be aware of

  price:              1.00

  cookingInstrutions: ""

  weight:             100g          # (required) unit g or ml is optional, of whole pack in case of pieces
  pieces:             6             # number of pieces in a pack (if any)
  usedAmounts:        ["1/4", ...]  # enter fraction, pieces or precise (you can't mix these, chosse one)
                                    # - default  "1/4", "1/3", 1 * weight            if pieces unset
                                    # -      or  1, 1pc, 2, 3    * (weight / pieces) if pieces set
                                    # - or use   10g or 50ml
                                    # consumed as a single piece: use 1

  calories:                         # (required) per 100g or 100ml in grams (depends on what weight is)
  nutritionalValues:                # basic data usually used on food packaging

    fat:              100           # (required)
    saturatedFat: 
    monoUnsaturated:                # a few foods have that data
    polyUnsaturated:                   
    carbs:                          # (required)
    sugar:                          # (required)
    sugarAlcohol:                   
    fibre:        
    amino:                          # (required)
    salt:             1.0           # (required) TASK: technically is a single substance

  fattyAcids:
  
  aminoAcids:
  
  vitamins:
  
  minerals:

    calcium:                        # same unit as in nutrients.yml

  secondary:  # secondary plant substances

  sources:            "Macro nutrients: web|pack, information on packaging may differ slightly, nutrients: ..., price: ..."
  lastUpd:            2024-02-18  # all (required)
  lastPriceUpd:       2024-03-23
```


Recipes file (recipes.yml)
----------------------------------------------------------

```yaml

My recipe:
                     # amount:
  Chick R Bio:  100g # < 0 (1/2)    is percent of pack
  Brokkoli R:        # > 0 (e.g. 2) is piece(s)
  Olivenöl:          # or with "g" (100g), "ml" (100ml)
```

Layout file (layout.yml)
----------------------------------------------------------

- for upper part of nutrients list
- left over recipes nutrients will be attached below

```yaml

-- Data --------     -- Layout --------
                      ___________________    
My nutrient group:   | My nutrient group     # nutrient names are from recipes.yml, foods.yml
                     |-------------------    # or misc.yml (use multiple times possible)
  - Recipe           | Recipe   |
  - or nutrient      | Nutrient | 1/4 | ...  < amounts as defined in
  - ...              |          |              foods.yml

My nutrient group:   
  comment:           # Variant with comment (i) switch in UI
  color:             # Visually group with color
  list:
    - ...
```


Misc counters (misc.yml)
----------------------------------------------------------

all misc counters

- water
- beverages
- misc expenses that are no food


Nutrient files (substances.yml and groups.yml)
----------------------------------------------------------

(advanced feature)

```yaml

B 12:

  type:              methylcobalamin
  short:             B12
  unit:              mg

  comment:           # Advantage
  careful:           false
  interactions:      
  sideEffects:      

  per:               day
  times:             morning, ...
  how:               # bio availability
  limit:             # take how long
  break:             # min break time
  keeping:           "keep cool"
  amounts:

    - criteria: { gender: male , age: 40 , height: "*" , weight: 70 }  # value mean all >= this
      amount:                                                          # height might be used to fix weight
      lower: 4%  # summary view still is green progress if within these bounds
      upper: 8   # percent for some defaut value, number for precise (max is added to amount)
```
