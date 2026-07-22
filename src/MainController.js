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
    this.toggleUnpreciseMode     = this.toggleUnpreciseMode.bind(this)
    this.toggleUnpreciseTimeMode = this.toggleUnpreciseTimeMode.bind(this)
    this.deleteLastLineBtnClick  = this.deleteLastLineBtnClick.bind(this)
    this.deleteEntryBtnClick     = this.deleteEntryBtnClick.bind(this)
    this.saveDayEntriesBtnClick  = this.saveDayEntriesBtnClick.bind(this)
    this.newEntryBtn             = this.newEntryBtn.bind(this)
    this.newEntrySaveBtn         = this.newEntrySaveBtn.bind(this)
    this.importShowBtn           = this.importShowBtn.bind(this)
    this.importBackBtn           = this.importBackBtn.bind(this)
    this.importRunBtn            = this.importRunBtn.bind(this)
    this.openSearch              = this.openSearch.bind(this)
    this.runSearch               = this.runSearch.bind(this)
    this.searchResultClick       = this.searchResultClick.bind(this)
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

    this.infoModal = new bootstrap.Modal( query('#infoModal'), {
      backdrop: true,
      keyboard: true,
      focus: true
    })

    this.confirmModal = new bootstrap.Modal( query('#confirmModal'))

    // Food search dialog (find a food across all grid tabs and jump to it)

    this.searchModal = new bootstrap.Modal( query('#searchModal'))

    this.searchModal._element.event('show.bs.modal', () => {
      query('#searchInput').value      = ''
      query('#searchResults').innerHTML = ''
    })

    // Focus on shown (not show): Bootstrap sets its own focus when the transition
    // completes, so focusing earlier gets stolen back
    this.searchModal._element.event('shown.bs.modal', () => query('#searchInput').focus())

    const searchInput = query('#searchInput')

    if( searchInput )
      searchInput.event('keydown', e => {
        if( e.key === 'Enter') {
          e.preventDefault()
          this.runSearch()
        }
        else if( e.key === 'ArrowDown') {
          const first = query('#searchResults .search-result')[0]
          if( first ) {
            e.preventDefault()
            first.focus()
          }
        }
      })

    const searchResults = query('#searchResults')

    if( searchResults )
      searchResults.event('keydown', e => {
        if( e.key !== 'ArrowDown' && e.key !== 'ArrowUp')
          return

        e.preventDefault()

        const results = Array.from( query('#searchResults .search-result'))
        const at      = results.indexOf( document.activeElement )

        if( e.key === 'ArrowDown') {
          if( at < results.length - 1 ) results[at + 1].focus()
        }
        else {  // ArrowUp: past the first result returns focus to the input
          if( at <= 0 ) query('#searchInput').focus()
          else          results[at - 1].focus()
        }
      })

    // Ctrl/Cmd+K opens the food search (not with Alt)

    event('keydown', e => {
      if( (e.ctrlKey || e.metaKey) && ! e.altKey && (e.key === 'k' || e.key === 'K')) {
        e.preventDefault()
        this.openSearch()
      }
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
      
      // Set modal body content with markdown rendering
      let content = query( btn.getAttribute('data-source')).innerHTML
      
      // Check if button has info-btn class (group headers) to enable markdown
      if( btn.classList.contains('info-btn'))
        content = this.renderMarkdown(content)
      
      infoModal.find('.modal-body').innerHTML = content
      
      // Reinitialize popovers inside the modal
      this.initModalPopovers(infoModal)
    })
    
    // new entry modal event handler
    
    // New entry modal - use the modal instance directly for better event handling
    this.newEntryModal._element.event('show.bs.modal', event => {
      // Reset form fields with consistent formatting
      query('#modalNameInput').value     = 'Misc entry'  // default
      query('#modalWeightInput').value   = ''
      query('#modalWeightUnit').value    = 'g'
      query('#modalPiecesInput').value   = ''
      query('#modalUsedSelect').value    = 'null'
      query('#modalUsedAmountsSelect').value = ''
      query('#modalCaloriesInput').value = ''
      query('#modalFatInput').value      = ''
      query('#modalSatFatInput').value   = ''
      query('#modalCarbsInput').value    = ''
      query('#modalSugarInput').value    = ''
      query('#modalFibreInput').value    = ''
      query('#modalAminoInput').value    = ''
      query('#modalSaltInput').value     = ''
      query('#modalPriceInput').value    = ''
      query('#modalDealPriceInput').value = ''

      // Details tab fields
      query('#modalProductNameInput').value = ''
      query('#modalUrlInput').value         = ''
      query('#modalAcceptableSelect').value = ''
      query('#modalNutriScoreInput').value  = ''
      query('#modalVeganCheck').checked     = false
      query('#modalBioCheck').checked       = false
      query('#modalIngredientsInput').value = ''
      query('#modalAllergyInput').value     = ''
      query('#modalMayContainInput').value  = ''
      // Prefill the packaging template so the user edits it down before saving
      query('#modalPackagingInput').value   = 'none|cardboard,alu,plastic,glass & rubber (maybe)'

      // Precise grid-amount labels reflect the current weight unit
      this.#updateGridAmountUnits()

      // Back to the Entry tab
      bootstrap.Tab.getOrCreateInstance( query('#entryTab')).show()

      // Clear any lingering save-validation hint
      this.#clearSaveError()

      // Reset import state (dev feature)
      this.importedFood = null
      this.#showImportPanel( false )
      const saveNewFood = query('#saveNewFood')
      if( saveNewFood )  saveNewFood.checked = false

      // Focus the first input for better UX
      setTimeout(() => query('#modalNameInput').focus(), 500)
    })

    // Keep precise grid-amount labels in sync with the weight unit

    query('#modalWeightUnit').event('change', () => this.#updateGridAmountUnits())
    
    // Mermaid  // TASK: problems in modal (works in page)
    //
    // mermaid.initialize({  // maybe unneeded
    //   securityLevel: 'loose',
    // })

    // Sortable day entries  #code/advancedDayEntries
    // Drag to reorder (mouse) or press-and-hold to reorder (touch). The list is the
    // source of truth: after a drop we rebuild the dayEntries array from the DOM.

    const dayEntriesList = query('#dayEntriesList')

    if( dayEntriesList )
      this.dayEntriesSortable = new PointerSortable( dayEntriesList, {
        itemSelector: '.day-entry',
        cancel:       '.day-entry-del',   // don't start a drag from the delete button
        onSort: () => {
          this.#syncDayEntriesFromDom()
          this.updSummary()
          this.#saveDayEntries()
        },
        onTap: item => this.showEntryInfo( item )   // click (not drag) opens the food info
      })

    // Keep the newest entry in view (unless the user scrolled up on purpose)
    this.#initDayEntriesAutoScroll()
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


  // List: btns
  // Unprecise toggles drive two visuals at once: desktop button (text-warning vs text-secondary)
  // and the mobile dropdown item (.bi-check .invisible flag)

  toggleUnpreciseMode(event)
  {
    event.preventDefault()

    const desktopIcon = query('#unpreciseToggleBtn').querySelector('i')
    const isOn        = desktopIcon.classList.contains('text-warning')
    const newState    = ! isOn

    this.applyUnpreciseUi('nutrients', newState)

    ajax.send('updateUnpreciseHeader', { date: this.date, unprecise: newState }, (result, data) => {
      if( result !== 'success')
      {
        console.error('Failed to update unprecise header:', data.message || 'Unknown error')
        this.applyUnpreciseUi('nutrients', ! newState)   // revert
      }
    })
  }

  toggleUnpreciseTimeMode(event)
  {
    event.preventDefault()

    const desktopIcon = query('#unpreciseTimeToggleBtn').querySelector('i')
    const isOn        = desktopIcon.classList.contains('text-warning')
    const newState    = ! isOn

    this.applyUnpreciseUi('time', newState)

    ajax.send('updateUnpreciseTimeHeader', { date: this.date, unpreciseTime: newState }, (result, data) => {
      if( result !== 'success')
      {
        console.error('Failed to update unprecise time header:', data.message || 'Unknown error')
        this.applyUnpreciseUi('time', ! newState)        // revert
      }
    })
  }

  applyUnpreciseUi( type, on )    // type: 'nutrients' | 'time'
  {
    const isTime    = type === 'time'
    const desktopId = isTime ? 'unpreciseTimeToggleBtn' : 'unpreciseToggleBtn'
    const mobileId  = isTime ? 'toggleTime'             : 'toggleNutrients'
    const iconBase  = isTime ? 'bi-stopwatch-fill'      : 'bi-exclamation-circle-fill'

    const desktopIcon = document.querySelector('#' + desktopId + ' i')
    if( desktopIcon )
      desktopIcon.className = 'bi ' + iconBase + (on ? ' text-warning' : ' text-secondary')

    const mobileCheck = document.querySelector('#' + mobileId + ' i.bi-check')
    if( mobileCheck )
      mobileCheck.classList.toggle('invisible', ! on)

    this.updateUnpreciseDropdownIcon()
  }

  updateUnpreciseDropdownIcon()   // mobile trigger turns orange if any unprecise flag is set
  {
    const trigger = document.querySelector('#unpreciseDropdown i')
    if( ! trigger ) return

    const anyOn = ['#toggleNutrients', '#toggleTime', '#togglePrice'].some( sel => {
      const c = document.querySelector( sel + ' i.bi-check')
      return c && ! c.classList.contains('invisible')
    })
    trigger.style.color = anyOn ? '#fd7e14' : ''   // Bootstrap orange / reset
  }

  deleteLastLineBtnClick(event)
  {
    event.preventDefault()

    const items = query('#dayEntriesList .day-entry')
    if( ! items.length )
      return

    items[items.length - 1].remove()  // remove the last entry

    this.#afterListChange()
  }

  // Per-entry delete (x button) - asks for confirmation first

  deleteEntryBtnClick(event)
  {
    event.preventDefault()

    const li = event.target.closest('.day-entry')
    if( ! li )
      return

    const name = li.dataset.food || 'this entry'

    this.confirm(`Delete "${name}"?`, () => {
      li.remove()
      this.#afterListChange()
    })
  }

  /*@

  Open the food info modal for a day entry (same content as clicking the food
  label in the grid). Reuses the shared #<id>Headline / #<id>Data blocks by
  handing the existing #infoModal show handler a proxy relatedTarget. Does
  nothing when the entry's food has no info block (e.g. a misc entry, or a food
  no longer in the grid).

  */
  showEntryInfo( li ) /*@*/
  {
    const food = li?.dataset?.food
    if( ! food )
      return

    // Mirror the PHP id: lcfirst( alnum-only of the food name)  (see layout/entry.php)
    const alnum   = food.replace(/[^a-zA-Z0-9]/g, '')
    const entryId = alnum.charAt(0).toLowerCase() + alnum.slice(1)

    // Only open when the shared info blocks exist (food present in the grid)
    if( ! document.getElementById( entryId + 'Headline')
    ||  ! document.getElementById( entryId + 'Data'))
      return

    // Proxy element carries the data-* the show.bs.modal handler reads
    const proxy = document.createElement('div')
    proxy.setAttribute('data-title',  '#' + entryId + 'Headline')
    proxy.setAttribute('data-source', '#' + entryId + 'Data')

    this.infoModal.show( proxy )
  }

  // Reusable confirm dialog (see modal/confirm.php)

  confirm( message, onConfirm )
  {
    query('#confirmModalMessage').textContent = message

    // Replace the OK button to drop any previous click handler
    const okBtn = query('#confirmModalOkBtn')
    const fresh = okBtn.cloneNode(true)
    okBtn.parentNode.replaceChild( fresh, okBtn)

    fresh.addEventListener('click', () => {
      this.confirmModal.hide()
      onConfirm()
    })

    this.confirmModal.show()
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

  // Food search dialog
  //
  // Lets the user find a food by name and jump to the tab where it lives (the same
  // food may appear on several grid tabs). Pure in-memory lookup over the already
  // rendered food grid - no server request. Search runs on Enter / the magnifier.

  openSearch(event)
  {
    this.searchModal.show()
  }

  runSearch()
  {
    const q         = (query('#searchInput').value || '').trim().toLowerCase()
    const container = query('#searchResults')

    if( ! q ) {  // empty query clears results and does nothing else
      container.innerHTML = ''
      return
    }

    // One record per (food, tab) occurrence, so each result jumps to a specific tab
    const matches = this.#buildFoodIndex().filter( rec => rec.food.toLowerCase().includes(q))

    this.searchMatches = matches  // referenced by searchResultClick via data-idx

    if( ! matches.length ) {
      container.innerHTML = '<div class="text-secondary text-center p-3">No matches found</div>'
      return
    }

    container.innerHTML = matches.map( (rec, i) => {
      const loc = rec.tabLabel
        ? this.#escapeHtml( rec.tabLabel) + (rec.groupName ? ' <span class="search-sep">›</span> ' + this.#escapeHtml( rec.groupName) : '')
        : this.#escapeHtml( rec.groupName)

      return `<button type="button" class="search-result" data-idx="${i}" onclick="mainCrl.searchResultClick(event)">
                <span class="search-result-name">${this.#highlight( rec.food, q)}</span>
                <span class="search-result-loc">${loc}</span>
              </button>`
    }).join('')
  }

  searchResultClick(event)
  {
    const btn = event.currentTarget
    const rec = (this.searchMatches || [])[parseInt( btn.dataset.idx, 10)]
    if( ! rec )
      return

    // Jump only once the dialog is fully closed, so the target pane is visible
    const jump = () => {
      if( rec.navLink )  rec.navLink.click()  // activate the food-grid tab
      if( rec.itemEl ) {
        rec.itemEl.scrollIntoView({ behavior: 'smooth', block: 'center' })
        this.#flashItem( rec.itemEl )
      }
    }

    query('#searchModal').addEventListener('hidden.bs.modal', () => setTimeout( jump, 50), { once: true })
    this.searchModal.hide()
  }

  // Walk the rendered food grid and return one record per (food, tab) occurrence.
  // The grid is static after load; matching foods and recipes are merged in the DOM.
  #buildFoodIndex() /*@*/
  {
    // Map each grid tab pane id -> its nav label + link (empty when a single-tab layout)
    const tabs = {}

    Array.from( query('#layout .nav-pills .nav-link[data-bs-toggle="tab"]')).forEach( link => {
      const href = link.getAttribute('href')  // e.g. "#mealsLayoutPane"
      if( href )
        tabs[href.slice(1)] = { label: link.textContent.trim(), link }
    })

    const index = []

    Array.from( query('#layout .layout-item')).forEach( item => {
      const btn  = item.querySelector('.amount-btn')
      const name = btn ? btn.dataset.food : (item.querySelector('.text-nowrap')?.textContent.trim() || '')
      if( ! name )
        return

      const pane = item.closest('.tab-pane[id$="LayoutPane"]')
      const tab  = pane && tabs[pane.id] ? tabs[pane.id] : null

      const header    = item.closest('[class*="col-md-6"]')?.querySelector('.group-header div')
      const groupName = header ? header.textContent.trim() : ''

      index.push({
        food:     name,
        itemEl:   item,
        navLink:  tab ? tab.link  : null,
        tabLabel: tab ? tab.label : '',
        groupName
      })
    })

    return index
  }

  // Briefly outline a grid row so the user can spot it after a jump
  #flashItem(el) /*@*/
  {
    el.classList.remove('search-flash')
    void el.offsetWidth  // reflow so the animation can retrigger on repeat jumps
    el.classList.add('search-flash')
    setTimeout(() => el.classList.remove('search-flash'), 1600)
  }

  #escapeHtml(s) /*@*/
  {
    return String(s).replace(/[&<>"']/g, c => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c]))
  }

  // Escape the whole string, wrapping the first case-insensitive match of q in <mark>
  #highlight(text, q) /*@*/
  {
    const at = text.toLowerCase().indexOf(q)
    if( at < 0 )
      return this.#escapeHtml(text)

    return this.#escapeHtml( text.slice(0, at))
         + '<mark>' + this.#escapeHtml( text.slice(at, at + q.length)) + '</mark>'
         + this.#escapeHtml( text.slice(at + q.length))
  }

  newEntrySaveBtn(event)
  {
    const usedSelect  = query('#modalUsedSelect')
    const saveNewFood = query('#saveNewFood')

    const consuming = usedSelect.value !== 'null' && usedSelect.value !== null
    const saving    = saveNewFood && saveNewFood.checked

    // The modal must do at least one thing. When not creating a food record, a
    // "Consumed now" amount is required (otherwise nothing would be saved).

    if( ! saving && ! consuming ) {
      this.#showSaveError('Pick a "Consumed now" amount, or check "Save as new food".')
      return
    }

    this.#clearSaveError()

    // "Consumed now" picked -> log a day entry.

    if( consuming )
      this.#addDayEntry( this.#buildDayEntry( usedSelect ))

    // Dev feature: also persist a new food record, then reload to refresh the grid.
    // Otherwise just close (the day entry is already saved by #addDayEntry).

    if( saving )
      this.#saveNewFood()
    else
      this.newEntryModal.hide()
  }


  // Save-validation hint shown as a red "!" (message in its title attribute)

  #showSaveError( message )
  {
    const el = query('#modalSaveError')
    if( ! el )  return

    el.title = message
    el.classList.remove('d-none')
  }

  #clearSaveError()
  {
    const el = query('#modalSaveError')
    if( el )  el.classList.add('d-none')
  }

  // Build a scaled day entry from the modal form for the picked "consumed now" amount

  #buildDayEntry( usedSelect )
  {
    let weight     = parseFloat( query('#modalWeightInput').value) || 0
    let weightUnit = query('#modalWeightUnit').value
    let usage      = usedSelect.options[usedSelect.selectedIndex].dataset.usage
    let value      = parseFloat( usedSelect.value)
    let pieces     = parseFloat( query('#modalPiecesInput').value) || 1

    // grams/ml consumed for the picked amount

    let usedWeight = usage === 'pack'   ? weight * value : (
                     usage === 'pieces' ? (weight / pieces) * value
                   : value  // precise: value is already grams/ml
    )

    const num = sel => parseFloat( query(sel).value.trim().replace(',', '.')) || 0

    let entry = {
      type:      'F',
      food:      query('#modalNameInput').value,  // TASK: rename
      // *10 /10 rounds to one decimal place
      calories:  Math.round( num('#modalCaloriesInput') * (usedWeight / 100) * 10) / 10,
      fat:       Math.round( num('#modalFatInput')      * (usedWeight / 100) * 10) / 10,
      carbs:     Math.round( num('#modalCarbsInput')    * (usedWeight / 100) * 10) / 10,
      amino:     Math.round( num('#modalAminoInput')    * (usedWeight / 100) * 10) / 10,
      salt:      Math.round( num('#modalSaltInput')     * (usedWeight / 100) * 10) / 10,
      price:     weight ? Math.round( num('#modalPriceInput') * (usedWeight / weight) * 100) / 100 : 0,
      // amount.label is shown in the day-entries list; weight (grams) is kept for later use
      nutrients: {
        amount: { label: this.#amountLabel( usage, usedSelect, weightUnit), weight: usedWeight }
      }
    }

    // Fibre lives inside nutrients (that's where the day summary sums it from)

    let fibreInp = query('#modalFibreInput')

    if( fibreInp && fibreInp.value.trim() !== '')
      entry.nutrients.fibre = Math.round( num('#modalFibreInput') * (usedWeight / 100) * 10) / 10

    return entry
  }

  // Human-readable amount label for the day-entries list

  #amountLabel( usage, usedSelect, weightUnit )
  {
    if( usage === 'precise' )
      return usedSelect.value + weightUnit  // e.g. "50g" / "100ml"

    return usedSelect.options[usedSelect.selectedIndex].textContent.trim()  // "1/4", "2 pc"
  }

  // Show the precise grid-amount options in the current weight unit (g/ml)

  #updateGridAmountUnits()
  {
    const unit = query('#modalWeightUnit').value

    query('#modalPreciseAmounts').querySelectorAll('option').forEach( o =>
      o.textContent = o.value.split(',').map( v => v + unit ).join(' / ')
    )
  }


  // Import a food from a product page (dev feature)
  //
  // Flow: Import button -> import panel (URL or pasted HTML) -> importFood ajax
  // fills the form and checks "Save as new food" -> Add entry persists the food
  // via saveFood and reloads so the new food shows up in the grid.

  importShowBtn(event)
  {
    this.#showImportPanel( true )
  }

  importBackBtn(event)
  {
    this.#showImportPanel( false )
  }

  importRunBtn(event)
  {
    const url  = query('#importUrlInput').value.trim()
    const html = query('#importHtmlInput').value
    const msg  = query('#importMsg')
    const btn  = query('#importRunBtn')

    msg.textContent = ''

    if( url === '' && html.trim() === '') {
      msg.textContent = 'Enter a URL or paste page HTML.'
      return
    }

    btn.disabled    = true
    btn.textContent = 'Importing …'

    ajax.send('importFood', { url: url, html: html }, (result, data) => {

      btn.disabled    = false
      btn.textContent = 'Import'

      if( result !== 'success') {
        msg.textContent = (data && data.message) || 'Import failed'
        return
      }

      this.#fillFormFromFood( data.food )
      this.#showImportPanel( false )

      const chk = query('#saveNewFood')
      if( chk )  chk.checked = true
    })
  }

  #showImportPanel( show )
  {
    const panel = query('#newEntryImportPanel')

    if( ! panel )  return   // devMode off: import UI is not rendered

    panel.classList.toggle('d-none', ! show)
    query('#newEntryFormPanel').classList.toggle('d-none', show)

    const footer = query('#newEntryFooter')
    if( footer )  footer.classList.toggle('d-none', show)
  }

  #fillFormFromFood( food )
  {
    this.importedFood = food

    const nv  = food.nutritionalValues || {}
    const set = ( sel, val ) => { const el = query(sel); if( el && val != null )  el.value = val }

    set('#modalNameInput', food.name)

    // Weight comes as "800g" / "330ml" / "0,75l": fill the number and unit.
    // Only g/ml are offered, so litres are converted to ml.

    const wm = String( food.weight || '').match(/([\d.,]+)\s*([a-zA-Z]*)/)

    if( wm ) {
      let num  = parseFloat( wm[1].replace(',', '.'))
      let unit = wm[2].toLowerCase()

      if( unit === 'l' )       { num *= 1000; unit = 'ml' }   // litres -> ml
      else if( unit !== 'ml')  { unit = 'g' }                 // default to grams

      query('#modalWeightInput').value = num
      query('#modalWeightUnit').value  = unit
    }

    set('#modalPiecesInput',   food.pieces)
    set('#modalCaloriesInput', food.calories)
    set('#modalFatInput',      nv.fat)
    set('#modalSatFatInput',   nv.saturatedFat)
    set('#modalCarbsInput',    nv.carbs)
    set('#modalSugarInput',    nv.sugar)
    set('#modalFibreInput',    nv.fibre)
    set('#modalAminoInput',    nv.amino)
    set('#modalSaltInput',     nv.salt)
    set('#modalPriceInput',    food.price)
    set('#modalDealPriceInput', food.dealPrice)

    // Details tab
    const certs = food.certificates || {}
    set('#modalProductNameInput', food.productName)
    set('#modalUrlInput',         food.url)
    set('#modalAcceptableSelect', food.acceptable)
    set('#modalNutriScoreInput',  certs.NutriScore)
    query('#modalVeganCheck').checked = certs.vegan === true
    query('#modalBioCheck').checked   = certs.bio === true
    set('#modalIngredientsInput', food.ingredients)
    set('#modalAllergyInput',     food.allergy)
    set('#modalMayContainInput',  food.mayContain)
    set('#modalPackagingInput',   food.packaging)
  }

  // Build a food payload from the form (over the imported base) and persist it

  #saveNewFood()
  {
    const num = sel => {
      const el = query(sel)
      const v  = el ? el.value.trim().replace(',', '.') : ''
      return v === '' ? null : parseFloat(v)
    }

    const base      = this.importedFood || {}
    const unit      = query('#modalWeightUnit').value
    const weightVal = query('#modalWeightInput').value.trim()

    // Nutrients shown in the form override the imported ones; hidden ones (e.g.
    // saturatedFat) are kept from the imported payload

    const nutrients = Object.assign({}, base.nutritionalValues || {})

    for( const [key, sel] of Object.entries({
      fat: '#modalFatInput', saturatedFat: '#modalSatFatInput', carbs: '#modalCarbsInput',
      sugar: '#modalSugarInput', fibre: '#modalFibreInput', amino: '#modalAminoInput', salt: '#modalSaltInput'
    })) {
      const v = num(sel)
      if( v != null )  nutrients[key] = v
    }

    // Certificates from the Details tab, merged over imported ones so extra keys
    // (oekotest, fairtrade, …) from the payload are preserved

    const certs = Object.assign({}, base.certificates || {})

    const nutriScore = query('#modalNutriScoreInput').value.trim().toUpperCase()
    if( nutriScore )  certs.NutriScore = nutriScore
    else              delete certs.NutriScore

    query('#modalVeganCheck').checked ? certs.vegan = true : delete certs.vegan
    query('#modalBioCheck').checked   ? certs.bio   = true : delete certs.bio

    const text = sel => { const v = query(sel).value.trim(); return v === '' ? null : v }

    // Typical grid amounts (usedAmounts); precise combinations take the weight unit

    const amtSel = query('#modalUsedAmountsSelect')
    const amtOpt = amtSel.options[amtSel.selectedIndex]
    let   usedAmounts = []

    if( amtSel.value )
      usedAmounts = amtSel.value.split(',').map( v => amtOpt.dataset.type === 'precise' ? v + unit : v )

    const food = Object.assign({}, base, {
      name:              query('#modalNameInput').value.trim(),
      weight:            weightVal === '' ? (base.weight || '') : weightVal + unit,
      pieces:            num('#modalPiecesInput'),
      usedAmounts:       usedAmounts,
      price:             num('#modalPriceInput') ?? base.price ?? null,
      dealPrice:         num('#modalDealPriceInput') ?? base.dealPrice ?? null,
      calories:          num('#modalCaloriesInput'),
      nutritionalValues: nutrients,
      // Details tab
      productName:       text('#modalProductNameInput') ?? base.productName ?? '',
      url:               text('#modalUrlInput') ?? base.url ?? '',
      acceptable:        query('#modalAcceptableSelect').value,
      certificates:      certs,
      ingredients:       text('#modalIngredientsInput') ?? '',
      allergy:           text('#modalAllergyInput') ?? '',
      mayContain:        text('#modalMayContainInput') ?? '',
      packaging:         text('#modalPackagingInput') ?? ''
    })

    ajax.send('saveFood', { food: food }, ( result, data ) => {

      if( result === 'success') {
        this.newEntryModal.hide()
        window.location.reload()
      }
      else
        this.#showSaveError( (data && data.message) || 'Could not save food')
    })
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
      type:     target.dataset.category || 'F',  // use category from data attribute (F=Food, S=Supplement)
      food:     food,  // TASK: rename
      calories: calories,
      fat:      nutritionalValues.fat,
      carbs:    nutritionalValues.carbs,
      amino:    nutritionalValues.amino,
      salt:     nutritionalValues.salt,
      price:    price,
      xTimeLog: target.dataset.xTimeLog === 'true',
      nutrients: {
        // amount kept first in the json portion: label is shown in the day entries,
        // weight (grams) is the calculated amount kept for later use
        amount: { label: target.dataset.amountLabel, weight: parseFloat( target.dataset.amountWeight) || 0 },
        fibre: JSON.parse( nutritionalValues.fibre || 0 ),  // TASK: or only add when set (see updSummary() for sum only if available)
        fat:   JSON.parse( target.dataset.fattyacids ),
        amino: JSON.parse( target.dataset.aminoacids ),
        vit:   JSON.parse( target.dataset.vitamins ),
        min:   JSON.parse( target.dataset.minerals ),
        sec:   JSON.parse( target.dataset.secondary ),
        misc:  JSON.parse( target.dataset.misc )
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

  Add a new entry: append a list item, then resync from the DOM and save.

  */
  #addDayEntry( entry ) /*@*/
  {
    // TASK: (advanced) time on server (currently a problem cause we still use save btn))
    //       user needs a timezone setting if done on server

    // Set normal time for all new entries (compatibility: old entries may still use "--:--:--")
    let now = new Date()
    entry.time = now.toTimeString().split(' ')[0]  // .replaceAll(':', '')  // gives HHMMSS format

    // TASK: add types for user > misc
    // if type === MiscBuyable
    // if type === Food

    query('#dayEntriesList').appendChild( this.#createEntryEl( entry))

    this.#afterListChange()

    // Reveal the just-added entry, unless the user scrolled up to review older ones
    if( this.autoScrollDayEntries )
      this.#scrollDayEntriesToBottom()
  }

  /*@

  Run after any list mutation (add / delete / reorder): rebuild the dayEntries
  array from the DOM (the list is the source of truth), refresh the summary and save.

  */
  #afterListChange() /*@*/
  {
    this.#syncDayEntriesFromDom()
    this.#updateEmptyHint()
    this.updSummary()
    this.#saveDayEntries()
  }

  /*@

  Day entries auto-scroll: once the list grows past the visible area we keep the
  newest entry in view when entries are added. If the user scrolls up we assume
  it's intentional and stop following; when they scroll back down to the last
  entry we resume following on the next add.

  */
  #initDayEntriesAutoScroll() /*@*/
  {
    this.dayEntriesScroller = queryOne('.day-entries-section')
    if( ! this.dayEntriesScroller ) return

    this.autoScrollDayEntries = true   // follow newest until the user scrolls up

    this.dayEntriesScroller.addEventListener('scroll', () => {
      this.autoScrollDayEntries = this.#dayEntriesAtBottom()
    }, { passive: true })

    // Start at the newest entry
    this.#scrollDayEntriesToBottom()
  }

  // True when the list is scrolled to (or within a couple px of) the bottom
  #dayEntriesAtBottom() /*@*/
  {
    const el = this.dayEntriesScroller
    if( ! el ) return true
    return el.scrollHeight - el.scrollTop - el.clientHeight < 4
  }

  #scrollDayEntriesToBottom() /*@*/
  {
    const el = this.dayEntriesScroller
    if( ! el ) return
    el.scrollTop = el.scrollHeight
  }

  // Build one list item from an entry object (mirrors view/main/edit/day_entries.php)

  #createEntryEl( entry )
  {
    const li = document.createElement('li')
    li.className = 'day-entry list-group-item d-flex align-items-center px-2 py-1'

    li.dataset.type      = entry.type
    li.dataset.food      = entry.food
    li.dataset.time      = entry.time
    li.dataset.calories  = entry.calories
    li.dataset.fat       = entry.fat
    li.dataset.carbs     = entry.carbs
    li.dataset.amino     = entry.amino
    li.dataset.salt      = entry.salt
    li.dataset.price     = entry.price
    li.dataset.nutrients = JSON.stringify( entry.nutrients || {})

    const timeDisp   = String( entry.time || '').slice(0, 5)
    const amountDisp = entry.nutrients?.amount?.label ?? ''

    li.innerHTML =
      `<span class="day-entry-type">${ this.#esc(entry.type) }</span>`
      + `<div class="day-entry-main flex-grow-1 ms-2 overflow-hidden">`
      +   `<div class="day-entry-name text-truncate">${ this.#esc(entry.food) }</div>`
      +   `<div class="day-entry-sub small text-secondary d-flex">`
      +     `<span class="day-entry-time">${ this.#esc(timeDisp) }</span>`
      +     `<span class="day-entry-amount">${ this.#esc(amountDisp) }</span>`
      +   `</div>`
      + `</div>`
      + `<button type="button" onclick="mainCrl.deleteEntryBtnClick(event)" class="day-entry-del btn p-1 border-0 bg-transparent text-secondary" aria-label="Delete entry">`
      +   `<i class="bi bi-x-lg"></i>`
      + `</button>`

    return li
  }

  // Rebuild the global dayEntries array from the list items (DOM order = data order)

  #syncDayEntriesFromDom()
  {
    dayEntries = Array.from( query('#dayEntriesList .day-entry')).map( li => ({
      time:      li.dataset.time,
      type:      li.dataset.type,
      food:      li.dataset.food,
      calories:  li.dataset.calories,
      fat:       li.dataset.fat,
      carbs:     li.dataset.carbs,
      amino:     li.dataset.amino,
      salt:      li.dataset.salt,
      price:     li.dataset.price,
      nutrients: JSON.parse( li.dataset.nutrients || '{}')
    }))
  }

  // Serialize the entries to the aligned TSV the server stores

  #serializeDayEntries()
  {
    if( ! dayEntries.length )
      return ''

    // Find the length of the longest strings

    let maxFoodLength     = Math.max( ...dayEntries.map( entry => entry.food.length))
    let maxCaloriesLength = Math.max( ...dayEntries.map( entry => String(entry.calories).length))  // for some reason we must do it like this here
    let maxFatLength      = Math.max( ...dayEntries.map( entry => String(entry.fat).length))
    let maxCarbsLength    = Math.max( ...dayEntries.map( entry => String(entry.carbs).length))
    let maxAminoLength    = Math.max( ...dayEntries.map( entry => String(entry.amino).length))
    let maxSaltLength     = Math.max( ...dayEntries.map( entry => String(entry.salt).length))
    let maxPriceLength    = Math.max( ...dayEntries.map( entry => String(entry.price).length))

    // Align cols

    return dayEntries.map( entry => {

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
  }

  // Show the "No entries yet" hint only when the list is empty

  #updateEmptyHint()
  {
    const hint = query('#dayEntriesEmpty')
    if( hint )
      hint.classList.toggle('d-none', query('#dayEntriesList .day-entry').length > 0)
  }

  // Escape text for innerHTML

  #esc( str )
  {
    const d = document.createElement('div')
    d.textContent = str == null ? '' : String(str)
    return d.innerHTML
  }

  /*@
  
  - public cause used in view

  */
  updSummary() /*@*/
  {
    const foodEntries = dayEntries.filter( entry => entry.type === 'F' || entry.type === 'FE' || entry.type === 'S' || entry.type === 'M')

    if( foodEntries.length == 0) {
      this.#resetSummary()   // e.g. after deleting the last entry (no reload anymore)
      return
    }

    // Quick summary

    // let caloriesSum = Number( foodEntries.reduce((sum, entry) => sum + Number(entry.calories), 0).toFixed(1))  // one decimal place
    query('#caloriesSum').textContent = Math.round( foodEntries.reduce((sum, entry) => sum + Number(entry.calories), 0))

    // eating time - filter out supplements, time-logged foods, and old "--:--:--" format for compatibility
    const timeLogEntries = foodEntries.filter( entry => entry.type !== "S" && entry.type !== 'M' && entry.time !== "--:--:--")
  
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

    query('#fibreSum').textContent = Math.round( foodEntries.reduce((sum, entry) => sum + (entry.nutrients.fibre ? Number(entry.nutrients.fibre) : 0), 0))  // only if set (else NaN)
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

      // Resolve one food's value for this nutrient row. Carbs > Fibre is special:
      // its active value lives at the top-level nutrients.fibre (the carbs group is
      // not carried in the day entry).
      // TASK: dedupe fibre in the data files, then this special case can go
      const isFibreRow = group === 'carbs' && short === 'fibre'

      const nutrientValue = food =>
        isFibreRow
          ? Number( food.nutrients.fibre ?? 0)
          : Number( food.nutrients[group]?.[short] ?? 0)

      const currentSum = Number( foodEntries.reduce((sum, food) => sum + nutrientValue(food), 0).toFixed(5))
      entry.dataset.current = currentSum


      let progressBarColor = 'bg-secondary'

      if( currentSum >= entry.dataset.lower && currentSum <= entry.dataset.upper )
        progressBarColor = 'bg-success'
      else
        progressBarColor = 'bg-danger'

      // Guard a zero ideal (e.g. Alcohol) so percent / bar width stay finite
      const ideal   = Number( entry.dataset.ideal)
      const percent = ideal > 0 ? (currentSum / ideal) * 100 : 0

      entry.find('.progress-bar').style.width = `${ Math.min( percent, 100)}%` // min: ensure it doesn't exceed 100% for progress
      // entry.find('.progress-label').textContent = `${currentSum} / ${entry.dataset.ideal}`
      entry.find('.percent').textContent = `${ Math.round( percent)}`
      entry.find('.vals').textContent    = `${currentSum} / ${entry.dataset.ideal}`

      entry.find('.progress-bar').classList.remove('bg-secondary', 'bg-success', 'bg-danger')
      entry.find('.progress-bar').classList.add(progressBarColor)

      // Table in modal

      let foodContributions = []

      for( let i = 0; i < foodEntries.length; i++ )
      {
        const food  = foodEntries[i]
        const value = nutrientValue( food)   // same resolver as the sum (incl. the fibre special case)

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
                <td class="text-end text-muted">(${ ideal > 0 ? ((item.value / ideal) * 100).toFixed(1) : '0.0' }%)</td>
              </tr>`
            ).join('')
          : '<tr><td colspan="3" class="text-center text-muted">No contributions</td></tr>'
        ) +
        '</table>';
    }
  }

  // Zero out the summary + nutrient bars (used when no entries remain)

  #resetSummary()
  {
    query('#caloriesSum').textContent = '0'
    query('#timeSum').textContent     = '00:00'
    query('#fatSum').textContent      = '0'
    query('#aminoSum').textContent    = '0'
    query('#carbsSum').textContent    = '0'
    query('#fibreSum').textContent    = '0'
    query('#saltSum').textContent     = '0.0'
    query('#priceSum').textContent    = '0.00'

    query('.nutrients-entry').forEach( entry => {
      entry.dataset.current = 0
      entry.find('.percent').textContent = '0'
      entry.find('.vals').textContent    = `0 / ${entry.dataset.ideal}`

      const bar = entry.find('.progress-bar')
      bar.style.width = '0%'
      bar.classList.remove('bg-success', 'bg-danger')
      bar.classList.add('bg-secondary')
    })
  }

  #saveDayEntries( uiMsg = false )
  {
    ajax.send('saveDayEntries', { date: this.date, data: this.#serializeDayEntries() }, function( result, data ) {

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
    
    // Get all real tab links for later use, excluding the new entry button.
    // Real tabs carry data-bs-toggle="tab"; the new entry button does not.
    // (Keying off .ms-auto broke in right-handed mode, where ms-auto sits on
    //  the first tab rather than the new entry button.)
    this.tabLinks = Array.from( query('#layout .nav-pills .nav-link[data-bs-toggle="tab"]'))
    
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

  renderMarkdown(markdownText)
  {
    marked.setOptions({
      gfm: true,
      breaks: false,
      pedantic: false,
      langPrefix: 'language-'
    })

    let html = marked.parse(markdownText)

    // same as overview, HalfDoneHero

    // Outlining lists
    // html = html.replace(/<li>\s*<p>(.*?)<\/p>\s*<\/li>/gs, '<li>$1</li>');
    html = html.replace(/<li>/gi, '<li class="md-li">');
    html = html.replace(/<li class="md-li">(.*?)<\/p>\s*(<ul|<ol)/gs, '<li class="md-li">$1$2');
    html = html.replace(/<p>\s*<\/p>\s*/gs, '');
    html = html.replace(/(<\/[^>]+>)\s*<p><\/p>/gs, '$1');
    html = html.replace(/<ul>/gi, '<ul class="no-indent">');
    html = html.replace(/<ul class="([^"]*)"/gi, '<ul class="$1 no-indent"');

    return html
  }
}
