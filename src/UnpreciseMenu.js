// Custom drop-up / drop-down menu for the unprecise toggles (no Bootstrap).
// The panel stays open while items are toggled and closes on outside click or Escape.
// Item clicks themselves are handled by mainCrl.toggleUnprecise() (inline onclick).

class UnpreciseMenu
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
    const trigger = event.target.closest('.unprecise-trigger')
    const menu    = trigger ? trigger.closest('.unprecise-menu') : null

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

    if( ! event.target.closest('.unprecise-panel'))  // inside the panel: keep open for multiple toggles
      this.closeAll()
  }

  _onKeyDown( event )
  {
    if( event.key === 'Escape')
      this.closeAll()
  }

  _onReflow()
  {
    document.querySelectorAll('.unprecise-menu.open').forEach( menu => this.place( menu ))
  }

  /*@

  The panel is position:fixed so it never widens a scrolling ancestor (the left
  column is narrower than the panel), which means we anchor it to its trigger
  ourselves and keep it inside the window.

  */
  place( menu ) /*@*/
  {
    const panel   = menu.querySelector('.unprecise-panel')
    const trigger = menu.querySelector('.unprecise-trigger').getBoundingClientRect()
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
    document.querySelectorAll('.unprecise-menu.open').forEach( menu => {

      if( menu === except )
        return

      menu.classList.remove('open')
      menu.querySelector('.unprecise-trigger').setAttribute('aria-expanded', 'false')
    })
  }
}
