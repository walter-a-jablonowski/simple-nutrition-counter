# Bundles

- [Naming scheme](#naming-scheme)
- [Bundle structure](#bundle-structure)
- [-this](#-this)
- [Foods](#foods)
- [Recipes file](#recipes-file)
- [Nutrients](#nutrients)
- [Layout](#layout)


Naming scheme
----------------------------------------------------------

- **Speaking id:**
  ```
  Default_JaneDoe@example.com-24080101000000
  ^          ^
  BUNDLENAME USERID
  ```
  - name unique per user
  - if a name is used a second time add an invisible idx `Default(2)_ ...`


Bundle structure
----------------------------------------------------------

see also user > misc

- **-this.yml**, see [-this](#-this)
- **/foods** see [foods](#foods)
- **recipes.yml** see [recipes file](#recipes-file)
- **/nutrients**
- **supplments.yml**
- **sports.yml**
- **/layout.yml** see [layout](#layout)
- **tips.html** used if exists, or default in /misc


-this
----------------------------------------------------------

- sources see [shared](../README.md)


Foods
----------------------------------------------------------

- sorting in UI like layout.yml, all left as entered
- all fields see -> readme

- field info available in app as well (in development), see def file [inline_help/foods.yml](../src/misc/inline_help/foods.yml)
- files that start with `_` will be excluded from food list


Recipes file
----------------------------------------------------------

```yaml

My recipe:
                      # amount:
  Chick R Bio:  100g  # < 0 (1/2)    is percent of pack
  Brokkoli R:         # > 0 (e.g. 2) is piece(s)
  Olivenöl:           # or with "g" (100g), "ml" (100ml)
```


Nutrients
----------------------------------------------------------

see [nutrients](Default_JaneDoe@example.com-24080101000000/nutrients/README.md)


Layout
----------------------------------------------------------

- foods, recipes, misc
- left over entries will be attached below (group Misc)
- you can add each entry multiple times

```yaml

tabName:

-- Data --------     -- Layout --------
                      ___________________    
  My nutrient group:   | My nutrient group     # nutrient names are from recipes.yml, foods
    list:              |-------------------    # or misc.yml (use multiple times possible)
      - Recipe         | Recipe   |
      - or nutrient    | Nutrient | 1/4 | ...  < amounts as defined in
      - ...            |          |              foods

  My nutrient group (color:#e0e0e0, fold:true):  # Visually group with color
    (i):                                         # Comment (i) switch in UI
    short:                                       # always visible
    list:
      - ...
```
