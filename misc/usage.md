# Usage

- [Install](#install)
- [Common usage](#common-usage)
- [Users](#users)
  - [Settings file](#settings-file)
  - [days](#days)
  - [User nutrient files](#user-nutrient-files)
- [Bundles](#bundles)
  - [Foods file](#foods-file)
  - [Supplements file](#supplements-file)
  - [Recipes file](#recipes-file)
  - [Sports](#sports)
  - [Layout file](#layout-file)
  - [misc.yml](#miscyml)
- [Nutrient files](#nutrient-files)


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


Users
----------------------------------------------------------

/users/USER/-this.yml

see also [readme](../src/data/users/README.md)


Settings file
----------------------------------------------------------

(advanced feature)


days
----------------------------------------------------------

(fill me)


User nutrient files
----------------------------------------------------------

(advanced feature)

Same as [Nutrient files](#nutrient-files), overrides settings.


Bundles
----------------------------------------------------------

/bundes/BUNDLE/-this.yml

see also [readme](../src/data/bundles/README.md)


Foods file
----------------------------------------------------------

Currently we enter the values once in data/bundles/BUNDLE/foods.yml. Simpler
than doing manual calculations (partially used ingredients).

Field info available in app as well (in development), see def file [inline_help/foods.yml](../src/misc/inline_help/foods.yml)


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


Sports
----------------------------------------------------------

(advanced feature)


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
  short:                            # always visible
  list:
    - ...
```


misc.yml
----------------------------------------------------------

all misc counters

- water
- beverages
- misc expenses that are no food


Nutrient files
----------------------------------------------------------

(advanced feature)

- see also [readme](../src/data/nutrients/README.md)
- derived see below

```yaml

# Nutrient group data

short:                           # short name used as short unique id in daily files (file name is alternative id used in code)
name:                            # display name
unit:      mg
per:       day
comment:   "Increased need if doing sports"
sources:   ""                    # source of information
amounts:

  # same as below


substances:                      # (required)

  B 12:                          # display name

    short:      B12              # short name is used as unique id over all files
    unit:       mg
    sources:    ""               # source of information

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


### Vitamins (derived)

all of the above attributes and

```yaml
type:       methylcobalamin  # sub type
```


### Fat (derived)

TASK:

```yaml
group:      saturated|monoUnsaturated|polyUnsaturated      TASK: Medium-chain fatty acids, Short-chain fatty acids
subGroup:   omega-3|omega-6|omega-9 TASK: omega-5|omega-7  # all unsaturated mono: 7 9, poly: 3 5 6
essential:  true             #                             TASK: saturated devide in long chain, short chain, ... 
```
