# Bundles

- [Naming scheme](#naming-scheme)
- [Bundle structure](#bundle-structure)
- [Foods](#foods)
  - [Layout file](#layout-file)
- [Recipes file](#recipes-file)
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

- **-this.yml**
- **/food_layout.yml** see [layout](#layout)
- **/foods** see [foods](#foods)
  - [Layout file](#layout-file)
- **/nutrients** all misc counters, may override default
  - water
  - beverages
  - misc expenses that are no food
- **recipes.yml** see [recipes file](#recipes-file)
- **sports.yml**
- **supplments.yml**
- **tips.html** used if exists, or default in /misc


Foods
----------------------------------------------------------

- sorting in UI like layout.yml, all left as entered
- all fields see -> readme

- field info available in app as well (in development), see def file [inline_help/foods.yml](../src/misc/inline_help/foods.yml)


Recipes file
----------------------------------------------------------

```yaml

My recipe:
                     # amount:
  Chick R Bio:  100g # < 0 (1/2)    is percent of pack
  Brokkoli R:        # > 0 (e.g. 2) is piece(s)
  Olivenöl:          # or with "g" (100g), "ml" (100ml)
```


Layout
----------------------------------------------------------

- left over nutrients and recipes will be attached below (group Misc foods)
- you can add each food multiple times

```yaml

tabName:

-- Data --------     -- Layout --------
                      ___________________    
  My nutrient group:   | My nutrient group     # nutrient names are from recipes.yml, foods.yml
    list:              |-------------------    # or misc.yml (use multiple times possible)
      - Recipe         | Recipe   |
      - or nutrient    | Nutrient | 1/4 | ...  < amounts as defined in
      - ...            |          |              foods.yml

  My nutrient group (color:#e0e0e0, fold:true):  # Visually group with color
    (i):                                         # Comment (i) switch in UI
    short:                                       # always visible
    list:
      - ...
```
