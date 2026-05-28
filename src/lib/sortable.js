/*@

PointerSortable - drag-to-reorder a list using Pointer Events (mouse + touch + pen, one code path)

- no handle: the whole item is draggable
- touch: press-and-hold (touchDelay ms) starts the drag, so a normal touch still scrolls the list
- mouse / pen: the drag starts after a small move (moveThreshold px)
- the dragged item is lifted (position: fixed) and follows the pointer; a placeholder keeps its slot
- auto-scrolls the nearest scrollable ancestor when dragging near its top / bottom edge
- onSort() fires after a drop that actually changed the order

  new PointerSortable( query('#myList'), {
    itemSelector: '.item',
    cancel:       '.delete-btn',   // never start a drag from here (e.g. buttons)
    onSort:       () => save()
  })

*/
class PointerSortable
{
  constructor( list, options = {} )
  {
    this.list         = list
    this.itemSelector = options.itemSelector  || '> *'
    this.cancelSel    = options.cancel        || null
    this.onSort       = options.onSort        || function() {}
    this.touchDelay   = options.touchDelay    ?? 180   // ms to hold before a touch drag starts
    this.moveThresh   = options.moveThreshold ?? 6     // px before a mouse drag starts / a touch hold is cancelled
    this.edge         = options.edgeScroll    ?? 45    // px edge zone for auto-scroll
    this.edgeSpeed    = options.edgeSpeed     ?? 12    // px per frame

    this.pending = null   // candidate before we know drag vs scroll/tap
    this.drag    = null   // active drag state

    this.list.addEventListener('pointerdown', this.#onDown)
  }


  // Pointer down: remember the candidate, decide later

  #onDown = ( e ) =>
  {
    if( e.pointerType === 'mouse' && e.button !== 0 )  // left button only
      return

    const item = e.target.closest( this.itemSelector )
    if( ! item || ! this.list.contains( item))
      return

    if( this.cancelSel && e.target.closest( this.cancelSel))
      return  // e.g. the delete button

    this.pending = {
      item,
      pointerId: e.pointerId,
      startX:    e.clientX,
      startY:    e.clientY,
      touch:     e.pointerType === 'touch',
      timer:     null
    }

    window.addEventListener('pointermove',   this.#onPendingMove)
    window.addEventListener('pointerup',     this.#onPendingUp)
    window.addEventListener('pointercancel', this.#onPendingUp)

    if( this.pending.touch )  // hold to start a drag (a quick move = scroll)
      this.pending.timer = setTimeout(() => this.#start( this.pending.startX, this.pending.startY), this.touchDelay)
  }

  #onPendingMove = ( e ) =>
  {
    if( ! this.pending ) return

    const dist = Math.hypot( e.clientX - this.pending.startX, e.clientY - this.pending.startY)

    if( this.pending.touch ) {
      if( dist > this.moveThresh )   // moved during the hold -> it's a scroll, let the browser do it
        this.#clearPending()
    }
    else {
      if( dist > this.moveThresh )   // mouse / pen: a small move starts the drag
        this.#start( e.clientX, e.clientY)
    }
  }

  #onPendingUp = () => this.#clearPending()

  #clearPending()
  {
    if( this.pending && this.pending.timer )
      clearTimeout( this.pending.timer)

    this.pending = null
    window.removeEventListener('pointermove',   this.#onPendingMove)
    window.removeEventListener('pointerup',     this.#onPendingUp)
    window.removeEventListener('pointercancel', this.#onPendingUp)
  }


  // Begin dragging

  #start( clientX, clientY )
  {
    const p = this.pending
    if( ! p ) return

    const item = p.item
    const pointerId = p.pointerId
    this.#clearPending()

    this.scrollParent = this.#findScrollParent( item)

    const rect = item.getBoundingClientRect()

    const placeholder = document.createElement( item.tagName)
    placeholder.className   = 'sortable-placeholder'
    placeholder.style.height = rect.height + 'px'
    item.parentNode.insertBefore( placeholder, item)

    item.classList.add('sortable-dragging')
    item.style.position      = 'fixed'
    item.style.zIndex        = '2000'
    item.style.width         = rect.width + 'px'
    item.style.left          = rect.left + 'px'
    item.style.top           = rect.top + 'px'
    item.style.margin        = '0'
    item.style.pointerEvents = 'none'

    this.drag = {
      item,
      placeholder,
      pointerId,
      offsetX:    clientX - rect.left,
      offsetY:    clientY - rect.top,
      lastY:      clientY,
      startIndex: this.#indexOf( placeholder),
      raf:        null
    }

    document.body.classList.add('sortable-active')

    window.addEventListener('pointermove',   this.#onDragMove)
    window.addEventListener('pointerup',     this.#onDragUp)
    window.addEventListener('pointercancel', this.#onDragUp)
    window.addEventListener('touchmove',     this.#blockTouch, { passive: false })  // stop native scroll while dragging

    this.#position( clientX, clientY)
    this.#startAutoScroll()
  }

  #blockTouch = ( e ) => e.preventDefault()

  #onDragMove = ( e ) =>
  {
    if( ! this.drag ) return

    this.drag.lastY = e.clientY
    this.#position( e.clientX, e.clientY)
    this.#reorder( e.clientY)
  }

  #position( clientX, clientY )
  {
    const d = this.drag
    d.item.style.left = (clientX - d.offsetX) + 'px'
    d.item.style.top  = (clientY - d.offsetY) + 'px'
  }

  #reorder( clientY )
  {
    const d      = this.drag
    const target = this.#siblings().find( el => {
      const r = el.getBoundingClientRect()
      return clientY < r.top + r.height / 2
    })

    if( target )
      this.list.insertBefore( d.placeholder, target)
    else
      this.list.appendChild( d.placeholder)
  }

  #onDragUp = () =>
  {
    const d = this.drag
    if( ! d ) return

    this.#stopAutoScroll()

    d.item.classList.remove('sortable-dragging')
    d.item.style.position      = ''
    d.item.style.zIndex        = ''
    d.item.style.width         = ''
    d.item.style.left          = ''
    d.item.style.top           = ''
    d.item.style.margin        = ''
    d.item.style.pointerEvents = ''

    this.list.insertBefore( d.item, d.placeholder)
    d.placeholder.remove()

    document.body.classList.remove('sortable-active')

    window.removeEventListener('pointermove',   this.#onDragMove)
    window.removeEventListener('pointerup',     this.#onDragUp)
    window.removeEventListener('pointercancel', this.#onDragUp)
    window.removeEventListener('touchmove',     this.#blockTouch, { passive: false })

    const changed = this.#indexOf( d.item) !== d.startIndex
    this.drag = null

    if( changed )
      this.onSort()
  }


  // Auto-scroll the scroll parent near its edges

  #startAutoScroll()
  {
    const step = () => {
      if( ! this.drag ) return

      const sp = this.scrollParent
      const y  = this.drag.lastY
      let top, bottom

      if( sp === document.scrollingElement || sp === document.documentElement ) {
        top = 0;  bottom = window.innerHeight
      }
      else {
        const r = sp.getBoundingClientRect()
        top = r.top;  bottom = r.bottom
      }

      if( y < top + this.edge )
        sp.scrollTop -= this.edgeSpeed
      else if( y > bottom - this.edge )
        sp.scrollTop += this.edgeSpeed

      this.drag.raf = requestAnimationFrame( step)
    }
    this.drag.raf = requestAnimationFrame( step)
  }

  #stopAutoScroll()
  {
    if( this.drag && this.drag.raf )
      cancelAnimationFrame( this.drag.raf)
  }


  // Helpers

  #siblings()
  {
    const match = this.itemSelector === '> *' ? '*' : this.itemSelector
    return Array.from( this.list.children).filter( el =>
      el !== this.drag.item && el !== this.drag.placeholder && el.matches( match)
    )
  }

  #indexOf( el )
  {
    return Array.from( this.list.children).indexOf( el)
  }

  #findScrollParent( el )
  {
    let p = el.parentElement
    while( p ) {
      const oy = getComputedStyle( p).overflowY
      if(( oy === 'auto' || oy === 'scroll') && p.scrollHeight > p.clientHeight)
        return p
      p = p.parentElement
    }
    return document.scrollingElement || document.documentElement
  }
}
