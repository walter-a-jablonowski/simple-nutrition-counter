
Overview

- [PC, Tablet wide screen](#pc-tablet-wide-screen)
- [Smartphone (portrait)](#smartphone-portrait)
- [Grid structure food page](#grid-structure-food-page)


PC, Tablet wide screen
----------------------------------------------------------

landscape like smartphone below

```
 --------------------------------------------------------
| ( ) User v [-1day]                               (i) * |
|--------------------------------------------------------|
| Day list         | Goals (quick summary)             v |  maybe foldable
|                  |-------------------------------------|
|                  | Food list       |                   |
|                  |                 |                   |
|                  |                 |                   |
|                  |                 |                   |
|                  |                 |                   |
|                  |                 |                   |
|                  |                 |                   |
|                  |                 |                   |
|                  |                 |                   |
|                  |                 |                   |
|--------------------------------------------------------|
| This day    | Nutrients    | Last days    | ...        |  <-- Tab bar
 --------------------------------------------------------
```


Smartphone (portrait)
----------------------------------------------------------

no landscape version

```
 ---------------------
| ( ) User v    (i) * |
|---------------------|
| Day list          v |
|                     |
|                     |
|                     |
|---------------------|
| Goals             v |  maybe foldable
|---------------------|
| [Foods]     [Suppl] |  <-- Btns open food list in
| [On the go] [Fin]   |      modal
| [Haush]     [Misc]  |
|                     |
|                     |
 ---------------------
| This day | ...      |
 ---------------------
```


Grid structure food page
----------------------------------------------------------

```
row
  col      day list
  col
    row    
      col  goals
    row    
      col  food list

        ... food list grid ...
```
