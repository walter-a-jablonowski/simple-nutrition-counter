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
unit:      g                     # this is the data of the group
per:       day
comment:   "Increased need if doing sports"
sources:                         # source of information (see also below)

  - title: 
    source:                      # e.g. url
    sub:                         # sub section
    comment:                     # details

amounts:

  # same as below

substances:                      # (required) key at least empty []

  B 12:                          # display name

    short:      B12              # short name is used as unique id over all files
    unit:       mg
    sources:                     # same as above

      - title:
        source:
        sub:   
        comment:

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

      # TASK: BMI might be relevant for weight
      
      - criteria: { dayType: standard, gender: male, age: 40, weight: 70, height: "*" }  # height might be used to fix weight
        lower:  4%   # bounds for acceptable intake (percent or precise)
        amount:
        upper:  8   
        sources:     # same as above (if needed for being more precise)

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


### Fat (derived)

TASK:

```yaml
            # TASK: Medium-chain fatty acids, Short-chain fatty acids
group:      Saturated|MonoUnsaturated|PolyUnsaturated,...    # multiple comma sep cause we have multiple supergroups (e.g. lipids contain fat and some vitamins)
subGroup:   omega-3|omega-6|omega-9 + TASK: omega-5|omega-7  # all unsaturated mono: 7 9, poly: 3 5 6
essential:  true             #                               # TASK: saturated devide in long chain, short chain, ... 
```
