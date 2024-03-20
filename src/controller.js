
class FoodsEventController
{
  constructor(args)
  {

    // Binding

    this.foodItemClick          = this.foodItemClick.bind(this)
    this.saveDayEntriesBtnClick = this.saveDayEntriesBtnClick.bind(this)
    this.saveFoodsBtnClick      = this.saveFoodsBtnClick.bind(this)
    this.updSums                = this.updSums.bind(this)
    // this.#saveDayEntries     = this.#saveDayEntries.bind(this)  // TASK: can't be done
  }

  foodItemClick(event)
  {
    let target    = event.target
    let food      = target.dataset.food
    let calories  = target.dataset.calories
    let nutrients = JSON.parse(target.dataset.nutrients)

    dayEntries.push({
      food:     food,
      calories: calories,
      fat:      nutrients.fat,
      amino:    nutrients.amino,
      salt:     nutrients.salt
    })
    
    // dayEntries.push({ food, calories, nutrients })  // TASK: (advanced) maybe

    // Find the length of the longest strings

    let maxFoodLength     = Math.max( ...dayEntries.map( entry => entry.food.length))
    let maxCaloriesLength = Math.max( ...dayEntries.map( entry => String(entry.calories).length))  // for some reason we must do it like this here
    let maxFatLength      = Math.max( ...dayEntries.map( entry => String(entry.fat).length))
    let maxAminoLength    = Math.max( ...dayEntries.map( entry => String(entry.amino).length))
    // let maxSaltLength  = Math.max( ...dayEntries.map( entry => entry.salt.length))

    // TASK: (advanced) use a loop for more nutrients

    // Align cols

    let formattedText = dayEntries.map( entry => {

      let foodPadding     = ' '.repeat( maxFoodLength     - entry.food.length + 2)              // 2 extra spaces
      let caloriesPadding = ' '.repeat( maxCaloriesLength - String(entry.calories).length + 2)  // for some reason we must do it like this here
      let fatPadding      = ' '.repeat( maxFatLength      - String(entry.fat).length + 2)
      let aminoPadding    = ' '.repeat( maxAminoLength    - String(entry.amino).length + 2)

      return `${entry.food}${foodPadding}${entry.calories}${caloriesPadding}${entry.fat}${fatPadding}${entry.amino}${aminoPadding}${entry.salt}`

    }).join('\n')

    document.getElementById('dayEntries').value = formattedText

    // Upd sums and save entries

    this.updSums()
    this.#saveDayEntries()
  }

  saveDayEntriesBtnClick(event)
  {
    // Manual entering values: current solution is enter values => save => reload

    this.#saveDayEntries()
    window.location.reload()
  }

  saveFoodsBtnClick(event)
  {
    send('ajax/save_foods.php', document.getElementById('foods').value, function( result, data) {

      if( result === 'success')
        document.getElementById('foodsUIMsg').innerHTML = 'Saved'
      else
        document.getElementById('foodsUIMsg').innerHTML = result.message
    })
  }

  #saveDayEntries()
  {
    send('ajax/save_day_entries.php', document.getElementById('dayEntries').value, function( result, data) {

      if( result === 'success')
        document.getElementById('foodsUIMsg').innerHTML = 'Saved'
      else
        document.getElementById('foodsUIMsg').innerHTML = result.message
    })
  }

  updSums()
  {
    let caloriesSum = Number( dayEntries.reduce((sum, entry) => sum + Number(entry.calories), 0).toFixed(1))
    let fatSum      = Number( dayEntries.reduce((sum, entry) => sum + Number(entry.fat),      0).toFixed(1))
    let aminoSum    = Number( dayEntries.reduce((sum, entry) => sum + Number(entry.amino),    0).toFixed(1))
    let saltSum     = Number( dayEntries.reduce((sum, entry) => sum + Number(entry.salt),     0).toFixed(1))

    document.getElementById('caloriesSum').textContent = caloriesSum
    document.getElementById('fatSum').textContent      = fatSum
    document.getElementById('aminoSum').textContent    = aminoSum
    document.getElementById('saltSum').textContent     = saltSum


    // (TASK) progress

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

    // document.getElementById('caloriesProgressBar').style.width   = `${percentage}%`
    // document.getElementById('caloriesProgressBar').className     = `progress-bar ${progressBarColor}`
    // document.getElementById('caloriesProgressLabel').textContent = `${currentSum}/${recommended}`
  }
}
