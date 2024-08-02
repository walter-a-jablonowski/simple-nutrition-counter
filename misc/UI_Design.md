
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
|                  | [Add food|v] ...                    |  maybe use some split btn save space
|                  | (Food) (Suppl) ...                  |    [Food] [Suppl] [On the go] [Fin] [Haush] [Misc]
|                  |                                     |  maybe use a tab
|                  | Food list       |                   |
|                  |                 |                   |
|                  |                 |                   |
|                  |                 |                   |
|                  |                 |                   |
|                  |                 |                   |
|                  |                 |                   |
|--------------------------------------------------------|
| This day    | Nutrients    | Last days    | ...        |  Tab bar
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
|                     |  
|                     |
|---------------------|
| Goals             v |  maybe foldable (expand up)
|---------------------|
| [ Add food |v]      |  simple: just the list as above and also scroll day entries
| (Food) ...          |  better: btn only and open food list in modal (space for day list)
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
