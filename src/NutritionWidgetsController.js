// Drives the design_3 layout: sidebar / bottomNav nav switching, widget
// horizontal scroll arrow, mobile caret collapse and unprecise dropdown.
// Wired up after mainCrl is ready in view/-this.php.

class NutritionWidgetsController
{
  constructor( root = document )
  {
    this.root = root

    // Configuration
    this.maxWidgetTextLength = 15

    // Elements
    this.navLinks         = this.root.querySelectorAll('[data-nav]')
    this.widgetsContainer = this.root.querySelector('.nutrition-widgets .overflow-auto')
    this.scrollArrow      = this.root.querySelector('.nutrition-widgets .scroll-arrow')
    this.mobileCaretBtn   = this.root.querySelector('.mobile-caret-btn')

    // State
    this.lastScrollPosition = 0
    this.userHasScrolled    = false
    this.currentNav         = 'day'

    // Bind handlers
    this._onNavClick         = this._onNavClick.bind(this)
    this._onResize           = this._onResize.bind(this)
    this._onScroll           = this._onScroll.bind(this)
    this._onWheel            = this._onWheel.bind(this)
    this._onArrowClick       = this._onArrowClick.bind(this)
    this._onMobileCaretClick = this._onMobileCaretClick.bind(this)
    this._onTogglePrice      = this._onTogglePrice.bind(this)

    this.init()
  }

  init()
  {
    // Mobile caret button
    if( this.mobileCaretBtn )
      this.mobileCaretBtn.addEventListener('click', this._onMobileCaretClick)

    // Navigation links
    this.navLinks.forEach( link => {
      link.addEventListener('click', this._onNavClick)
    })

    // Dropdown toggle items
    // toggleNutrients / toggleTime are owned by MainController (AJAX-backed); see mainCrl.toggleUnpreciseMode.
    // togglePrice is UI-only for now.
    const togglePrice = this.root.querySelector('#togglePrice')
    if( togglePrice )  togglePrice.addEventListener('click', this._onTogglePrice)

    // Core listeners (only if widgets are present)
    if( this.widgetsContainer && this.scrollArrow )
    {
      window.addEventListener('resize', this._onResize)
      this.widgetsContainer.addEventListener('scroll', this._onScroll)
      this.widgetsContainer.addEventListener('wheel', this._onWheel, { passive: false })
      this.scrollArrow.addEventListener('click', this._onArrowClick)

      // Initial visibility check after render
      setTimeout(() => this.checkScroll(), 100)

      // Initialize widget value scrolling for long text
      setTimeout(() => this._initWidgetValueScrolling(), 200)
    }
  }

  _onNavClick( event )
  {
    event.preventDefault()

    const navType = event.currentTarget.getAttribute('data-nav')
    if( navType === this.currentNav )
      return  // already active

    this.switchToNav( navType )
  }

  _onMobileCaretClick()
  {
    const caretIcon          = document.querySelector('.mobile-caret-btn i')
    const leftColumn         = document.querySelector('.left-column')
    const mainContentSection = document.querySelector('.left-column section.flex-grow-1')

    if( ! leftColumn || ! mainContentSection )
      return

    if( leftColumn.classList.contains('collapsed') ) {
      leftColumn.classList.remove('collapsed')
      mainContentSection.classList.remove('collapsed')
      if( caretIcon ) caretIcon.className = 'bi bi-caret-down'
    }
    else {
      leftColumn.classList.add('collapsed')
      mainContentSection.classList.add('collapsed')
      if( caretIcon ) caretIcon.className = 'bi bi-caret-up'
    }
  }

  _onTogglePrice( event )
  {
    event.preventDefault()

    const check = event.currentTarget.querySelector('i.bi-check')
    if( check )
      check.classList.toggle('invisible')

    // Trigger dropdown icon recolor via mainCrl (it reads DOM state across all three items)
    if( typeof mainCrl !== 'undefined' && mainCrl.updateUnpreciseDropdownIcon )
      mainCrl.updateUnpreciseDropdownIcon()
  }

  _initWidgetValueScrolling()
  {
    const widgetValues = this.root.querySelectorAll('#strategy .widget-value')

    widgetValues.forEach( valueEl => {
      const text = valueEl.textContent.trim()

      if( text.length > this.maxWidgetTextLength )
      {
        const fullText      = text
        const truncatedText = text.substring( 0, this.maxWidgetTextLength) + '...'

        valueEl.style.overflow   = 'visible'
        valueEl.style.whiteSpace = 'nowrap'
        valueEl.style.position   = 'relative'
        valueEl.style.clipPath   = 'inset(0 0.35rem)'  // clip with visible padding

        this._scrollTextOnce( valueEl, fullText, truncatedText)
      }
    })
  }

