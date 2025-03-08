class MainController
{
  constructor(args)
  {
    this.date = null

    // Binding

    this.userSelectChange       = this.userSelectChange.bind(this)
    this.lastDayBtnClick        = this.lastDayBtnClick.bind(this)
    // this.nextDayBtnClick     = this.nextDayBtnClick.bind(this)
    // this.thisDayBtnClick     = this.thisDayBtnClick.bind(this)
    this.settingsBtnClick       = this.settingsBtnClick.bind(this)
    this.saveDayEntriesBtnClick = this.saveDayEntriesBtnClick.bind(this)
    this.newEntryBtn            = this.newEntryBtn.bind(this)
    this.newEntrySaveBtn        = this.newEntrySaveBtn.bind(this)
    this.layoutItemClick        = this.layoutItemClick.bind(this)
    this.priceColClick          = this.priceColClick.bind(this)
    this.updPriceClick          = this.updPriceClick.bind(this)
    this.offLimitCheckChange    = this.offLimitCheckChange.bind(this)
    // this.#addDayEntry        = this.#addDayEntry.bind(this)     // TASK: can't be done
    this.updSummary             = this.updSummary.bind(this)
    // this.#saveDayEntries     = this.#saveDayEntries.bind(this)

    let crl = this

    // BS

    // TASK: make ingredients popover in food in work
    // - Settings > Me popover is working, most likely there is a problem with z-index or sth in modal
    // - currently using event on a div, maybe must be a btn
    // - see also code below

    // Manual version (didn't work with modal for some reason)
/*
    this.popoverTriggerList = query('[data-bs-toggle="popover"]')
    this.popoverList = [...this.popoverTriggerList].map( popoverTriggerEl => new bootstrap.Popover( popoverTriggerEl, {
      html: true
      customClass: 'popover-cus'
    }))
*/
    // AI Version

    // this.popoverTriggerList = [].slice.call( document.querySelectorAll('[data-bs-toggle="popover"]'))
    this.popoverTriggerList = [].slice.call( query('[data-bs-toggle="popover"]'))
    this.popoverList = this.popoverTriggerList.map( function( popoverTriggerEl) {
      return new bootstrap.Popover( popoverTriggerEl, {
        html: true,
        customClass: 'popover-cus'
      })
    })

    // Sample
    //
    // <div class="popover popover-cus bs-popover-auto fade show"
    //      role="tooltip" id="popover653960"
    //      style="position: absolute; inset: 0px auto auto 0px; margin: 0px; transform: translate3d(103.2px, 66.4px, 0px);"
    //      data-popper-placement="right"
    // >
    //   <div class="popover-arrow" style="position: absolute; top: 0px; transform: translate3d(0px, 49.6px, 0px);"></div>
    //   <div class="popover-body">These settings are used for calculating the right nutrient amounts</div>
    // </div>

    // if click was outside the popover and its trigger close
    // TASK: alternative maybe: https://getbootstrap.com/docs/5.3/components/popovers/#dismiss-on-next-click
    // see also upd in modal below
    
    // document.addEventListener('click', function(e) {
    event('click', function(e) {
    
      if( ! e.target.closest('.popover') && ! e.target.closest('[data-bs-toggle="popover"]')) {
        crl.popoverTriggerList.forEach( function(popoverTriggerEl) {
          let popover = bootstrap.Popover.getInstance(popoverTriggerEl)
          if( popover ) popover.hide()
        })
      }
    })

    const popover = new bootstrap.Popover('.info-popover', {
      container: '#infoModal .modal-body'
    })

    // Info modal (used for groups and food)
    
    const modal = query('#infoModal')
    // modal.addEventListener('show.bs.modal', event => {
    event('show.bs.modal', event => {

      if( event.target.id != 'infoModal')
        return;

      const btn = event.relatedTarget
      
      if( btn.getAttribute('data-title').startsWith('#'))
        modal.find('.modal-title').innerHTML = query( btn.getAttribute('data-title')).innerHTML
      else
        modal.find('.modal-title').innerHTML = btn.getAttribute('data-title')
      
      modal.find('.modal-body').innerHTML = query( btn.getAttribute('data-source')).innerHTML

      // upd needed for some reason (by AI)

      crl.popoverList.forEach( function(popover) {
        popover.update()
      })
      
      // or
      // var popover = bootstrap.Popover.getInstance(document.querySelector('[data-bs-toggle="popover"]'))
      // if( popover )
      //   popover.update()
    })

    // New modal

    // const myModal = new bootstrap.Modal('#myModal'), options)  // some error
    this.newEntryModal = new bootstrap.Modal( query('#newEntryModal'))
    // myModal.show()  // also, you can pass a DOM element as an argument that can be received in the modal events (as the relatedTarget property). (i.e. const modalToggle = document.getElementById('toggleMyModal'); myModal.show(modalToggle)
    // $('#newEntryModal').modal('hide')

    // query('#newEntryModal').addEventListener('show.bs.modal', event => {
    query('#newEntryModal').event('show.bs.modal', event => {
      
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
      // query('#flexCheckDefault').checked = false  // TASK: devMode only
      // TASK: maybe set tab (remains clicked)
    })

    // Mermaid  // TASK: problems in modal (works in page)

    // mermaid.initialize({  // maybe unneeded
    //   securityLevel: 'loose',
    // })

    query('#helpModal').event('shown.bs.modal', function() {
      // mermaid.init( undefined, queryFirst('#helpModal .mermaid'))
      query('#helpModal .mermaid').forEach( diagr => {
        mermaid.init( undefined, diagr)
      })
    })
    
    // Sortable (advanced)  #code/advancedDayEntries

    // $('#dayEntries').sortable({
    //   handle:      '.handle',
    //   placeholder: 'ui-state-highlight'
    // })
    //    
    // $('#dayEntries').disableSelection()
  }


  // Change user

  userSelectChange(event)
  {
    ajax.send('changeUser', { user: event.target.value }, function( result, data ) {

      if( result === 'success')
        window.location.reload()
      else
        alert('error')  // TASK
    })
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
      type:      'F',
      food:      entry.food,
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
    //   let factor = Math.pow(10, decimalPlaces)
    //   return Math.round(number * factor) / factor
    // }

    if('fibre' in entry)
      entry.fibre = Math.round(entry.fibre * (usedWeight / 100) * 10) / 10

    this.#addDayEntry( entry )
    this.newEntryModal.hide()
  }


  // List: entries

  layoutItemClick(event)
  {
    // TASK: add types for user > misc
    // if type === MiscBuyable
    // if type === Food

    let target   = event.target.closest('.amount-btn')
    let food     = target.dataset.food
    let calories = target.dataset.calories
    let price    = target.dataset.price

    let nutritionalValues = JSON.parse(target.dataset.nutritionalvalues)

    // new version
    // console.log( queryData('.food-item ...', ['food']))

    let entry = {
      type:     'F',
      food:     food,  // TASK: rename
      calories: calories,
      fat:      nutritionalValues.fat,
      carbs:    nutritionalValues.carbs,
      amino:    nutritionalValues.amino,
      salt:     nutritionalValues.salt,
      price:    price,
      nutrients: {
        fibre: JSON.parse( nutritionalValues.fibre || 0 ),  // TASK: or only add when set (see updSummary() for sum only if available)
        fat:   JSON.parse( target.dataset.fattyacids ),
        amino: JSON.parse( target.dataset.aminoacids ),
        vit:   JSON.parse( target.dataset.vitamins ),
        min:   JSON.parse( target.dataset.minerals ),
        sec:   JSON.parse( target.dataset.secondary )
      }
    }

    this.#addDayEntry( entry )
  }


  priceColClick(event)
  {
    let target = event.target.tagName !== 'TD' ? event.target.closest('td') : event.target

    target.find('.price-label-view').style.display = 'none'
    target.find('.price-input-view').style.display = 'block'
  }


  updPriceClick(event)
  {
    let name  = event.target.dataset.name
    let input = event.target.closest('.price-col').find('.price-inp')
    let price = input.textContent.trim()

    ajax.send('savePrice', { name: name, price: price }, function(result, data) {
      if( result === 'success' )
        window.location.reload()       // TASK: maybe show the label again
      else
        alert('Error updating price')  // TASK: some inline error
    })
  }


  offLimitCheckChange(event)
  {
    query('#nutrientsList .nutrients-entry').forEach( entry => {

      const current = parseFloat(entry.dataset.current)
      const lower   = parseFloat(entry.dataset.lower)
      const upper   = parseFloat(entry.dataset.upper)
      
      if( event.target.checked )
      {
        if( current < lower || current > upper )
          entry.style.display = 'block'
        else
          entry.style.display = 'none'
      }
      else
        entry.style.display = 'block'
    })
  }


  timeSwitchClick(event)
  {
    if( event.target.classList.contains('dropdown-item'))
    {
      event.preventDefault()

      const selectedPeriod = event.target.getAttribute('data-value')
      const label = document.querySelector('.time-switch .label')

      label.textContent = event.target.textContent

      query('.head-view .avg').forEach( span => {
        span.style.display = 'none'
      })

      query(`.head-view .avg.${selectedPeriod}`).forEach( span => {
        span.style.display = 'inline'
      })
    }
  }


  // Helper

  /*@
  
  */
  #addDayEntry( entry ) /*@*/
  {
    // TASK: (advanced) time on server (currently a problem cause we still use save btn))
    //       user needs a timezone setting if done on server

    let now    = new Date()
    entry.time = now.toTimeString().split(' ')[0]  // .replaceAll(':', '')  // gives HHMMSS format

    // TASK: add types for user > misc
    // if type === MiscBuyable
    // if type === Food

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

      return `${entry.time}  ${entry.type}  ${entry.food}${foodPadding}${entry.calories}${caloriesPadding}${entry.fat}${fatPadding}${entry.carbs}${carbsPadding}${entry.amino}${aminoPadding}${entry.salt}${saltPadding}${entry.price}${pricePadding}`
             + YAMLish.dump( entry.nutrients )

    }).join('\n')

    query('#dayEntries').value = formattedText

    this.updSummary()
    this.#saveDayEntries()
  }

  /*@
  
  - public cause used in view

  */
  updSummary() /*@*/
  {
    const foodEntries = dayEntries.filter( entry => entry.type === 'F' || entry.type === 'FE')

    if( foodEntries.length == 0)
      return;

    // Quick summary

    // let caloriesSum = Number( foodEntries.reduce((sum, entry) => sum + Number(entry.calories), 0).toFixed(1))  // one decimal place
    query('#caloriesSum').textContent = Math.round( foodEntries.reduce((sum, entry) => sum + Number(entry.calories), 0))

    // eating time

    const [hours1, minutes1, seconds1] = foodEntries[0].time.split(':').map(Number)
    const [hours2, minutes2, seconds2] = foodEntries[foodEntries.length - 1].time.split(':').map(Number)

    let diffSeconds = (hours2 * 3600 + minutes2 * 60 + seconds2) - (hours1 * 3600 + minutes1 * 60 + seconds1)
    if( diffSeconds < 0 )  diffSeconds += 24 * 3600

    const hours = Math.floor(diffSeconds / 3600)
    const mins  = Math.floor((diffSeconds % 3600) / 60)  // TASK: use classes and single id for the view

    query('#timeSum').textContent = `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`

    query('#fatSum').textContent   = Math.round( foodEntries.reduce((sum, entry) => sum + Number(entry.fat),   0))  // just the int
    query('#aminoSum').textContent = Math.round( foodEntries.reduce((sum, entry) => sum + Number(entry.amino), 0))
    query('#carbsSum').textContent = Math.round( foodEntries.reduce((sum, entry) => sum + Number(entry.carbs), 0))
    // query('#sugarSum').textContent = Math.round( foodEntries.reduce((sum, entry) => sum + Number(entry.sugar), 0))  // TASK

    // let fibreSum = Number( foodEntries.reduce((sum, entry) => {
    //   return sum + (entry.nutrients.fibre ? Number(entry.nutrients.fibre) : 0)  // only if set
    // }, 0).toFixed(1))

    query('#fibreSum').textContent = Math.round( foodEntries.reduce((sum, entry) => sum + Number(entry.nutrients.fibre), 0))
    // query('#saltSum').textContent = Number( foodEntries.reduce((sum, entry) => sum + Number(entry.salt), 0)).toFixed(1)
    query('#saltSum').textContent  = foodEntries.reduce((sum, entry) => sum + Number(entry.salt),  0).toFixed(1)  // 1 decimal place
    query('#priceSum').textContent = foodEntries.reduce((sum, entry) => sum + Number(entry.price), 0).toFixed(2)  // 2 decimal places


    // Nutrients tab
    // TASK: maybe add a simple sum first (no percent) #code/progress

    let nutrientEntries = query('.nutrients-entry')

    for( const entry of nutrientEntries )
    {
      const group = entry.dataset.group
      const short = entry.dataset.short
      
      const currentSum = Number( foodEntries.reduce((sum, entry) => sum + Number( entry.nutrients[group]?.[short] ?? 0), 0).toFixed(1))
      entry.dataset.current = currentSum


      let progressBarColor = 'bg-secondary'

      if( currentSum >= entry.dataset.lower && currentSum <= entry.dataset.upper )
        progressBarColor = 'bg-success'
      else
        progressBarColor = 'bg-danger'

      entry.find('.progress-bar').style.width = `${ Math.min( (currentSum / entry.dataset.ideal) * 100, 100)}%` // min: ensure it doesn't exceed 100% for progress
      // entry.find('.progress-label').textContent = `${currentSum} / ${entry.dataset.ideal}`
      entry.find('.percent').textContent = `${Math.round( (currentSum / entry.dataset.ideal) * 100 )}`
      entry.find('.vals').textContent    = `${currentSum} / ${entry.dataset.ideal}`

      entry.find('.progress-bar').classList.remove('bg-secondary', 'bg-success', 'bg-danger')
      entry.find('.progress-bar').classList.add(progressBarColor)

      // Table in modal

      let foodContributions = [];
      for( let i = 0; i < foodEntries.length; i++ )
      {
        const food = foodEntries[i];
        let value = 0;
        
        // Handle nested nutrient structure for amino, vit, min, and fat groups
        if(['amino', 'vit', 'min', 'fat'].includes(group) && food.nutrients[group])
          value = Number(food.nutrients[group][short] ?? 0);
        else
          value = Number(food.nutrients[group]?.[short] ?? 0);
        
        if( value > 0 )
          foodContributions.push({ name: food.food, value: value });
      }

      foodContributions.sort((a, b) => b.value - a.value);

      query('#' + entry.dataset.short + 'Data').innerHTML = 
        '<table class="table table-borderless table-sm mb-2">' +
        (foodContributions.length > 0 
          ? foodContributions.map(item => 
              `<tr>
                <td>${item.name}</td>
                <td class="text-end">${item.value.toFixed(1)} ${entry.dataset.unit}</td>
                <td class="text-end text-muted">(${((item.value / entry.dataset.ideal) * 100).toFixed(1)}%)</td>
              </tr>`
            ).join('')
          : '<tr><td colspan="3" class="text-center text-muted">No contributions</td></tr>'
        ) +
        '</table>';
    }
  }

  #saveDayEntries( uiMsg = false )
  {
    ajax.send('saveDayEntries', { date: this.date, data: query('#dayEntries').value }, function( result, data ) {

      if( result === 'success' && uiMsg)
        query('#uiMsg').innerHTML = 'Saved'
      else if( result !== 'success')
        query('#uiMsg').innerHTML = result.message
    })
  }
}
