// Custom drop-up / drop-down menu (no Bootstrap), shared by the unprecise
// toggles and the devMode menu. One instance handles every .drop-menu on the
// page; the items' own actions stay with their feature (inline onclick).
//
// Markup: .drop-menu[data-dir="up|down"] > .drop-menu-trigger + .drop-menu-panel
// Add data-close-on-select to close the panel after an item was clicked;
// without it the panel stays open (toggle menus, where several clicks are normal).

class DropMenu
{
  constructor( root = document )
  {
    this.root   = root
    this.gap    = 6   // px between trigger and panel
    this.margin = 8   // px the panel keeps from the window edges

    this._onDocClick = this._onDocClick.bind(this)
    this._onKeyDown  = this._onKeyDown.bind(this)
    this._onReflow   = this._onReflow.bind(this)

    this.root.addEventListener('click', this._onDocClick)
    document.addEventListener('keydown', this._onKeyDown)
    window.addEventListener('resize', this._onReflow)
    window.addEventListener('scroll', this._onReflow, true)   // capture: inner scroll containers too
  }

  _onDocClick( event )
  {
    const trigger = event.target.closest('.drop-menu-trigger')
    const menu    = trigger ? trigger.closest('.drop-menu') : null

    if( menu )
    {
      event.preventDefault()
      const open = ! menu.classList.contains('open')

      this.closeAll( menu )

      menu.classList.toggle('open', open)
      trigger.setAttribute('aria-expanded', open ? 'true' : 'false')

      if( open )
        this.place( menu )

      return
    }

    const panel = event.target.closest('.drop-menu-panel')

    if( ! panel )   // outside click
      this.closeAll()
    else if( event.target.closest('.drop-menu-item') && panel.closest('.drop-menu').hasAttribute('data-close-on-select'))
      this.closeAll()
  }

  _onKeyDown( event )
  {
    if( event.key === 'Escape')
      this.closeAll()
  }

  _onReflow()
  {
    document.querySelectorAll('.drop-menu.open').forEach( menu => this.place( menu ))
  }

  /*@

  The panel is position:fixed so it never widens a scrolling ancestor (the left
  column is narrower than the panel), which means we anchor it to its trigger
  ourselves and keep it inside the window.

  */
  place( menu ) /*@*/
  {
    const panel   = menu.querySelector('.drop-menu-panel')
    const trigger = menu.querySelector('.drop-menu-trigger').getBoundingClientRect()
    const isUp    = menu.dataset.dir === 'up'

    // Drop-up opens above the trigger, drop-down below it

    const top = isUp ? trigger.top - panel.offsetHeight - this.gap
                     : trigger.bottom + this.gap

    // The drop-up starts at its trigger, the drop-down (header) ends at it

    let left = isUp ? trigger.left : trigger.right - panel.offsetWidth

    left = Math.min( left, window.innerWidth - panel.offsetWidth - this.margin )
    left = Math.max( this.margin, left )

    panel.style.top  = Math.round( Math.max( this.margin, top )) + 'px'
    panel.style.left = Math.round( left ) + 'px'
  }

  closeAll( except = null )
  {
    document.querySelectorAll('.drop-menu.open').forEach( menu => {

      if( menu === except )
        return

      menu.classList.remove('open')
      menu.querySelector('.drop-menu-trigger').setAttribute('aria-expanded', 'false')
    })
  }
}
