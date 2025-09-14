# Dev info

Overview

- [Tools overview](#tools-overview)
- [App design](#app-design)
- [Model](#model)
  - [YML representation](#yml-representation)
- [UI design](UI_Design.md)


Tools overview
----------------------------------------------------------

```
/tools        # dev tools
/src/tools    # tools
/src/reports
```

App design
----------------------------------------------------------

- Code is minimalistic intentionally
  - we use only: PHP, js, bootstrap, maybe a view styles
  - no database, just parsable text files
- We try to make intuitive, fluent, natural code and data files


Model
----------------------------------------------------------

```
  <<hasVariants>>
   --------------                      --------------
  |  Food        | *                1 |    Recipe    |
   --------------  ------------------  --------------
  | - weight     |         |          |              |
  | - ...        |   <<savedInline>>  |              |
   --------------      [FoodList]      --------------
        | 1
        |
        |   <<savedInline>>
        | -- [NutrientList]
        |
        | *
   ---------------
  |  Nutrient     |
   ---------------
  | - amountMale  |
  |               |
   ---------------
```


### YML representation

We use YML and (at least currently) tsv as our database. We save objects in a
hierarchical logical tree. For this we use index files for folders `-this.yml`.

Objects can start anywhere in the tree, outside or inside YML files.

- **Naming:** camelCase
- **Types:**  start with upper case `MyEntity`
  - implicit type field added in code `@type: Food` or `type: Food`
- **Ids:** camel `aminoAcids`
  - some speaking, readable cause more intuitive (see readme files)
    - we also use blanks `My food` or underscore ...
    - app may generate a blank free version in code
  - implicit field in code `@id: myFood` or `id: myFood`
  - may be used as file name or saved inside the file
    - in files we prefer that the key is the id

Link classes can be saved inline for simplicity

  ```yaml

  My food S Bio:

    # ...

    nutritionalValues:

      fat:  11  # fat = object ident of fattyAcids (short) -> amount
  ```
