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
unit:      g                     # (non-required) default is g for groups and mg for nutrients
per:       day
comment:   "Increased need if doing sports"

sources:                         # source of information, string or array(object) (see also below)

  - title: 
    source:                      # e.g. url
    sub:                         # sub section
    comment:                     # details

amounts:

  # same as below

substances:                       # (required) key at least empty []

  B 12:                           # display name

    short:        B12             # short name is used as unique id over all files
    unit:         mg
    groups:       ["PolyUnsaturated/..."]  # alternative groups
    essential:    true

    comment:                      # Advantage
    interactions:      
    sideEffects:      
    careful:      false

    usage:

      per:        day
      times:      morning, ...
      how:                        # bio availability
      limit:                      # take how long
      break:                      # min break time

      keeping:    "keep cool"

    sources:                      # string or array(object), same as above

      - title:
        source:
        sub:   
        comment:

    amounts:

      # TASK: BMI might be relevant for weight
      
      - criteria: { dayType: standard, gender: male, age: 40, weight: 70, height: "*" }  # height might be used to fix weight
        lower:  4%   # bounds for acceptable intake (percent or precise)
        amount:
        upper:  8   
        sources:     # string or array(object), same as above (if needed for being more precise)

          - title:
            source: 
            sub:    
            comment:    
```


### Vitamins (derived)

all of the above attributes and

```yaml

subType:    methylcobalamin
```
