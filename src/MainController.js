class MainController
{
  constructor(args)
  {
    const urlParams = new URLSearchParams( window.location.search )
    const dateParam = urlParams.get('date')

    if( dateParam )  // date updated in switchDayBtnClick()
      this.date = dateParam
    else {
      const now = new Date()
      this.date = this.#formatDateLocal( now )  // all entries will be saved to this date (YYYY-MM-DD)
    }

    // Binding

    this.showOverlayInfo         = this.showOverlayInfo.bind(this)
    this.userSelectChange        = this.userSelectChange.bind(this)
    this.switchDayBtnClick       = this.switchDayBtnClick.bind(this)
    this.settingsBtnClick        = this.settingsBtnClick.bind(this)
    this.toggleUnpreciseMode     = this.toggleUnpreciseMode.bind(this)
    this.toggleUnpreciseTimeMode = this.toggleUnpreciseTimeMode.bind(this)
    this.deleteLastLineBtnClick  = this.deleteLastLineBtnClick.bind(this)
    this.saveDayEntriesBtnClick  = this.saveDayEntriesBtnClick.bind(this)
    this.newEntryBtn             = this.newEntryBtn.bind(this)
    this.newEntrySaveBtn         = this.newEntrySaveBtn.bind(this)
    this.layoutItemClick         = this.layoutItemClick.bind(this)
    this.priceColClick           = this.priceColClick.bind(this)
    this.updPriceClick           = this.updPriceClick.bind(this)
    this.offLimitCheckChange     = this.offLimitCheckChange.bind(this)
    this.sportsToggleBtnClick    = this.sportsToggleBtnClick.bind(this)
    // this.#addDayEntry         = this.#addDayEntry.bind(this)     // TASK: can't be done
    this.updSummary              = this.updSummary.bind(this)
    // this.#saveDayEntries      = this.#saveDayEntries.bind(this)
    this.initTabSwipeGestures    = this.initTabSwipeGestures.bind(this)
    this.handleTabSwipe          = this.handleTabSwipe.bind(this)

    let crl = this


    this.initTabSwipeGestures()
    
    // BS init
    
    // Popover

    // <div class="popover popover-cus bs-popover-auto fade show"
    //      role="tooltip" id="popover653960"
    //      style="position: absolute; inset: 0px auto auto 0px; margin: 0px; transform: translate3d(103.2px, 66.4px, 0px);"
    //      data-popper-placement="right"
    // >
    //   <div class="popover-arrow" style="position: absolute; top: 0px; transform: translate3d(0px, 49.6px, 0px);"></div>
    //   <div class="popover-body">These settings are used for calculating the right nutrient amounts</div>
    // </div>

    // Get all elements with data-bs-toggle="popover"
    this.popoverTriggerList = [].slice.call( query('[data-bs-toggle="popover"]'))
    
    // Create popover instances with options
    this.popoverList = this.popoverTriggerList.map( function( popoverTriggerEl) {
      return new bootstrap.Popover( popoverTriggerEl, {
        html: true,
        customClass: 'popover-cus',
        trigger:     'focus',        // Use focus instead of click for better accessibility
        boundary:    'viewport'      // Ensures popover stays in viewport
      })
    })

    // Modals

    this.newEntryModal = new bootstrap.Modal( query('#newEntryModal'))
    this.helpModal     = new bootstrap.Modal( query('#helpModal'))
    
    this.infoModal = new bootstrap.Modal( query('#infoModal'), {
      backdrop: true,
      keyboard: true,
      focus: true
    })

    // global click handler to close popovers when clicking outside
    
    event('click', function(e) {
      if( ! e.target.closest('.popover') && ! e.target.closest('[data-bs-toggle="popover"]')) {
        crl.popoverTriggerList.forEach( function(popoverTriggerEl) {
          const popover = bootstrap.Popover.getInstance(popoverTriggerEl)
          if( popover ) popover.hide()
        })
      }
    })
    
    // info modal event handler (used for groups and food)
    
    const infoModal = query('#infoModal')
    event('show.bs.modal', event => {
      if( event.target.id != 'infoModal')
        return
        
      const btn = event.relatedTarget
      
      // Set modal title
      if( btn.getAttribute('data-title').startsWith('#'))
        infoModal.find('.modal-title').innerHTML = query( btn.getAttribute('data-title')).innerHTML
      else
        infoModal.find('.modal-title').innerHTML = btn.getAttribute('data-title')
      
      // Set modal body content
      infoModal.find('.modal-body').innerHTML = query( btn.getAttribute('data-source')).innerHTML
      
      // Reinitialize popovers inside the modal
      this.initModalPopovers(infoModal)
    })
    
    // new entry modal event handler
    
    // New entry modal - use the modal instance directly for better event handling
    this.newEntryModal._element.event('show.bs.modal', event => {
      // Reset form fields with consistent formatting
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
      
      // Focus the first input for better UX
      setTimeout(() => query('#modalNameInput').focus(), 500)
    })
    
    // Mermaid  // TASK: problems in modal (works in page)
    //
    // mermaid.initialize({  // maybe unneeded
    //   securityLevel: 'loose',
    // })

    // Help modal with Mermaid diagrams
    this.helpModal._element.event('shown.bs.modal', event => {
      // Initialize all Mermaid diagrams in the modal
      query('#helpModal .mermaid').forEach( diagr => {
        mermaid.init( undefined, diagr)
      })
      
      // Initialize any popovers inside the help modal
      this.initModalPopovers(query('#helpModal'))
    })

    // Sortable (advanced)  #code/advancedDayEntries

    // $('#dayEntries').sortable({
    //   handle:      '.handle',
    //   placeholder: 'ui-state-highlight'
    // })
    //    
    // $('#dayEntries').disableSelection()
  }

  initModalPopovers(modalElement)
  {
    // Find all popover triggers inside the modal
    const popoverTriggers = modalElement.find('[data-bs-toggle="popover"]')
    
    // Create new popover instances for each trigger
    if(popoverTriggers.length) {
      const modalPopovers = [].slice.call(popoverTriggers).map( function(popoverTriggerEl) {
        return new bootstrap.Popover( popoverTriggerEl, {
          html: true,
          customClass: 'popover-cus',
          container:   'body',   // This is important - attach to body to avoid z-index issues
          trigger:     'focus',
          boundary:    'viewport'
        })
      })
      
      // Add these to the main popover list for global management
      this.popoverList = [...this.popoverList, ...modalPopovers]
    }
    
    // Initialize any info popovers with specific container
    const infoPopovers = modalElement.find('.info-popover')
    if( infoPopovers.length ) {
      [].slice.call(infoPopovers).forEach(element => {
        new bootstrap.Popover(element, {
          container:   'body',  // attach to body to avoid z-index issues
          html:        true,
          customClass: 'popover-cus'
        })
      })
    }
  }

  /**
   * Show an overlay info tooltip
   * This is a wrapper around the showOverlayInfo function from overlay_MOV.js
   * 
   * @param {Event} event - The click event
   */
  showOverlayInfo(event)
  {
    // TASK: popover in food modal (ingredients?)

    return showOverlayInfo(event, {
      tooltipId:    'info-tooltip',
      position:     'auto',
      closeOnClick: true
    })
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

  switchDayBtnClick(event)  // see also construct
  {
    // Refresh today's date safely using local time
    this.date = this.#formatDateLocal( new Date() )  // update (YYYY-MM-DD) in case behind midnight

    // Make dataset selection robust against clicks on child elements
    const sel = event.currentTarget?.dataset?.sel || event.target.closest('button')?.dataset?.sel

    if( sel === 'current')
    {
      const [y, m, d] = this.date.split('-').map(Number)
      let currentDate = new Date( y, m - 1, d )
      currentDate.setDate( currentDate.getDate() - 1 )
      window.location.href = `?date=${ this.#formatDateLocal(currentDate) }`
    }
    else if( sel === 'last')
    {
      const [y, m, d] = this.date.split('-').map(Number)
      let currentDate = new Date( y, m - 1, d )
      currentDate.setDate( currentDate.getDate() + 1 )
      window.location.href = `?date=${ this.#formatDateLocal(currentDate) }`
    }
    else if( sel === 'next')
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

  toggleUnpreciseMode(event)
  {
    event.preventDefault()
    
    const btn = query('#unpreciseToggleBtn')
    const icon = btn.querySelector('i')
    const isCurrentlyOn = icon.classList.contains('text-warning')
    const newState = !isCurrentlyOn
    
    // Toggle button appearance
    if( isCurrentlyOn )
    {
      // Turn off: secondary warning icon
      icon.className = 'bi bi-exclamation-circle-fill text-secondary'
    }
    else
    {
      // Turn on: warning circle icon
      icon.className = 'bi bi-exclamation-circle-fill text-warning'
    }
    
    // Update the data file header via AJAX
    ajax.send('updateUnpreciseHeader', { date: this.date, unprecise: newState }, function(result, data) {
      if( result !== 'success' )
      {
        console.error('Failed to update unprecise header:', data.message || 'Unknown error')
        // Revert button state on error
        const btn = query('#unpreciseToggleBtn')
        const icon = btn.querySelector('i')
        if( newState )
          icon.className = 'bi bi-exclamation-circle-fill text-secondary'
        else
          icon.className = 'bi bi-exclamation-circle-fill text-warning'
      }
    })
  }

  toggleUnpreciseTimeMode(event)
  {
    event.preventDefault()
    
    const btn = query('#unpreciseTimeToggleBtn')
    const icon = btn.querySelector('i')
    const isCurrentlyOn = icon.classList.contains('text-warning')
    const newState = !isCurrentlyOn
    
    // Toggle button appearance
    if( isCurrentlyOn )
    {
      // Turn off: secondary stopwatch icon
      icon.className = 'bi bi-stopwatch-fill text-secondary'
    }
    else
    {
      // Turn on: warning stopwatch icon
      icon.className = 'bi bi-stopwatch-fill text-warning'
    }
    
    // Update the data file header via AJAX
    ajax.send('updateUnpreciseTimeHeader', { date: this.date, unpreciseTime: newState }, function(result, data) {
      if( result !== 'success' )
      {
        console.error('Failed to update unprecise time header:', data.message || 'Unknown error')
        // Revert button state on error
        const btn = query('#unpreciseTimeToggleBtn')
        const icon = btn.querySelector('i')
        if( newState )
          icon.className = 'bi bi-stopwatch-fill text-secondary'
        else
          icon.className = 'bi bi-stopwatch-fill text-warning'
      }
    })
  }

  deleteLastLineBtnClick(event)
  {
    event.preventDefault()
    
    const textarea = query('#dayEntries')
    const text = textarea.value
    
    if( ! text.trim())
      return
    
    const lines = text.trim().split('\n')
    lines.pop()  // remove the last line
    
    textarea.value = lines.join('\n')
    
    // this.#saveDayEntries( true )  // currently save manually
  }

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
      type:     target.dataset.category || 'F',  // Use category from data attribute (F=Food, S=Supplement)
      food:     food,  // TASK: rename
      calories: calories,
      fat:      nutritionalValues.fat,
      carbs:    nutritionalValues.carbs,
      amino:    nutritionalValues.amino,
      salt:     nutritionalValues.salt,
      price:    price,
      xTimeLog: target.dataset.xTimeLog === 'true',
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
    
    // Check if clicking on deal badge
    if( event.target.classList.contains('badge') || event.target.closest('.badge'))
    {
      target.find('.price-label-view').style.display = 'none'
      target.find('.deal-price-input-view').style.display = 'block'
    }
    else
    {
      target.find('.price-label-view').style.display = 'none'
      target.find('.price-input-view').style.display = 'block'
    }
  }


  updPriceClick(event)
  {
    let name      = event.target.dataset.name
    let priceType = event.target.dataset.priceType
    let priceCol  = event.target.closest('.price-col')
    let value     = ''
    
    if( priceType === 'price' )
      value = priceCol.find('.price-inp').textContent.trim()
    else
      value = priceCol.find('.deal-price-inp').textContent.trim()

    ajax.send('savePrice', { name: name, priceType: priceType, value: value }, function(result, data) {
      if( result === 'success' )
        window.location.reload()       // TASK: maybe show the label again
      else 
        alert(data.message || 'Error updating price')
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


  sportsToggleBtnClick(event)
  {
    event.target.classList.toggle('active')
    
    // TASK: additional functionality can be implemented here later
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

    // Entries that shouldn't count for eating time
    if( entry.xTimeLog === true )
      entry.time = "--:--:--"
    else {
      let now = new Date()
      entry.time = now.toTimeString().split(' ')[0]  // .replaceAll(':', '')  // gives HHMMSS format
    }

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
    const foodEntries = dayEntries.filter( entry => entry.type === 'F' || entry.type === 'FE' || entry.type === 'S')

    if( foodEntries.length == 0)
      return

    // Quick summary

    // let caloriesSum = Number( foodEntries.reduce((sum, entry) => sum + Number(entry.calories), 0).toFixed(1))  // one decimal place
    query('#caloriesSum').textContent = Math.round( foodEntries.reduce((sum, entry) => sum + Number(entry.calories), 0))

    // eating time - filter out entries with xTimeLog
    const timeLogEntries = foodEntries.filter( entry => ! entry.xTimeLog && entry.time !== "--:--:--")
  
    if( timeLogEntries.length >= 2 ) {

      const [hours1, minutes1, seconds1] = timeLogEntries[0].time.split(':').map(Number)
      const [hours2, minutes2, seconds2] = timeLogEntries[timeLogEntries.length - 1].time.split(':').map(Number)

      let diffSeconds = (hours2 * 3600 + minutes2 * 60 + seconds2) - (hours1 * 3600 + minutes1 * 60 + seconds1)
      if( diffSeconds < 0 )  diffSeconds += 24 * 3600

      const hours = Math.floor(diffSeconds / 3600)
      const mins  = Math.floor((diffSeconds % 3600) / 60)  // TASK: use classes and single id for the view
      
      query('#timeSum').textContent = `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`
    }
    else  // if there are fewer than 2 entries with time logging, display 00:00
      query('#timeSum').textContent = "00:00"

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

      let foodContributions = []

      for( let i = 0; i < foodEntries.length; i++ )
      {
        const food = foodEntries[i]
        let value = 0
        
        // Handle nested nutrient structure for amino, vit, min, and fat groups
        if(['amino', 'vit', 'min', 'fat'].includes(group) && food.nutrients[group])
          value = Number(food.nutrients[group][short] ?? 0)
        else
          value = Number(food.nutrients[group]?.[short] ?? 0)
        
        if( value > 0 )
          foodContributions.push({ name: food.food, value: value })
      }

      foodContributions.sort((a, b) => b.value - a.value)

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

  /**
   * Initialize swipe gestures for tab navigation
   * This adds touch and mouse event listeners to tab content areas to allow swiping between tabs
   * Works on both mobile touch devices and PC touchpads
   */
  initTabSwipeGestures()
  {
    // Find the tab content container instead of individual panes
    const tabContent = query('#layout .tab-content')
    if( ! tabContent.length ) {
      console.log('No tab content found')
      return
    }
    
    // Get all tab links for later use
    // this.tabLinks = query('#layout .nav-pills .nav-link')
    // Fix: exclude btn
    // Get all tab links for later use, excluding the new entry button (which has ms-auto on its parent)
    this.tabLinks = Array.from( query('#layout .nav-pills .nav-link')).filter( link => {
      // Exclude the new entry button which is inside a nav-item with ms-auto class
      return ! link.closest('.nav-item.ms-auto');
    })
    
    if( ! this.tabLinks.length ) {
      console.log('No tab links found')
      return
    }
        
    // Variables to track events
    let startX = 0
    let startY = 0
    let isMouseDown = false
    const minSwipeDistance = 100     // minimum distance required for a swipe (increased from 50)
    const maxVerticalDistance = 50   // maximum vertical movement allowed for horizontal swipe (reduced from 100)
    
    // Add touch events to the tab content container (for mobile devices)
    tabContent[0].addEventListener('touchstart', e => {
      
      startX = e.changedTouches[0].screenX
      startY = e.changedTouches[0].screenY
    }, { passive: true })
    
    tabContent[0].addEventListener('touchend', e => {
      
      const endX = e.changedTouches[0].screenX
      const endY = e.changedTouches[0].screenY
      
      // Calculate vertical distance to ensure it's a horizontal swipe
      const verticalDistance   = Math.abs(endY - startY)
      const horizontalDistance = Math.abs(endX - startX)
            
      // Only process horizontal swipes (not vertical scrolling)
      if( horizontalDistance >= minSwipeDistance && verticalDistance <= maxVerticalDistance ) {
        this.handleTabSwipe(startX, endX, minSwipeDistance)
      }
    }, { passive: true })

    // Mouse events (for PC touchpads)
    tabContent[0].addEventListener('mousedown', e => {
      isMouseDown = true
      startX = e.clientX
      startY = e.clientY
    })
    
    tabContent[0].addEventListener('mouseup', e => {
    
      if( isMouseDown ) {
        const endX = e.clientX
        const endY = e.clientY
        
        // Calculate distances
        const verticalDistance   = Math.abs(endY - startY)
        const horizontalDistance = Math.abs(endX - startX)
                
        // Only process horizontal swipes (not vertical scrolling)
        if( horizontalDistance >= minSwipeDistance && verticalDistance <= maxVerticalDistance )
          this.handleTabSwipe(startX, endX, minSwipeDistance)
        
        isMouseDown = false
      }
    })
    
    // Reset mouse down state if mouse leaves the element
    tabContent[0].addEventListener('mouseleave', () => {
      isMouseDown = false
    })
  }
  
  /**
   * Handle tab swipe gesture
   * @param {number} startX - Starting X position of touch
   * @param {number} endX - Ending X position of touch
   * @param {number} minDistance - Minimum distance required for a swipe
   */
  handleTabSwipe(startX, endX, minDistance)
  {
    // Calculate swipe distance
    const swipeDistance = endX - startX
    
    // If swipe distance is less than minimum, ignore
    if( Math.abs(swipeDistance) < minDistance ) return
    
    // Find the active tab link
    const activeTabLink = query('#layout .nav-pills .nav-link.active')[0]
    if( ! activeTabLink ) return
    
    // Find the index of the active tab
    // const activeIndex = Array.from( this.tabLinks ).findIndex( link => link === activeTabLink)
    // Fix: exclude btn
    // Make sure the active tab is one of our filtered tabs (no the new entry button)
    if( ! this.tabLinks.includes(activeTabLink) ) return
    
    // Find the index of the active tab in our filtered list
    const activeIndex = this.tabLinks.indexOf(activeTabLink)
    // (end fix)

    if( activeIndex === -1 )  return
    
    // Determine which tab to show based on swipe direction
    let targetIndex
    
    if( swipeDistance > 0 ) {
      // Swipe right - show previous tab
      targetIndex = activeIndex - 1
      if( targetIndex < 0 ) targetIndex = this.tabLinks.length - 1  // wrap to last tab
    }
    else {
      // Swipe left - show next tab
      targetIndex = activeIndex + 1
      if( targetIndex >= this.tabLinks.length ) targetIndex = 0     // wrap to first tab
    }
    
    // Click the target tab link to activate it
    if( this.tabLinks[targetIndex] )
      this.tabLinks[targetIndex].click()
  }

  // Helper: format a Date to local YYYY-MM-DD
  #formatDateLocal(dateObj) /*@*/
  {
    const y = dateObj.getFullYear()
    const m = String( dateObj.getMonth() + 1 ).padStart(2, '0')
    const d = String( dateObj.getDate()      ).padStart(2, '0')
    return `${y}-${m}-${d}`
  }
}
