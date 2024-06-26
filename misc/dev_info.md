# Dev info

Common
----------------------------------------------------------

- Code is minimalistic intentionally
  - we use only: PHP, js, bootstrap, maybe a view styles
  - no database, just parsable text files
- view.php is for including in a larger app
  - html ids have a prefix

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



### Yaml represantation

We use yaml and (at least currently) tsv as our database

```yaml
- 
  name:   My food S Bio
  weight: 
  # ...

-
  name:   calories
  amount: 
  # ...

- 
  name:   My food S Bio
  amountMale:
```

For a more intuitive, fluent, natural usage in this format/representation of the model we define that:

- the name should be used as id with no numeric ids
- it should written in the key
- and the link class/table should be saved inline in foods under a key nutrients (view like)
- all entries in the foods file implicit have the field "type: food"

Currently ignored cause we don't know in foods: How do we use the sub type B 12 methylcobalamin ? (kind of combined key name + type)


```yaml

My food S Bio:

  weight: 
  # ...

  nutrients:

    "@inline": ident -> amount  # some syntax could be used 2 highlight that the link class is saved inline

    fat:
    # ...
```
