class FoodsEventController
{
  constructor(args)
  {
    this.date = null

    // Binding

    this.lastDayBtnClick        = this.lastDayBtnClick.bind(this)
    // this.nextDayBtnClick     = this.nextDayBtnClick.bind(this)
    // this.thisDayBtnClick     = this.thisDayBtnClick.bind(this)
    this.saveDayEntriesBtnClick = this.saveDayEntriesBtnClick.bind(this)
    this.foodItemClick          = this.foodItemClick.bind(this)
    this.saveFoodsBtnClick      = this.saveFoodsBtnClick.bind(this)
    this.updSums                = this.updSums.bind(this)
    // this.#saveDayEntries     = this.#saveDayEntries.bind(this)  // TASK: can't be done

    // BS

    // const popoverTriggerList = query('[data-bs-toggle="popover"]')
    // const popoverList = [...popoverTriggerList].map( popoverTriggerEl => new bootstrap.Popover( popoverTriggerEl, {
    //   html: true,
    //   customClass: 'app-modal'
    // }))

    // Modal

    // const myModal = new bootstrap.Modal('#myModal'), options)  // some error
    this.newEntryModal = new bootstrap.Modal( query('#newEntryModal'))
    // myModal.show()  // also, you can pass a DOM element as an argument that can be received in the modal events (as the relatedTarget property). (i.e. const modalToggle = document.getElementById('toggleMyModal'); myModal.show(modalToggle)
    // $('#newEntryModal').modal('hide')

    query('#newEntryModal').addEventListener('show.bs.modal', event => {
      
      query('#modalNameInput').value     = 'Misc entry'  // default
      query('#modalWeightInput').value   = ''
      query('#modalPiecesInput').value   = ''
      query('#modalUsedSelect').value    = 'null'
      query('#modalCaloriesInput').value = ''
      query('#modalFatInput').value      = ''
      query('#modalCarbsInput').value    = ''
      query('#modalFibreInput').value    = ''
      query('#modalAminoInput').value    = ''
      query('#modalSaltInput').value     = ''
      query('#modalPriceInput').value    = ''
    })

    // Sortable (advanced)  #code/advancedDayEntries

    // $('#dayEntries').sortable({
    //   handle:      '.handle',
    //   placeholder: 'ui-state-highlight'
    // })
    //    
    // $('#dayEntries').disableSelection()
  }


  // Switch day

  // multiple days version

  // lastDayBtnClick(event)
  // {
  //   let currentDate = new Date( query('#dateDisplay').textContent)
  //
  //   currentDate.setDate( currentDate.getDate() - 1)
  //   window.location.href = `?date=${ currentDate.toISOString().split('T')[0]}`
  // }
  //
  // nextDayBtnClick(event)
  // {
  //   let currentDate = new Date( query('#dateDisplay').textContent)
  //
  //   currentDate.setDate( currentDate.getDate() + 1)
  //   window.location.href = `?date=${ currentDate.toISOString().split('T')[0]}`
  // }
  //
  // thisDayBtnClick(event)

  lastDayBtnClick(event)
  { 
    if( event.target.dataset.sel === 'current')
    {
      let currentDate = new Date( this.date)

      currentDate.setDate( currentDate.getDate() - 1)
      window.location.href = `?date=${ currentDate.toISOString().split('T')[0]}`
    }
    else if( event.target.dataset.sel === 'last')
    {
      window.location.href = `index.php`
    }
  }


  // Settings btn (tab content see below)

  settingsBtnClick(event)
  {
    var mainView     = query('#mainView')
    var settingsView = query('#settingsView')

    if( mainView.style.display === 'block')
    {
      mainView.style.display     = 'none'
      settingsView.style.display = 'block'
    }
    else
    {
      mainView.style.display     = 'block'
      settingsView.style.display = 'none'
    }
  }


  // List: btns

  saveDayEntriesBtnClick(event)
  {
    // Manual entering values: current solution is enter values => save => reload

    this.#saveDayEntries( true )
    window.location.reload()
  }

