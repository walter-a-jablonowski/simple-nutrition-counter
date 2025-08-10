class NutritionWidgetsController {

  constructor(root = document)
  {
    this.root = root;

    // Elements
    this.widgetsContainer = this.root.querySelector('.nutrition-widgets .overflow-auto');
    this.scrollArrow      = this.root.querySelector('.nutrition-widgets .scroll-arrow');
    this.mobileCaretBtn   = this.root.querySelector('.mobile-caret-btn');

    // State
    this.lastScrollPosition = 0;
    this.userHasScrolled = false;

    // Bind handlers
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

    // Core listeners
    window.addEventListener('resize', this._onResize);
    this.widgetsContainer.addEventListener('scroll', this._onScroll);
    this.scrollArrow.addEventListener('click', this._onArrowClick);

    // Initial visibility check after render
    setTimeout(() => this.checkScroll(), 100);
  }

  _onMobileCaretClick(event)
  {
    // Toggle the caret icon between down and up
    const caretIcon = event.currentTarget.querySelector('i');
    if( caretIcon )
    {
      if( caretIcon.classList.contains('bi-caret-down') ) {
        caretIcon.classList.remove('bi-caret-down');
        caretIcon.classList.add('bi-caret-up');
      }
      else {
        caretIcon.classList.remove('bi-caret-up');
        caretIcon.classList.add('bi-caret-down');
      }
    }

    // Toggle visibility of the nutrition widgets section
    const nutritionWidgets = this.root.querySelector('.nutrition-widgets');
    if( nutritionWidgets )
      nutritionWidgets.style.display = nutritionWidgets.style.display === 'none' ? '' : 'none';
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
}
