# Dev info - UI Design

Overview

- [PC, Tablet wide screen](#pc-tablet-wide-screen)
- [Smartphone (portrait)](#smartphone-portrait)
- [Grid structure food page](#grid-structure-food-page)
- [Scrollbars](#scrollbars)


PC, Tablet wide screen
----------------------------------------------------------

```
 v narrow
                                                                  Third tab gets full width (modify content layout)
 --------------------------------------------------------------
| ( ) User v [-1day] | Goals (quick summary)             v |   |  Header and daily goals 
|--------------------|-------------------------------------|   |    header: less often used controls (people are used to have this above)
| Day list           | (Food) (Suppl) ...                  |   |      status information always visible
|                    |-------------------------------------|   |  Tabs
|                    | Food list       |                   |   |  
|                    |                 |                   |   |  
|                    |                 |                   |(i)|  
|                    |                 |                   |   |  
|                    |                 |                   |   |  
|                    |                 |                   |   |  
|                    |                 |                   |   |  
|                    |                 |              _    |   |  Vertical side menu (menu entries freq used)
|                    |                 |             \ /   |   |    alternative: circular menu save space
|                    | [Add food|v] ...                    |   |  maybe use some split btn save space
 --------------------------------------------------------------     [Food] [Suppl] [On the go] [Fin] [Haush] [Misc]
```


Smartphone (portrait)
----------------------------------------------------------

no landscape version

```
 ---------------------   
| ( ) User [-1day]  v |
 ---------------------
| Day list            |  day list and goals full height
|                     |
|                     |
|                     |  
|---------------------|
| < Goals >           |  maybe foldable (expand up)
|                     |
|---------------------|
| [ Add food ]        |  scroll entries
|  ...                |  or: open food list in modal (space for day list)
|                     |  
|                     |
|                     |
|---------------------|
|                     |  Menu: easy one hand access on smartphones
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


Scrollbars
----------------------------------------------------------

Detection by input device, not viewport size, via `@media (hover: hover) and (pointer: fine)`.

- PC (mouse / fine pointer): visible (native scrollbar acts as affordance)
- Tablet: hidden (touch input)
- Smartphone: hidden (touch input)
- User opt-out via `settings.yml > hideScrollbars: true` -> `body.no-scrollbars` hides them on every device (e.g. touchpad-only PC users)

Exception: the nutrition widgets bar (`.nutrition-widgets .overflow-auto`) never shows a horizontal scrollbar - a custom scroll-arrow button is the affordance.
