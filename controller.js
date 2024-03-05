
function addFood(food, calories, amino, salt)
{
  dayEntries.push({ food, calories, amino, salt })  // because we need a unique name for dashb
                                                      // object keys will be the var name
  // Find the length of the longest strings

  let maxFoodLength     = Math.max( ...dayEntries.map( entry => entry.food.length))
  let maxCaloriesLength = Math.max( ...dayEntries.map( entry => String(entry.calories).length))
  let maxAminoLength   = Math.max( ...dayEntries.map( entry => String(entry.amino).length))
  let maxSaltLength     = Math.max( ...dayEntries.map( entry => String(entry.salt).length))

  // Align cols

  let formattedText = dayEntries.map( entry => {

    let foodPadding     = ' '.repeat( maxFoodLength     - entry.food.length + 2)  // 2 extra spaces for food
    let caloriesPadding = ' '.repeat( maxCaloriesLength - String(entry.calories).length + 2)
    let aminoPadding   = ' '.repeat( maxAminoLength   - String(entry.amino).length + 2)

    return `${entry.food}${foodPadding}${entry.calories}${caloriesPadding}${entry.amino}${aminoPadding}${entry.salt}`

  }).join('\n')

  document.getElementById('dayEntries').value = formattedText

  // Upd sums and save entries

  updSums()
  saveDayEntries()
}

function saveManualInput(event)
{
  // Manual entering values: current solution is enter values => save => reload

  saveDayEntries()
}

function saveDayEntries(event)
{
  // let xhr = new XMLHttpRequest()  // (TASK) can use send from below
  // xhr.open('POST', 'ajax/save_day_entries.php', true)
  // xhr.setRequestHeader('Content-type', 'text/plain;charset=UTF-8')
  //
  // xhr.send(formattedText)

  send('ajax/save_day_entries.php', document.getElementById('dayEntries').value, function( result, data) {

    if( result === 'success')
      document.getElementById('foodsUIMsg').innerHTML = 'Saved'
    else
      document.getElementById('foodsUIMsg').innerHTML = result.message
  })
}

function saveFoods(event)
{
  send('ajax/save_foods.php', document.getElementById('foods').value, function( result, data) {

    if( result === 'success')
      document.getElementById('foodsUIMsg').innerHTML = 'Saved'
    else
      document.getElementById('foodsUIMsg').innerHTML = result.message
  })
}

function updSums()
{
  let caloriesSum = dayEntries.reduce((sum, entry) => sum + Number(entry.calories), 0)
  let aminoSum    = dayEntries.reduce((sum, entry) => sum + Number(entry.amino), 0)
  let saltSum     = dayEntries.reduce((sum, entry) => sum + Number(entry.salt), 0)

  document.getElementById('caloriesSum').textContent = caloriesSum + ' kcal'
  document.getElementById('aminoSum').textContent    = aminoSum + ' g'
  document.getElementById('saltSum').textContent     = saltSum + ' g'


  // TASK: progress

  // let currentSum  = 100  // This should be dynamically set based on the day's sum
  // let recommended = 500
  // let tolerance   = 0.05

  // let percentage = (currentSum / recommended) * 100
  // percentage = Math.min(percentage, 100)  // ensure it doesn't exceed 100%

  // let progressBarColor = 'bg-secondary'

  // if( currentSum >= recommended * (1 - tolerance) && currentSum <= recommended * (1 + tolerance))
  //   progressBarColor = 'bg-success'
  // else if(currentSum > recommended * (1 + tolerance))
  //   progressBarColor = 'bg-danger'

  // document.getElementById('calorieProgressBar').style.width = `${percentage}%`
  // document.getElementById('calorieProgressBar').className = `progress-bar ${progressBarColor}`
  // document.getElementById('calorieProgress').textContent = `${currentSum}/${recommended}`
}
