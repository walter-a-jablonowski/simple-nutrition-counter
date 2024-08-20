
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
| ( ) User v                               [-1day] (i) * |  CURRENTLY: keep above even if below more easy on
|--------------------------------------------------------|    smartphone with one hand because people are more used to this (see menu below)
| Day list         | Goals (quick summary)             v |  maybe foldable
|                  |-------------------------------------|
|                  | (Food) (Suppl) ...                  |  maybe use a tab
|                  |                 |                   |
|                  | Food list       |                   |
|                  |                 |                   |
|                  |                 |                   |
|                  |                 |                   |
|                  |                 |                   |
|                  |                 |              _    |
|                  |                 |             \ /   |  Circular menu for easy one hand acces on smartphone
|                  | [Add food|v] ...                    |  maybe use some split btn save space
 --------------------------------------------------------     [Food] [Suppl] [On the go] [Fin] [Haush] [Misc]
```

alternative menue

```
 --------------------------------------------------------
| Day list         |                           | Menu    |  like old comm
|                  |                           |         |
 ...
 --------------------------------------------------------
```

AI: I make a bootstrap 5.3 app and want to replace the nav bar with my own special version of a nav bar.

It should be a narrow vertical bar on the right side of the screen. On top it has some icons that are the menu entries, on the bottom is the name of the app "My App" written with a vertical font. When the mouse hovers the menu bar it expands to the left (overlay) and beside the icons (menu entries) the text of the entry gets visible.


Smartphone (portrait)
----------------------------------------------------------

no landscape version

```
 ---------------------   
| ( ) User v    (i) * |
 ---------------------
| Day list          v |  day list and goals full height
|                     |
|                     |
|                     |
|                     |  
|                     |
|                     |  
|---------------------|
| Goals             v |  maybe foldable (expand up)
|                     |
|---------------------|
| [ Add food ]     _  |  simple: just the list and scroll entries
|  ...            \ / |  better: btn only and open food list in modal (space for day list)
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
