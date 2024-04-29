# Usage

- [Install](install)
- [Common usage](#common-usage)
- [Recipes file usage (recipes.yml)](#recipes-file-usage-recipesyml)
- [Foods file usage (foods.yml)](#foods-file-usage-foodsyml)
- [Nutrients file usage (nutrients.yml)](#nutrients-file-usage-nutrientsyml)
- [Layout file usage (layout.yml)](#layout-file-usage-layoutyml)


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


Recipes file usage (recipes.yml)
----------------------------------------------------------

```yaml

My recipe:
                     # amount:
  Chick R Bio:  100g # < 0 (1/2)    is percent of pack
  Brokkoli R:        # > 0 (e.g. 2) is piece(s)
  Olivenöl:          # or with "g" (100g), "ml" (100ml)
```

Foods file usage (foods.yml)
----------------------------------------------------------

Currently we enter the values once in data/foods.yml. Simpler than doing manual calculations (partially used ingredients).

```yaml

My food S Bio:                      # S = short for store if needed (save some space)

  product:            "..."         # precise product name

  nutriVal:           "less|occasionally"  # highlight less good or acceptable occasionally ingredients
  comment:            "..."         # all misc comments
  ingredients:        "..."         # ingredients that you want to be aware of
  origin:             "..."         # country of origin, if you want to be aware of

  price:              1.00

  weight:             100g          # unit g or ml is optional, of whole pack in case of pieces
  pieces:             6             # number of pieces in a pack (if any)
  usedAmounts:        ["1/4", ...]  # enter fraction, pieces or precise (you can't mix these, chosse one)
                                    # - default  "1/4", "1/3", 1 * weight            if pieces unset
                                    # -      or  1, 1pc, 2, 3    * (weight / pieces) if pieces set
                                    # - or use   10g or 50ml
                                    # consumed as a single piece: use 1

  calories:                         # per 100g or 100ml in grams (depends on what weight is)
  nutrients:

    fat:              100
    saturatedFat: 
    monoUnsaturated:                # a few foods have that data
    polyUnsaturated:                   
    carbs:        
    sugar:        
    fibre:        
    amino:        
    salt:             1.0

    calcium:                        # same unit as in nutrients.yml
    # ... some important ...

  sources:            "Macro nutrients: web|pack, information on packaging may differ slightly, nutrients: ..., price: ..."
  lastUpd:            2024-02-18
  lastPriceUpd:       2024-03-23
```


Nutrients file usage (nutrients.yml)
----------------------------------------------------------

(advanced feature)

```yaml

B 12:

  comment:              ""

  type:                 methylcobalamin
  amountMale:           
  amountFemale: 
  increasedNeedMale:
  increasedNeedFemale:
  unit:                 mg
  lower:                5   # percent, summary view still is green progress if within these bounds
  upper:                5
```

Layout file usage (layout.yml)
----------------------------------------------------------

- for upper part of nutrients list
- left over recipes nutrients will be attached below

```yaml
                      ___________________    
My nutrient group:   | My nutrient group     # names are from recipes.yml
                     |-------------------    # or foods.yml
  - Recipe           | Recipe   |
  - or nutrient      | Nutrient | 1/4 | ...  < amounts as defined in
  - ...              |          |              foods.yml

My nutrient group:   # Variant with comment
  comment:
  list:
    - ...
```
