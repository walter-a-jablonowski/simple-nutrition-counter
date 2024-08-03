# Dev info

Overview

- [Common](#common)
- [Model](#model)
- [UI design](UI_Design.md)


Common
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

- **Naming:** Camel, entity identifier e.g. type start with upper case
- **Types:**  Entries may have an implicit type field `@type: food`
- **Ids:**    Speaking, readable (see readme files)

- Ids may be used as file name or saved inside the file
  - in files we prefer that the key is the id
- Link classes can be saved inline for simplicity

  ```yaml

  My food S Bio:

    # ...

    nutritionalValues:

      fat:  11  # fat = object ident of fattyAcids (short) -> amount
  ```
