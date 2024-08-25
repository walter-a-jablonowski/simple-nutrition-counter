
Overview

- [Usability concept](#usability-concept)
- [PC, Tablet wide screen](#pc-tablet-wide-screen)
- [Smartphone (portrait)](#smartphone-portrait)
- [Grid structure food page](#grid-structure-food-page)
- [AI](#ai)


Usability concept MOV
----------------------------------------------------------

- menu entries low use ~5 times per day
  - no menu bar at all, round hover btn lower right (or left for left handed, configurable)
  - one click more per menu use, but more space for app content
- menu entries freq used: add bar (one click less, consume some space in favor of less clicks)
  - top bar: users are more used to
  - bottom bar more usable on smartphone one handed (nu users may difficulties understand this)
- information that should always be visible
  - use a menu bar (more like a status bar)
  - combine with menu
- app name: obsolete


PC, Tablet wide screen
----------------------------------------------------------

portrait like smartphone below
or: like wide screen with only one col in food list

```
 --------------------------------------------------------
| ( ) User v                               [-1day] (i) * |  CURRENTLY: keep above even if below more easy on
|--------------------------------------------------------|    smartphone with one hand because people are more used to this (see menu below)
| Day list         | Goals (quick summary)             v |  Day list might be shown for This day and Nutrients
|                  |-------------------------------------|    third tab gets full width (modify content layout)
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