  _scrollTextOnce( element, fullText, truncatedText )
  {
    const wrapper = document.createElement('span')
    wrapper.style.display    = 'inline-block'
    wrapper.style.whiteSpace = 'nowrap'
    wrapper.textContent      = fullText

    element.textContent = ''
    element.appendChild( wrapper )

    setTimeout(() => {
      const containerWidth = element.clientWidth
      const textWidth      = wrapper.scrollWidth
      const scrollDistance = textWidth - containerWidth

      if( scrollDistance <= 0 ) {
        element.textContent = truncatedText
        return
      }

      wrapper.style.transition = 'transform 3s linear'
      wrapper.style.transform  = `translateX(-${ scrollDistance }px)`

      setTimeout(() => {
        wrapper.style.transition = 'transform 0.5s ease-out'
        wrapper.style.transform  = 'translateX(0)'

        setTimeout(() => {
          element.textContent = truncatedText
        }, 500)
      }, 3000)
    }, 1000)
  }

  _onResize()
  {
    this.userHasScrolled    = false
    this.lastScrollPosition = this.widgetsContainer.scrollLeft
    this.checkScroll()
  }

  _onScroll()
  {
    if( Math.abs( this.widgetsContainer.scrollLeft - this.lastScrollPosition) > 5 )
      this.userHasScrolled = true

    this.lastScrollPosition = this.widgetsContainer.scrollLeft
    this.checkScroll()
  }

  _onWheel( event )
  {
    // Map vertical wheel to horizontal scroll; let native horizontal wheel pass through.
    if( event.deltaY === 0 ) return
    if( event.shiftKey ) return  // Shift+wheel: browsers already scroll horizontally

    const delta = event.deltaY !== 0 ? event.deltaY : event.deltaX
    const max   = this.widgetsContainer.scrollWidth - this.widgetsContainer.clientWidth
    const atStart = this.widgetsContainer.scrollLeft <= 0          && delta < 0
    const atEnd   = this.widgetsContainer.scrollLeft >= max - 1    && delta > 0
    if( atStart || atEnd ) return  // allow page to scroll vertically at the ends

    event.preventDefault()
    this.widgetsContainer.scrollLeft += delta
  }

  _onArrowClick()
  {
    this.widgetsContainer.scrollBy({
      left: 200,
      behavior: 'smooth'
    })
    setTimeout(() => {
      this.lastScrollPosition = this.widgetsContainer.scrollLeft
    }, 500)
  }

  checkScroll()
  {
    if( this.userHasScrolled && this.widgetsContainer.scrollLeft > 20 ) {
      this.scrollArrow.classList.remove('visible')
      return
    }

    if( this.widgetsContainer.scrollWidth > this.widgetsContainer.clientWidth )
      this.scrollArrow.classList.add('visible')
    else
      this.scrollArrow.classList.remove('visible')

    if( this.widgetsContainer.scrollLeft + this.widgetsContainer.clientWidth >= this.widgetsContainer.scrollWidth - 10 )
      this.scrollArrow.classList.remove('visible')
  }

  destroy()
  {
    if( this.mobileCaretBtn )
      this.mobileCaretBtn.removeEventListener('click', this._onMobileCaretClick)

    window.removeEventListener('resize', this._onResize)

    if( this.widgetsContainer ) {
      this.widgetsContainer.removeEventListener('scroll', this._onScroll)
      this.widgetsContainer.removeEventListener('wheel', this._onWheel)
    }

    if( this.scrollArrow )
      this.scrollArrow.removeEventListener('click', this._onArrowClick)
  }

  switchToNav( navType )
  {
    // Update active state on every nav link with matching data-nav (mirrors sidebar + bottomNav)
    this.root.querySelectorAll('[data-nav]').forEach( link => {
      const isMatch = link.getAttribute('data-nav') === navType
      link.classList.toggle('active', isMatch)
    })

    const mainLayout       = document.getElementById('mainLayout')
    const favoritesLayout  = document.getElementById('favoritesLayout')
    const dayContent       = document.getElementById('dayContent')
    const nutrientsContent = document.getElementById('nutrientsContent')

    if( navType === 'day' )
    {
      mainLayout.classList.remove('d-none')
      favoritesLayout.classList.add('d-none')

      dayContent.classList.remove('d-none')
      nutrientsContent.classList.add('d-none')
    }
    else if( navType === 'nutrients' )
    {
      mainLayout.classList.remove('d-none')
      favoritesLayout.classList.add('d-none')

      dayContent.classList.add('d-none')
      nutrientsContent.classList.remove('d-none')
    }
    else if( navType === 'favorites' )
    {
      mainLayout.classList.add('d-none')
      favoritesLayout.classList.remove('d-none')
    }

    this.currentNav = navType
  }
}
