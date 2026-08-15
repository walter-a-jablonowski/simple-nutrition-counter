class MainController
{
  constructor(args)
  {
    const urlParams = new URLSearchParams( window.location.search )
    const dateParam = urlParams.get('date')

    if( dateParam )  // date set by selectDay()
      this.date = dateParam
    else {
      const now = new Date()
      this.date = this.#formatDateLocal( now )  // all entries will be saved to this date (YYYY-MM-DD)
    }

    // Binding

    this.showOverlayInfo         = this.showOverlayInfo.bind(this)
    this.userSelectChange        = this.userSelectChange.bind(this)
    this.selectDay               = this.selectDay.bind(this)
    this.toggleUnprecise         = this.toggleUnprecise.bind(this)
    this.deleteLastLineBtnClick  = this.deleteLastLineBtnClick.bind(this)
    this.deleteEntryBtnClick     = this.deleteEntryBtnClick.bind(this)
    this.newEntryBtn             = this.newEntryBtn.bind(this)
    this.newEntrySaveBtn         = this.newEntrySaveBtn.bind(this)
    this.showPanel               = this.showPanel.bind(this)
    this.importRunBtn            = this.importRunBtn.bind(this)
    this.applyImportedFood       = this.applyImportedFood.bind(this)
    this.openSearch              = this.openSearch.bind(this)
    this.runSearch               = this.runSearch.bind(this)
    this.searchResultClick       = this.searchResultClick.bind(this)
    this.layoutItemClick         = this.layoutItemClick.bind(this)
    this.openMoveFood            = this.openMoveFood.bind(this)
    this.fillMoveFoodPositions   = this.fillMoveFoodPositions.bind(this)
    this.moveFoodConfirm         = this.moveFoodConfirm.bind(this)
    this.openPublishFoods        = this.openPublishFoods.bind(this)
    this.publishFoodsCheck       = this.publishFoodsCheck.bind(this)
    this.publishFoodsRun         = this.publishFoodsRun.bind(this)
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

    this.moveFoodModal = new bootstrap.Modal( query('#moveFoodModal'))

    if( query('#publishFoodsModal'))   // devMode only
      this.publishFoodsModal = new bootstrap.Modal( query('#publishFoodsModal'))

    // Entries per tab/group for the move dialog's position select (see move_food.php)

    this.moveFoodEntries = JSON.parse( document.getElementById('moveFoodEntries').textContent )

    query('#moveFoodGroup').addEventListener('change', () => this.fillMoveFoodPositions())

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

      // Opened from a day entry: its time and amount come first (see showEntryInfo)

      const entryInfo = btn.getAttribute('data-entry-info')

      if( entryInfo )
        infoModal.find('.modal-body').insertAdjacentHTML('afterbegin', entryInfo)

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

      // Grid placement defaults back to the first option (Meals > First entries)
      const targetGroup = query('#modalTargetGroup')
      if( targetGroup )  targetGroup.selectedIndex = 0

      // Precise grid-amount labels reflect the current weight unit
      this.#updateGridAmountUnits()

      // Back to the Entry tab
      bootstrap.Tab.getOrCreateInstance( query('#entryTab')).show()

      // Clear any lingering save-validation hint
      this.#clearSaveError()

      // Reset import state (dev feature)
      this.importedFood = null
      this.showPanel('form')
      if( window.foodPhotoCrl )  foodPhotoCrl.reset()
      const warn = query('#importWarnMsg')
      if( warn )  warn.classList.add('d-none')
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
  // The days come from the day menu (view/day_menu.php), which renders them
  // relative to the day the page was built on.

  selectDay( event, date )  // date: YYYY-MM-DD; see also construct
  {
    event.preventDefault()

    // The current day keeps the plain url, so a reload stays on today even
    // when the page was left open behind midnight

    window.location.href = date === this.#formatDateLocal( new Date()) ? 'index.php' : `?date=${date}`
  }


  // List: btns
  // The unprecise menu is rendered twice (desktop drop-up + mobile drop-down), so all
  // items of a type are updated together; see view/main/edit/unprecise_menu.php

  toggleUnprecise( event, flag )   // flag: day file header, see UNPRECISE_FLAGS
  {
    event.preventDefault()

    const item = document.querySelector(`.drop-menu-item[data-unprecise="${flag}"]`)
    const on   = ! item.classList.contains('active')

    this.applyUnpreciseUi( flag, on )

    ajax.send('updateUnpreciseHeader', { date: this.date, flag: flag, on: on }, (result, data) => {
      if( result !== 'success')
      {
        console.error('Failed to update unprecise header:', data.message || 'Unknown error')
        this.applyUnpreciseUi( flag, ! on )   // revert
      }
    })
  }

  applyUnpreciseUi( flag, on )
  {
    document.querySelectorAll(`.drop-menu-item[data-unprecise="${flag}"]`).forEach( item => {
      item.classList.toggle('active', on)
      item.setAttribute('aria-checked', on ? 'true' : 'false')
    })

    // Triggers turn orange as long as any flag is set

    const anyOn = document.querySelector('[data-unprecise].active') !== null

    document.querySelectorAll('.unprecise-menu').forEach( menu => menu.classList.toggle('any-on', anyOn))
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

    // The food info is the same for every logging of that food, so the entry's own
    // second line goes on top of it - time and amount are what tell the loggings
    // apart. Read off the rendered line, so both stay in sync by construction.
    // Opening from the grid has no entry and leaves the attribute away

    const time   = li.querySelector('.day-entry-time')?.textContent.trim()
    const amount = li.querySelector('.day-entry-amount')?.textContent.trim()

    if( time || amount )
      proxy.setAttribute('data-entry-info',
          '<div class="info-entry-sub d-flex gap-3 mb-3 fs-6 text-secondary">'
        + ( time   ? `<span><i class="bi bi-clock me-1"></i>${ this.#esc(time) }</span>` : '')
        + ( amount ? `<span>${ this.#esc(amount) }</span>` : '')
        + '</div>')

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

  newEntryBtn(event)
  {
    this.newEntryModal.show()
  }

  // Food search dialog
  //
  // Lets the user find a food by name and jump to the tab where it lives (the same
  // food may appear on several grid tabs). Pure in-memory lookup over the already
  // rendered food grid - no server request. Search runs on Enter / the magnifier.

  // term prefills the input and runs the search right away (used by the voice agent).
  // Not named "query" - that would shadow the global query() helper in here
  openSearch(event, term = '')
  {
    // Prefill on shown, not before: the show handler clears input and results
    if( term )
      query('#searchModal').addEventListener('shown.bs.modal', () => {
        query('#searchInput').value = term
        this.runSearch()
      }, { once: true })

    this.searchModal.show()
  }

  // Matching (food, tab) records from the rendered grid, one per occurrence, so each
  // one jumps to a specific tab. Shared by the search dialog and the voice agent
  findFoods(term)
  {
    const q = (term || '').trim().toLowerCase()

    if( ! q )
      return []

    return this.#buildFoodIndex().filter( rec => rec.food.toLowerCase().includes(q))
  }

  // Activate the food's grid tab, scroll the row into view and flash it. The pane must
  // be visible already, so callers that close a modal first have to wait for that
  jumpToFood(rec)
  {
    if( ! rec )
      return

    if( rec.navLink )  rec.navLink.click()  // activate the food-grid tab

    if( rec.itemEl ) {
      rec.itemEl.scrollIntoView({ behavior: 'smooth', block: 'center' })
      this.#flashItem( rec.itemEl )
    }
  }

  runSearch()
  {
    const q         = (query('#searchInput').value || '').trim().toLowerCase()
    const container = query('#searchResults')

    if( ! q ) {  // empty query clears results and does nothing else
      container.innerHTML = ''
      return
    }

    const matches = this.findFoods(q)

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
    query('#searchModal').addEventListener('hidden.bs.modal',
      () => setTimeout(() => this.jumpToFood(rec), 50), { once: true })

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

      // Food facts for the voice agent, put on the row by layout/entry.php

      const d = item.dataset

      index.push({
        food:     name,
        itemEl:   item,
        navLink:  tab ? tab.link  : null,
        tabLabel: tab ? tab.label : '',
        groupName,
        category:    btn ? btn.dataset.category : '',   // F food, S supplement, M misc
        vendor:      d.vendor      || '',
        productName: d.productName || '',
        voice:       d.voice       || '',               // optional spoken alias
        packWeight:  parseFloat( d.foodWeight) || 0,
        pieces:      parseFloat( d.foodPieces) || 0,
        unit:        d.foodUnit    || 'g',
        amounts:     Array.from( item.querySelectorAll('.amount-btn')).map( b => b.dataset.amountLabel)
      })
    })

    return index
  }

  /*@

  foodVocabulary()

  The spoken vocabulary of the food grid, one line per food, for the voice agent's system
  instruction. The grid is the source on purpose: retired records that are no longer in
  layout.yml can then never be logged.

  The name is what the user says most of the time, the vendor expands the single letter in
  it ("Gemüse R Bio" is Rewe), productName is the vendor's own wording and only corroborates.
  See dev_info/Voice_Logging_Plan.md.

  RETURN: string, one food per line

  */
  foodVocabulary() /*@*/
  {
    const seen  = new Set()
    const lines = []

    this.#buildFoodIndex().forEach( rec => {

      if( seen.has( rec.food))   // the same food on two tabs is one vocabulary entry
        return

      seen.add( rec.food)

      // Everything that helps recognise the food, in brackets after the name.
      // Some food files carry a placeholder instead of a vendor ("none", "multiple") -
      // passing those on would offer the model a shop that doesn't exist

      const vendor = ['none', 'multiple'].includes( rec.vendor.toLowerCase()) ? '' : rec.vendor
      const about  = [vendor, rec.productName, rec.voice].filter( Boolean).join(', ')

      let line = rec.food

      if( about )
        line += `  (${about})`

      if( rec.category === 'S' )
        line += '  [supplement]'

      if( rec.amounts.length )
        line += `  amounts: ${rec.amounts.join(' | ')}`

      // Only needed for foods sold by pack or piece, where "half a can" needs a base

      if( rec.packWeight )
        line += `  pack: ${rec.packWeight}${rec.unit}`

      if( rec.pieces )
        line += `, ${rec.pieces} pieces`

      lines.push( line)
    })

    return lines.join('\n')
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


  // Move an existing grid entry to another group (opened from the entry "..." menu)

  openMoveFood(event)
  {
    this.moveFoodName = event.currentTarget.dataset.food

    query('#moveFoodName').textContent = this.moveFoodName

    this.fillMoveFoodPositions()
    this.moveFoodModal.show()
  }

  /*@

  Position select: "Top" plus one "Below <entry>" per entry of the selected
  group. The food itself is left out (it can't be placed below itself). Called
  when the dialog opens and whenever the target group changes.

  */
  fillMoveFoodPositions() /*@*/
  {
    const groupSel = query('#moveFoodGroup')
    const posSel   = query('#moveFoodPos')
    const tab      = groupSel.options[groupSel.selectedIndex].dataset.tab

    const entries = (this.moveFoodEntries[tab]?.[groupSel.value] ?? [])
                      .filter( name => name !== this.moveFoodName )

    posSel.replaceChildren( new Option('Top', ''))          // new Option() escapes for us

    entries.forEach( name => posSel.append( new Option(`Below  ${name}`, name)))
  }

  moveFoodConfirm()
  {
    const sel = query('#moveFoodGroup')

    const tab   = sel.options[sel.selectedIndex].dataset.tab
    const group = sel.value
    const after = query('#moveFoodPos').value

    ajax.send('moveFood', { food: this.moveFoodName, tab: tab, group: group, after: after }, ( result, data ) => {

      if( result === 'success') {
        this.moveFoodModal.hide()
        window.location.reload()
      }
      else
        query('#uiMsg').innerHTML = (data && data.message) || 'Could not move food'
    })
  }


  // Publish the food data to the installation folder (devMode, see tools/publish_foods).
  // Two steps: check reports what would change, publish applies it.

  openPublishFoods()
  {
    query('#publishFoodsReport').textContent = 'Click "Check" to see what would change.'
    query('#publishFoodsRunBtn').disabled    = true

    this.publishFoodsModal.show()
  }

  publishFoodsCheck()
  {
    this.#publishFoods('plan')
  }

  publishFoodsRun()
  {
    this.#publishFoods('run')
  }

  /*@

  Both steps hit the same handler; 'run' rebuilds the plan server side, so the
  report the user confirmed is never the thing that gets applied blindly.
  Publish stays disabled until a check has run and found something to do.

  */
  #publishFoods( mode ) /*@*/
  {
    const report = query('#publishFoodsReport')
    const runBtn = query('#publishFoodsRunBtn')

    report.textContent = mode === 'run' ? 'Publishing ...' : 'Checking ...'
    runBtn.disabled    = true

    ajax.send('publishFoods', { mode: mode, delete: query('#publishFoodsDelete').checked }, ( result, data ) => {

      if( result !== 'success')
      {
        report.textContent = (data && data.message) || 'Could not run the publish tool'
        return
      }

      const lines = data.lines.slice()

      if( mode === 'run')
      {
        lines.push('', `Copied ${data.copied}, deleted ${data.deleted}.`)

        if( data.backupDir )
          lines.push(`Replaced files backed up to ${data.backupDir}`)
      }

      if( data.errors.length )
        lines.push('', ...data.errors.map( e => `ERROR  ${e}`))

      report.textContent = lines.join('\n')

      // After a run everything is published, so there is nothing left to confirm

      runBtn.disabled = mode === 'run' || ! (data.counts.new || data.counts.changed || data.counts.obsolete)
    })
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


  // Import a food, from a product page or from pictures of the packaging (dev feature)
  //
  // Flow: header button -> import or photo panel -> importFood / importFoodPhotos
  // ajax fills the form and checks "Save as new food" -> Add entry persists the
  // food via saveFood and reloads so the new food shows up in the grid.

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

      this.applyImportedFood( data.food )
    })
  }

  // What both imports do with their result: fill the form, come back to it and
  // arm the save, plus whatever the reader could not be sure about

  applyImportedFood( food, warnings )
  {
    this.#fillFormFromFood( food )
    this.showPanel('form')

    const chk = query('#saveNewFood')
    if( chk )  chk.checked = true

    const warn  = query('#importWarnMsg')
    const lines = warnings || []

    // textContent, not innerHTML: a warning can quote what the model read off a picture

    if( warn ) {
      warn.textContent = ''

      lines.forEach( line => {
        const row = document.createElement('div')
        row.textContent = line
        warn.appendChild( row )
      })

      warn.classList.toggle('d-none', ! lines.length)
    }
  }

  /* Which of the modal's panels is visible: 'form', 'import' or 'photo'.
     The footer and the header buttons belong to the form, so they go with it.
     Panels that are switched off in the config are simply not there */

  showPanel( name )
  {
    const panels = {
      form:   '#newEntryFormPanel',
      import: '#newEntryImportPanel',
      photo:  '#newEntryPhotoPanel'
    }

    for( const [panelName, selector] of Object.entries( panels ))
    {
      const panel = query( selector )
      if( panel )  panel.classList.toggle('d-none', panelName !== name)
    }

    const onForm = name === 'form'

    for( const selector of ['#newEntryFooter', '#importShowBtn', '#imgShowBtn'])
    {
      const element = query( selector )
      if( element )  element.classList.toggle('d-none', ! onForm)
    }
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

    // Grid placement (tab + group) for the new food record

    const targetSel = query('#modalTargetGroup')
    const targetTab   = targetSel ? targetSel.options[targetSel.selectedIndex].dataset.tab : null
    const targetGroup = targetSel ? targetSel.value : null

    ajax.send('saveFood', { food: food, targetTab: targetTab, targetGroup: targetGroup }, ( result, data ) => {

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

    // new version
    // console.log( queryData('.food-item ...', ['food']))

    let target = event.target.closest('.amount-btn')

    this.#addDayEntry( this.#entryFromButton( target, 1, {
      label:  target.dataset.amountLabel,
      weight: parseFloat( target.dataset.amountWeight) || 0
    }))
  }

  /*@

  #entryFromButton()

  The day entry for one amount button, optionally scaled to a different amount. Shared by
  the grid click (factor 1) and by the voice agent, which logs amounts no button has.

  Scaling is a plain multiplication because the server side is linear: every button is
  nutrient * weight/100 (models/LayoutView.php), so any other amount sits on the same
  straight line. The rounding mirrors the server's - 1 decimal for the macros, 2 for the
  price, 5 for the mg-scale groups - so factor 1 reproduces the button exactly.

  ARGS:

    btn:    the .amount-btn element to take the values from
    factor: 1 for the button's own amount
    amount: { label, weight } as shown in the day entries list

  RETURN: object, the day entry

  */
  #entryFromButton( btn, factor, amount ) /*@*/
  {
    let nutritionalValues = JSON.parse(btn.dataset.nutritionalvalues)

    const macro = value => Math.round( (parseFloat( value) || 0) * factor * 10) / 10

    const micro = json => Object.fromEntries(
      Object.entries( JSON.parse( json)).map(([ name, value]) =>
        [name, Math.round( (parseFloat( value) || 0) * factor * 1e5) / 1e5])
    )

    return {
      type:     btn.dataset.category || 'F',  // use category from data attribute (F=Food, S=Supplement)
      food:     btn.dataset.food,  // TASK: rename
      calories: macro( btn.dataset.calories),
      fat:      macro( nutritionalValues.fat),
      carbs:    macro( nutritionalValues.carbs),
      amino:    macro( nutritionalValues.amino),
      salt:     macro( nutritionalValues.salt),
      price:    Math.round( (parseFloat( btn.dataset.price) || 0) * factor * 100) / 100,
      xTimeLog: btn.dataset.xTimeLog === 'true',

      // Only for the list item, see #createEntryEl. Not saved: a reload reads them
      // off the food again (day_entries.php), so fixing the food clears them

      unprecise: btn.dataset.unprecise === 'true',
      noPrice:   btn.dataset.noPrice   === 'true',

      nutrients: {
        // amount kept first in the json portion: label is shown in the day entries,
        // weight (grams) is the calculated amount kept for later use
        amount: amount,
        fibre: macro( nutritionalValues.fibre || 0 ),  // TASK: or only add when set (see updSummary() for sum only if available)
        fat:   micro( btn.dataset.fattyacids ),
        amino: micro( btn.dataset.aminoacids ),
        vit:   micro( btn.dataset.vitamins ),
        min:   micro( btn.dataset.minerals ),
        sec:   micro( btn.dataset.secondary ),
        misc:  micro( btn.dataset.misc )
      }
    }
  }

  /*@

  logFoodAmount()

  Log a spoken amount of a grid food - the voice equivalent of tapping an amount button.
  The amount does not have to be one the grid offers: "200g" is logged as a single precise
  entry, not as two taps of the 100g button. See dev_info/Voice_Logging_Plan.md.

  ARGS:

    foodName: the exact grid name
    value:    the number that was said, may be missing
    unit:     'g' | 'ml' | 'piece' | 'pack' | 'x', may be missing

  RETURN: object, the result for the agent to read back

  */
  logFoodAmount( foodName, value = null, unit = null ) /*@*/
  {
    const matches = this.findFoods( foodName )

    if( ! matches.length )
      return { result: 'none' }

    // findFoods is a substring search, so "Gemüse R" also finds "Gemüse R Bio". An exact
    // name wins; without one the name is ambiguous and must not be logged on a guess

    const wanted = (foodName || '').trim().toLowerCase()
    const exact  = matches.filter( rec => rec.food.toLowerCase() === wanted )

    if( ! exact.length && matches.length > 1 )
      return { result: 'multiple', matches: matches.map( rec => ({ food: rec.food, tab: rec.tabLabel })) }

    const rec = (exact.length ? exact : matches)[0]  // the same food on two tabs is one food

    const buttons = Array.from( rec.itemEl.querySelectorAll('.amount-btn'))
                         .map( btn => ({ btn, weight: parseFloat( btn.dataset.amountWeight) || 0 }))
                         .filter( button => button.weight > 0 )

    if( ! buttons.length )
      return { result: 'error', message: `${rec.food} has no amount that could be logged` }

    // What was said, in grams (ml counts as the same number, see data-food-unit)

    const said        = parseFloat( value )
    const pieceWeight = rec.pieces ? rec.packWeight / rec.pieces : buttons[0].weight

    let weight

    if( ! (said > 0) )
      weight = buttons[0].weight       // no amount said, take the food's typical one
    else if( unit === 'piece' )
      weight = said * pieceWeight
    else if( unit === 'pack' )
      weight = said * rec.packWeight
    else if( unit === 'x' )
      weight = said * buttons[0].weight
    else
      weight = said                    // g, ml, or nothing given - grams is the common case

    if( ! (weight > 0) )
      return { result: 'error', message: `Could not work out that amount of ${rec.food}` }

    // Scale from the button closest to the target so the factor stays near 1: the button
    // values are already rounded, and a big factor would multiply that rounding up. The
    // log makes "closest" symmetric, so half a button is as near as twice one

    const ref    = buttons.reduce(( best, button) =>
      Math.abs( Math.log( weight / button.weight)) < Math.abs( Math.log( weight / best.weight)) ? button : best )
    const factor = weight / ref.weight

    if( factor > 20 )
      return { result: 'error', message: `${weight}${rec.unit} of ${rec.food} seems far too much, please check` }

    // Label for the day entries list, in the terms the amount was given in

    const fractions = { '0.13': '1/8', '0.25': '1/4', '0.33': '1/3', '0.5': '1/2', '0.67': '2/3', '0.75': '3/4' }

    let label

    if( unit === 'piece' )
      label = `${said} pc`
    else if( unit === 'pack' )
      label = fractions[ String( Math.round( said * 100) / 100)] ?? `${said} pack`
    else if( unit === 'x' )
      label = `${said}x ${ref.btn.dataset.amountLabel}`
    else
      label = `${Math.round( weight * 10) / 10}${rec.unit}`

    const entry = this.#entryFromButton( ref.btn, factor,
                                         { label: label, weight: Math.round( weight * 10) / 10 })

    this.#lastLogBatch.push( this.#addDayEntry( entry ))
    this.#flashItem( rec.itemEl )

    return {
      result:   'logged',
      food:     rec.food,
      label:    label,
      weight:   entry.nutrients.amount.weight,
      unit:     rec.unit,
      calories: entry.calories
    }
  }

  // The rows the last logFoods() added, so undoLastLog() can take back exactly those

  #lastLogBatch = []

  /*@

  logFoods()

  Log a whole spoken list at once. Each call starts a new batch, so undoLastLog() always
  takes back the last thing the user said, not everything ever logged by voice.

  ARGS: items: array of { food, value, unit }

  RETURN: array, one result per item, in the order they came in

  */
  logFoods( items ) /*@*/
  {
    this.#lastLogBatch = []

    return items.map( item => this.logFoodAmount( item.food, item.value, item.unit))
  }

  /*@

  undoLastLog()

  Take back what the last logFoods() added - the repair for a misheard amount or a wrong
  food. Rows the user deleted by hand in the meantime are skipped, so undo never removes
  something twice or deletes an entry that has already gone.

  RETURN: object, what was removed, for the agent to say back

  */
  undoLastLog() /*@*/
  {
    const rows = this.#lastLogBatch.filter( li => li && li.isConnected )

    if( ! rows.length )
      return { result: 'nothing' }

    const removed = rows.map( li => ({
      food:   li.dataset.food,
      amount: (JSON.parse( li.dataset.nutrients || '{}').amount || {}).label || ''
    }))

    rows.forEach( li => li.remove())

    this.#lastLogBatch = []

    this.#afterListChange()

    return { result: 'undone', removed: removed }
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

    const li = this.#createEntryEl( entry)

    query('#dayEntriesList').appendChild( li )

    this.#afterListChange()

    // Reveal the just-added entry, unless the user scrolled up to review older ones
    if( this.autoScrollDayEntries )
      this.#scrollDayEntriesToBottom()

    return li   // the voice agent keeps it, so undoLastLog() can take it back
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

    // Same flags the php side renders, in the same order (see day_entries.php)

    let flags = ''

    if( entry.unprecise )
      flags += '<i class="bi bi-question-circle" title="Unprecise food data"></i>'

    if( entry.noPrice )
      flags += `<i class="bi ${ query('#dayEntriesList').dataset.currencyIcon }" title="No price"></i>`

    li.innerHTML =
      `<span class="day-entry-type">${ this.#esc(entry.type) }</span>`
      + `<div class="day-entry-main flex-grow-1 ms-2 overflow-hidden">`
      +   `<div class="day-entry-name text-truncate">${ this.#esc(entry.food) }</div>`
      +   `<div class="day-entry-sub small text-secondary d-flex">`
      +     `<span class="day-entry-time">${ this.#esc(timeDisp) }</span>`
      +     `<span class="day-entry-amount">${ this.#esc(amountDisp) }</span>`
      +     ( flags ? `<span class="day-entry-flags ms-auto">${flags}</span>` : '')
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

  // Saving is implicit (every list change calls this), so we only report failures

  #saveDayEntries()
  {
    ajax.send('saveDayEntries', { date: this.date, data: this.#serializeDayEntries() }, function( result, data ) {

      if( result !== 'success')
        query('#uiMsg').innerHTML = (data && data.message) || 'Could not save the day entries'
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
