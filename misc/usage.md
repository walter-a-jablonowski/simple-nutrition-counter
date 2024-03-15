# Usage

- [Common](#common)
- [Foods file usage](#foods-file-usage)
- [Nutrients file usage](#nutrients-file-usage)


Common
----------------------------------------------------------

- Manual entering values: current solution is page reload on save
- We include at least some supplements like magnesium to make sure we get the required amount. For some it may be enough to just take it regularly with no tracking.


Foods file usage
----------------------------------------------------------

Currently we enter the values once in data/foods.yml. Easy when the number of ingredients usually used in a day is limited, and simpler than doing manual calculations (partially used ingredients).

```yaml
My food S Bio:                     # S = short for store if needed (save some space)

  comment:      "..."              # misc comment
  ingredients:  "..."              # ingredients that you want to be aware of
  origin:       "..."              # country of origin, if you want to be aware of

  weight:       100                # grams
  packaging:    pack|piece|pieces  # pack e.g. brokkoli
  quantity:     3                  # number of pieces in a pack
  usedAmounts:  [1, 2, 3, 4, 5]    # for packaging = pack (default 1/4 - 1) and pieces (default 1 - 3)

  calories:                        # grams
  fat:          100
  saturatedFat: 
  monoUnsaturated:                 # a few foods have that data
  polyUnsaturated:                   
  carbs:        
  sugar:        
  fibre:        
  amino:        
  salt:         1.0

  calcium:                         # same unit as in nutrients.yml
  # ... some important ...

  sources:      "Web|Pack, ..."    # comment on from where the infomation is
  lastUpd:      2024-02-18
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
