// Custom drop-up / drop-down menu for the unprecise toggles (no Bootstrap).
// The panel stays open while items are toggled and closes on outside click or Escape.
// Item clicks themselves are handled by mainCrl.toggleUnprecise() (inline onclick).

class UnpreciseMenu
{
  constructor( root = document )
  {
    this.root = root

    this._onDocClick = this._onDocClick.bind(this)
    this._onKeyDown  = this._onKeyDown.bind(this)

    this.root.addEventListener('click', this._onDocClick)
    document.addEventListener('keydown', this._onKeyDown)
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
