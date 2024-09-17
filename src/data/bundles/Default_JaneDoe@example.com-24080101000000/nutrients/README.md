# Nutrients

Default nutrients

- [Naming scheme](#naming-scheme)
- [File structure](#file-scheme)
- [Nutrient files](#nutrient-files)


Naming scheme
----------------------------------------------------------

- **Speaking id**

  - fil name = group id
  - short second id: `short` in file


File structure
----------------------------------------------------------


Nutrient files
----------------------------------------------------------

(advanced feature)

- derived see below

```yaml

# Nutrient group data

name:                            # display name
short:                           # short name used as short unique id in daily files (file name is alternative id used in code)
unit:      mg
per:       day
comment:   "Increased need if doing sports"
sources:   ""                    # source of information
amounts:

  # same as below

substances:                      # (required) key at least empty []

  B 12:                          # display name

    short:      B12              # short name is used as unique id over all files
    unit:       mg
    sources:                     # source of information (string or array)

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
      - criteria: { dayType: standard, gender: male , age: 40 , height: "*" , weight: 70 }
        lower:  4%  # summary view still is green progress if within these bounds
        amount:     # percent for some default value, number for precise (max is added to amount)
        upper:  8   
```


### Vitamins (derived)

all of the above attributes and

```yaml

subType:    methylcobalamin  # sub type
```


### Fat (derived)

TASK:

```yaml

group:      Saturated|MonoUnsaturated|PolyUnsaturated      TASK: Medium-chain fatty acids, Short-chain fatty acids
subGroup:   omega-3|omega-6|omega-9 TASK: omega-5|omega-7  # all unsaturated mono: 7 9, poly: 3 5 6
essential:  true             #                             TASK: saturated devide in long chain, short chain, ... 
```