  newEntryBtn(event)
  { 
    this.newEntryModal.show()
  }

  newEntrySaveBtn(event)
  {
    // modal see also construct

    let weight     = query('#modalWeightInput').value
    let usedSelect = query('#modalUsedSelect')
    let usage      = 'precise'

    // similar PHP controller

    usage = usedSelect.options[usedSelect.selectedIndex].dataset.usage

    let usedWeight = usage === 'pack'   ? weight * usedSelect.value : (
                     usage === 'pieces' ? (weight / pieces) * query('#modalPiecesSelect').value
                   : usedSelect.value  // precise
    )

    let entry = {
      food:     query('#modalNameInput').value,  // TASK: rename
      calories: parseFloat( query('#modalCaloriesInput').value.trim().replace(',', '.')) || 0,
      fat:      parseFloat( query('#modalFatInput').value.trim().replace(',', '.'))      || 0,
      carbs:    parseFloat( query('#modalCarbsInput').value.trim().replace(',', '.'))    || 0,
      amino:    parseFloat( query('#modalAminoInput').value.trim().replace(',', '.'))    || 0,
      salt:     parseFloat( query('#modalSaltInput').value.trim().replace(',', '.'))     || 0,
      price:    parseFloat( query('#modalPriceInput').value.trim().replace(',', '.'))    || 0,
      nutrients: {}
    }

    let fibreInp = query('#modalFibreInput')

    if( fibreInp && fibreInp.value.trim() !== '')
      entry.fibre = fibreInp.value.trim()

    entry = {
      food:     entry.food,
      // multiplying by 10 and then dividing by 10: This is a common technique to round to a specific number of decimal places—in this case, one decimal place
      calories:  Math.round( entry.calories * (usedWeight / 100) * 10) / 10,  // 10 is one decimal place
      fat:       Math.round( entry.fat      * (usedWeight / 100) * 10) / 10,
      carbs:     Math.round( entry.carbs    * (usedWeight / 100) * 10) / 10,
      amino:     Math.round( entry.amino    * (usedWeight / 100) * 10) / 10,
      salt:      Math.round( entry.salt     * (usedWeight / 100) * 10) / 10,  // 100 2 decimal places
      price:     ! entry.price ? 0 : Math.round( entry.price * (usedWeight / weight) * 100) / 100,
      nutrients: entry.nutrients
    }

    // TASK: function for upper decimal places

    // function roundToDecimalPlace(number, decimalPlaces) {
    //   let factor = Math.pow(10, decimalPlaces);
    //   return Math.round(number * factor) / factor;
    // }

    if('fibre' in entry)
      entry.fibre = Math.round(entry.fibre * (usedWeight / 100) * 10) / 10

    dayEntries.push(entry);  // simple version
    query('#dayEntries').value += `\n${entry.food}  ${entry.calories}  ${entry.fat}  ${entry.carbs}  ${entry.amino}  ${entry.salt}  ${entry.price}`
                               +  '  {}'
    this.updSums()
    this.#saveDayEntries()

    this.newEntryModal.hide()

    // window.location.reload()  // simple version: fix tsv
  }


  // List: entries

