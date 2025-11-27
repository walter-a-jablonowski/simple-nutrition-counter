class NutritionWidgetsController
{
  constructor(root = document)
  {
    this.root = root;

    // Elements
    this.navLinks         = this.root.querySelectorAll('[data-nav]');
    this.widgetsContainer = this.root.querySelector('.nutrition-widgets .overflow-auto');
    this.scrollArrow      = this.root.querySelector('.nutrition-widgets .scroll-arrow');
    this.mobileCaretBtn   = this.root.querySelector('.mobile-caret-btn');

    // State
    this.lastScrollPosition = 0;
    this.userHasScrolled    = false;
    this.currentNav         = 'day';

    // Bind handlers
    this._onNavClick         = this._onNavClick.bind(this);
    this._onResize           = this._onResize.bind(this);
    this._onScroll           = this._onScroll.bind(this);
    this._onArrowClick       = this._onArrowClick.bind(this);
    this._onMobileCaretClick = this._onMobileCaretClick.bind(this);

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

    // Core listeners
    window.addEventListener('resize', this._onResize);
    this.widgetsContainer.addEventListener('scroll', this._onScroll);
    this.scrollArrow.addEventListener('click', this._onArrowClick);

    // Initial visibility check after render
    setTimeout(() => this.checkScroll(), 100);
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
    const rightContent = document.getElementById('rightContent');
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
