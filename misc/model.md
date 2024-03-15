# Model

```
   --------------
  |     Food     |
   --------------
  | - weight     |
  | - ...        |
   --------------
        | 1
        |  
        |
        | *
   ---------------                     --------------
  | NutrientsList | 1               1 |  Nutrient    |
  |---------------| ----------------- |--------------|
  | - name        |                   | - amountMale |
  | - amount      |                   | - ...        |
   ---------------                     --------------
```


Yaml represantation
----------------------------------------------------------

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
- and the link class/table should be saved inline in foods under a key nutrients

Currently ignored cause we don't know in foods: How do we use the sub type B 12 methylcobalamin ? (kind of combined key name + type)

```yaml
My food S Bio:

  weight: 
  # ...

  nutrients:

    calories:
    # ...
```