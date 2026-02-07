class NutritionWidgetsController
{
  constructor(root = document)
  {
    this.root = root;

    // Configuration
    this.maxWidgetTextLength = 15;

    // Elements
    this.navLinks         = this.root.querySelectorAll('[data-nav]');
    this.widgetsContainer = this.root.querySelector('.nutrition-widgets .overflow-auto');
    this.scrollArrow      = this.root.querySelector('.nutrition-widgets .scroll-arrow');
    this.mobileCaretBtn   = this.root.querySelector('.mobile-caret-btn');

    // State
    this.lastScrollPosition = 0;
    this.userHasScrolled    = false;
    this.currentNav         = 'day';
    this.unpreciseStates    = {
      nutrients: false,
      time: false,
      price: false
    };

    // Bind handlers
    this._onNavClick         = this._onNavClick.bind(this);
    this._onResize           = this._onResize.bind(this);
    this._onScroll           = this._onScroll.bind(this);
    this._onArrowClick       = this._onArrowClick.bind(this);
    this._onMobileCaretClick = this._onMobileCaretClick.bind(this);
    this._onToggleNutrients  = this._onToggleNutrients.bind(this);
    this._onToggleTime       = this._onToggleTime.bind(this);
    this._onTogglePrice      = this._onTogglePrice.bind(this);

    this.init();
  }

  init()
  {
    if( ! this.widgetsContainer || ! this.scrollArrow )
      return; // nth to wire up

    // Mobile caret button
    if( this.mobileCaretBtn )
      this.mobileCaretBtn.addEventListener('click', this._onMobileCaretClick);

    // Navigation links
    this.navLinks.forEach(link => {
      link.addEventListener('click', this._onNavClick);
    });

    // Dropdown toggle items
    const toggleNutrients = this.root.querySelector('#toggleNutrients');
    const toggleTime = this.root.querySelector('#toggleTime');
    const togglePrice = this.root.querySelector('#togglePrice');
    
    if( toggleNutrients )
      toggleNutrients.addEventListener('click', this._onToggleNutrients);
    if( toggleTime )
      toggleTime.addEventListener('click', this._onToggleTime);
    if( togglePrice )
      togglePrice.addEventListener('click', this._onTogglePrice);

    // Core listeners
    window.addEventListener('resize', this._onResize);
    this.widgetsContainer.addEventListener('scroll', this._onScroll);
    this.scrollArrow.addEventListener('click', this._onArrowClick);

    // Initial visibility check after render
    setTimeout(() => this.checkScroll(), 100);
    
    // Initialize widget value scrolling for long text
    setTimeout(() => this._initWidgetValueScrolling(), 200);
  }

  _onNavClick(event)
  {
    event.preventDefault();
    
    const navType = event.currentTarget.getAttribute('data-nav');
    if( navType === this.currentNav )
      return; // Already active
    
    this.switchToNav(navType);
  }

  _onMobileCaretClick() {
    const caretIcon = document.querySelector('.mobile-caret-btn i');
    const leftColumn = document.querySelector('.left-column');
    const mainContentSection = document.querySelector('.left-column section.flex-grow-1');
    
    if( leftColumn.classList.contains('collapsed') ) {
      leftColumn.classList.remove('collapsed');
      mainContentSection.classList.remove('collapsed');
      caretIcon.className = 'bi bi-caret-down';
    } else {
      leftColumn.classList.add('collapsed');
      mainContentSection.classList.add('collapsed');
      caretIcon.className = 'bi bi-caret-up';
    }
  }

  _onToggleNutrients(event) {
    event.preventDefault();
    this.unpreciseStates.nutrients = !this.unpreciseStates.nutrients;
    this._updateCheckbox(event.currentTarget, this.unpreciseStates.nutrients);
    this._updateDropdownIcon();
  }

  _onToggleTime(event) {
    event.preventDefault();
    this.unpreciseStates.time = !this.unpreciseStates.time;
    this._updateCheckbox(event.currentTarget, this.unpreciseStates.time);
    this._updateDropdownIcon();
  }

  _onTogglePrice(event) {
    event.preventDefault();
    this.unpreciseStates.price = !this.unpreciseStates.price;
    this._updateCheckbox(event.currentTarget, this.unpreciseStates.price);
    this._updateDropdownIcon();
  }

  _updateCheckbox(element, isChecked) {
    const checkbox = element.querySelector('i.bi-check');
    if( checkbox )
      checkbox.style.visibility = isChecked ? 'visible' : 'hidden';
  }

  _updateDropdownIcon() {
    const dropdownIcon = this.root.querySelector('#unpreciseDropdown i');
    const anyChecked = this.unpreciseStates.nutrients || this.unpreciseStates.time || this.unpreciseStates.price;
    
    if( dropdownIcon ) {
      if( anyChecked )
        dropdownIcon.style.color = '#fd7e14'; // Bootstrap orange
      else
        dropdownIcon.style.color = ''; // Reset to default
    }
  }

  _initWidgetValueScrolling() {
    const widgetValues = this.root.querySelectorAll('#strategy .widget-value');
    
    widgetValues.forEach(valueEl => {
      const text = valueEl.textContent.trim();
      
      if( text.length > this.maxWidgetTextLength ) {
        const fullText = text;
        const truncatedText = text.substring(0, this.maxWidgetTextLength) + '...';
        
        valueEl.style.overflow = 'hidden';
        valueEl.style.whiteSpace = 'nowrap';
        valueEl.style.position = 'relative';
        
        this._scrollTextOnce(valueEl, fullText, truncatedText);
      }
    });
  }

  _scrollTextOnce(element, fullText, truncatedText) {
    const wrapper = document.createElement('span');
    wrapper.style.display = 'inline-block';
    wrapper.style.whiteSpace = 'nowrap';
    wrapper.textContent = fullText;
    
    element.textContent = '';
    element.appendChild(wrapper);
    
    setTimeout(() => {
      const containerWidth = element.clientWidth;
      const textWidth = wrapper.scrollWidth;
      const scrollDistance = textWidth - containerWidth;
      
      if( scrollDistance <= 0 ) {
        element.textContent = truncatedText;
        return;
      }
      
      wrapper.style.transition = 'transform 3s linear';
      wrapper.style.transform = `translateX(-${scrollDistance}px)`;
      
      setTimeout(() => {
        wrapper.style.transition = 'transform 0.5s ease-out';
        wrapper.style.transform = 'translateX(0)';
        
        setTimeout(() => {
          element.textContent = truncatedText;
        }, 500);
      }, 3000);
    }, 1000);
  }

  _onResize()
  {
    // Reset user scroll state on resize
    this.userHasScrolled = false;
    this.lastScrollPosition = this.widgetsContainer.scrollLeft;
    this.checkScroll();
  }

  _onScroll()
  {
    // Detect if this is a user-initiated scroll
    if (Math.abs(this.widgetsContainer.scrollLeft - this.lastScrollPosition) > 5) {
      this.userHasScrolled = true;
    }
    this.lastScrollPosition = this.widgetsContainer.scrollLeft;
    this.checkScroll();
  }

  _onArrowClick()
  {
    this.widgetsContainer.scrollBy({ 
      left: 200, 
      behavior: 'smooth' 
    });
    // This is not a user-initiated scroll
    setTimeout(() => {
      this.lastScrollPosition = this.widgetsContainer.scrollLeft;
    }, 500);
  }

  checkScroll()
  {
    // If user has manually scrolled more than 20px, hide the arrow
    if( this.userHasScrolled && this.widgetsContainer.scrollLeft > 20) {
      this.scrollArrow.classList.remove('visible');
      return;
    }

    if( this.widgetsContainer.scrollWidth > this.widgetsContainer.clientWidth)
      // Content is wider than container, show arrow
      this.scrollArrow.classList.add('visible');
    else
      // No scrolling needed, hide arrow
      this.scrollArrow.classList.remove('visible');

    // Hide arrow if scrolled to the end
    if (this.widgetsContainer.scrollLeft + this.widgetsContainer.clientWidth >= this.widgetsContainer.scrollWidth - 10)
      this.scrollArrow.classList.remove('visible');
  }

  destroy()
  {
    if( this.mobileCaretBtn )
      this.mobileCaretBtn.removeEventListener('click', this._onMobileCaretClick);

    window.removeEventListener('resize', this._onResize);

    if( this.widgetsContainer )
      this.widgetsContainer.removeEventListener('scroll', this._onScroll);

    if( this.scrollArrow )
      this.scrollArrow.removeEventListener('click', this._onArrowClick);
  }

  switchToNav(navType)
  {
    // Update active nav link
    this.navLinks.forEach(link => {
      link.classList.remove('active');
      if( link.getAttribute('data-nav') === navType )
        link.classList.add('active');
    });

    // Get content elements
    const mainLayout = document.getElementById('mainLayout');
    const favoritesLayout = document.getElementById('favoritesLayout');
    const rightContent = document.getElementById('layout');
    const dayContent = document.getElementById('dayContent');
    const nutrientsContent = document.getElementById('nutrientsContent');

    if( navType === 'day' )
    {
      // Show main layout, hide favorites layout
      mainLayout.classList.remove('d-none');
      favoritesLayout.classList.add('d-none');
      
      // Show day content, hide nutrients content
      dayContent.classList.remove('d-none');
      nutrientsContent.classList.add('d-none');
    }
    else if( navType === 'nutrients' )
    {
      // Show main layout, hide favorites layout
      mainLayout.classList.remove('d-none');
      favoritesLayout.classList.add('d-none');
      
      // Hide day content, show nutrients content
      dayContent.classList.add('d-none');
      nutrientsContent.classList.remove('d-none');
    }
    else if( navType === 'favorites' )
    {
      // Hide main layout, show favorites layout
      mainLayout.classList.add('d-none');
      favoritesLayout.classList.remove('d-none');
    }

    this.currentNav = navType;
  }
}
