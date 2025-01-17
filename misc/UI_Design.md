
Overview

- [Usability concept](#usability-concept)
- [PC, Tablet wide screen](#pc-tablet-wide-screen)
- [Smartphone (portrait)](#smartphone-portrait)
- [Grid structure food page](#grid-structure-food-page)
- [AI](#ai)


Usability concept MOV
----------------------------------------------------------

- menu
  - menu entries freq used: always visible, reachable
  - menu entries low use ~5 times per day: less reachable space
- status information that should always be visible
  - we might use a header with in the content area as sime kind of status bar
    - could also contain user switch and less imp menu elems
- app name: obsolete


PC, Tablet wide screen
----------------------------------------------------------

### Conventional

(curerntly preferring innovative design below)

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
|                  |                 |             \ /   |  Circular menu for easy one hand access on smartphone
|                  | [Add food|v] ...                    |  maybe use some split btn save space
 --------------------------------------------------------     [Food] [Suppl] [On the go] [Fin] [Haush] [Misc]
```

Innovative menu

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
