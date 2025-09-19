
- [ ] unsure if the unprecise btns really filter out date
  - looks like we don't see all data (until 2025-03)
- [ ] verfiy that data shown in chart is right
- [x] timespan

  In the main app I also calculate an eating time per data file. Below you can see how it is calculated. Add a chart that looks like the existing charts and show the eating time per day/file.

  Eating time calculation from main app:

  ```javascript
  // eating time calculation

  const [hours1, minutes1, seconds1] = foodEntries[0].time.split(':').map(Number)
  const [hours2, minutes2, seconds2] = foodEntries[foodEntries.length - 1].time.split(':').map(Number)

  let diffSeconds = (hours2 * 3600 + minutes2 * 60 + seconds2) - (hours1 * 3600 + minutes1 * 60 + seconds1)
  if( diffSeconds < 0 )  diffSeconds += 24 * 3600

  const hours = Math.floor(diffSeconds / 3600)
  const mins  = Math.floor((diffSeconds % 3600) / 60)  // TASK: use classes and single id for the view

  let timespan = `${ hours.toString().padStart(2, '0')}:${ mins.toString().padStart(2, '0')}`
  ```


- [ ] ask ai to move as much functionallity as possible from js to php
- [ ] simplify code if possible
