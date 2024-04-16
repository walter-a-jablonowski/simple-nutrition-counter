# Usage

- [Common](#common)
- [Recipes file usage](#recipes-file-usage)
- [Foods file usage](#foods-file-usage)
- [Nutrients file usage](#nutrients-file-usage)
- [Layout file usage](#layout-file-usage)


Common
----------------------------------------------------------

- Manual entering values: current solution is page reload on save
- We include at least some supplements like magnesium to make sure we get the required amount. For some it may be enough to just take it regularly with no tracking.


Recipes file usage
----------------------------------------------------------

```yaml

My recipe:
                     # amount:
  Chick R Bio:  100g # < 0 (1/2)    is percent of pack
  Brokkoli R:        # > 0 (e.g. 2) is piece(s)
  Olivenöl:          # or with "g" (100g), "ml" (100ml)
                     
```

Foods file usage
----------------------------------------------------------

Currently we enter the values once in data/foods.yml. Easy when the number of ingredients usually used in a day is limited, and simpler than doing manual calculations (partially used ingredients).

```yaml

My food S Bio:                           # S = short for store if needed (save some space)

  comment:            "..."              # misc comment
  ingredients:        "..."              # ingredients that you want to be aware of
  origin:             "..."              # country of origin, if you want to be aware of

  price:              1.00

  weight:             100                # of whole pack in case of pieces
  packaging:          pack|piece|pieces  # pack e.g. brokkoli
  quantity:           3                  # number of pieces in a pack
  usedAmounts:        ["1/4", "1/3", "1/2", "1"]  # default for packaging = pack, (1-3 for  pieces)

  perUnit:            g (default) | ml   # all values in g per 100g or 100ml
  calories:                              # grams
  nutrients:

    fat:              100
    saturatedFat: 
    monoUnsaturated:                     # a few foods have that data
    polyUnsaturated:                   
    carbs:        
    sugar:        
    fibre:        
    amino:        
    salt:             1.0

    calcium:                             # same unit as in nutrients.yml
    # ... some important ...

  sources:            "Macro nutrients: web|pack, information on packaging may differ slightly, nutrients: ..., price: ..."
  lastUpd:            2024-02-18
  lastPriceUpd:       2024-03-23
```


Nutrients file usage
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

Layout file usage
----------------------------------------------------------

```yaml

```
