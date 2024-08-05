
Overview

- [PC, Tablet wide screen](#pc-tablet-wide-screen)
- [Smartphone (portrait)](#smartphone-portrait)
- [Grid structure food page](#grid-structure-food-page)
- [AI](#ai)


PC, Tablet wide screen
----------------------------------------------------------

portrait like smartphone below

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


AI
----------------------------------------------------------

Can you make a BS 5.3 grid layout that has the following structure?

```
row
  col      content: "day list" list of nutrients consumed on a day (list-group)
  col
    row    
      col  content: "goals" this days nutrient intake goals
    row    
      col  content: "food list" list of available foods
```

Responsive behavior:

On PC or tablet in landscape mode: day list goes left of the screen full height,
the rest goes right ("goals" has a fix height, while food list takes the rest of
available height.

On a smartphone in portrait mode: day list is above goals. Landscape is unavailable
on smartphones, if possible keep portrait when the phone is in landscape.

On PC or tablet in portrait mode: same as smartphone.