  foodItemClick(event)
  {
    let target    = event.target
    let food      = target.dataset.food
    let calories  = target.dataset.calories
    let price     = target.dataset.price

    let nutritionalValues = JSON.parse(target.dataset.nutritionalvalues)

    let entry = {
      food:      food,  // TASK: rename
      calories:  calories,
      fat:       nutritionalValues.fat,
      carbs:     nutritionalValues.carbs,
      amino:     nutritionalValues.amino,
      salt:      nutritionalValues.salt,
      price:     price,
      nutrients: {
        fat:   JSON.parse( target.dataset.fattyacids),
        amino: JSON.parse( target.dataset.aminoacids),
        vit:   JSON.parse( target.dataset.vitamins),
        min:   JSON.parse( target.dataset.minerals),
        sec:   JSON.parse( target.dataset.secondary)
      }
    }

    dayEntries.push( entry )
    
    // Find the length of the longest strings

    let maxFoodLength     = Math.max( ...dayEntries.map( entry => entry.food.length))
    let maxCaloriesLength = Math.max( ...dayEntries.map( entry => String(entry.calories).length))  // for some reason we must do it like this here
    let maxFatLength      = Math.max( ...dayEntries.map( entry => String(entry.fat).length))
    let maxCarbsLength    = Math.max( ...dayEntries.map( entry => String(entry.carbs).length))
    let maxAminoLength    = Math.max( ...dayEntries.map( entry => String(entry.amino).length))
    let maxSaltLength     = Math.max( ...dayEntries.map( entry => String(entry.salt).length))
    let maxPriceLength    = Math.max( ...dayEntries.map( entry => String(entry.price).length))

    // Align cols

    let formattedText = dayEntries.map( entry => {

      let foodPadding     = ' '.repeat( maxFoodLength     - entry.food.length + 2)              // 2 extra spaces
      let caloriesPadding = ' '.repeat( maxCaloriesLength - String(entry.calories).length + 2)  // for some reason we must do it like this here
      let fatPadding      = ' '.repeat( maxFatLength      - String(entry.fat).length + 2)
      let carbsPadding    = ' '.repeat( maxCarbsLength    - String(entry.carbs).length + 2)
      let aminoPadding    = ' '.repeat( maxAminoLength    - String(entry.amino).length + 2)
      let saltPadding     = ' '.repeat( maxSaltLength     - String(entry.salt).length + 2)
      let pricePadding    = ' '.repeat( maxPriceLength    - String(entry.price).length + 2)

      return `${entry.food}${foodPadding}${entry.calories}${caloriesPadding}${entry.fat}${fatPadding}${entry.carbs}${carbsPadding}${entry.amino}${aminoPadding}${entry.salt}${saltPadding}${entry.price}${entry.pricePadding}`
             + YAMLish.dump( entry.nutrients )

    }).join('\n')

    query('#dayEntries').value = formattedText

    // Upd sums and save entries

    this.updSums()
    this.#saveDayEntries()
  }

  saveFoodsBtnClick(event)
  {
    ajax.send('saveFoods', { data: query('#foods').value }, function( result, data ) {

      if( result === 'success')
        query('#foodsUIMsg').innerHTML = 'Saved'
      else
        query('#foodsUIMsg').innerHTML = result.message
    })
  }


  // Helper

  /*@
  
  - public cause used in view

  */
  updSums() /*@*/
  {
    let caloriesSum = Number( dayEntries.reduce((sum, entry) => sum + Number(entry.calories), 0).toFixed(1))
    let fatSum      = Number( dayEntries.reduce((sum, entry) => sum + Number(entry.fat),      0).toFixed(1))
    let aminoSum    = Number( dayEntries.reduce((sum, entry) => sum + Number(entry.amino),    0).toFixed(1))
    let saltSum     = Number( dayEntries.reduce((sum, entry) => sum + Number(entry.salt),     0).toFixed(1))
    let priceSum    = Number( dayEntries.reduce((sum, entry) => sum + Number(entry.price),    0).toFixed(2))

    query('#caloriesSum').textContent = caloriesSum
    query('#fatSum').textContent      = fatSum
    query('#aminoSum').textContent    = aminoSum
    query('#saltSum').textContent     = saltSum
    query('#priceSum').textContent    = priceSum


    // (TASK) #code/progress

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

    // query('#caloriesProgressBar').style.width   = `${percentage}%`
    // query('#caloriesProgressBar').className     = `progress-bar ${progressBarColor}`
    // query('#caloriesProgressLabel').textContent = `${currentSum}/${recommended}`
  }

  #saveDayEntries( uiMsg = false )
  {
    ajax.send('saveDayEntries', { date: this.date, data: query('#dayEntries').value }, function( result, data ) {

      if( result === 'success' && uiMsg)
        query('#uiMsg').innerHTML = 'Saved'
      elseif( result !== 'success')
        query('#uiMsg').innerHTML = result.message
    })
  }
}
