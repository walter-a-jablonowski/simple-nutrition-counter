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
