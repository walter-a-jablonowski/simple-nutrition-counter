/*

Standalone tests for the food grid fold-all menu (no browser, no dependencies).
Run from the `src` directory:  node tools/test_fold_all.mjs

Covers the case that made this necessary: the first "Fold all" / "Unfold all"
click after a page load took seconds. The old implementation ran every group
through Bootstrap's collapse animation at once - one whole-grid reflow per
frame over content never painted before. The fix flips the final state
directly on the group bodies (the same classes Bootstrap itself ends up with),
so one clean relayout replaces the animated thrash. The tests pin down that
behaviour: toggleFoldAll must work purely through the 'show' class, without
bootstrap.Collapse being available at all.

MainController is a browser script, so it is loaded through new Function() over
a fake dom built here - same approach as tools/test_voice_entries.mjs, trimmed
to what these tests need.

*/

import fs from 'fs'

let pass = 0
let fail = 0

function check( name, ok, detail = '')
{
  if( ok ) { pass++; console.log(`  PASS  ${name}`) }
  else     { fail++; console.log(`  FAIL  ${name}${ detail ? `  (${detail})` : ''}`) }
}

function eq( name, actual, expected )
{
  const same = Array.isArray( actual ) && Array.isArray( expected )
             ? actual.length === expected.length && actual.every(( v, i) => v === expected[i])
             : actual === expected

  check( name, same, `expected ${JSON.stringify(expected)}, got ${JSON.stringify(actual)}`)
}


// Fake dom
// ----------------------------------------------------------

function makeEl( props = {})
{
  const el =
  {
    dataset:     props.dataset || {},
    children:    [],
    parentNode:  null,
    isConnected: false,
    className:   '',
    innerHTML:   '',
    offsetWidth: 0,
    id:          props.id || '',
    style:       {},

    _q:       props.q       || {},   // selector -> element or array
    _closest: props.closest || {},   // selector -> element
    _attrs:   props.attrs   || {},

    classList: {
      _set: new Set( props.classes || []),
      add( ...c)    { c.forEach( x => this._set.add(x)) },
      remove( ...c) { c.forEach( x => this._set.delete(x)) },
      contains( c)  { return this._set.has(c) },
      toggle( c, on) { on ? this._set.add(c) : this._set.delete(c) }
    },

    querySelector( sel)    { const r = el._q[sel]; return Array.isArray(r) ? (r[0] ?? null) : (r ?? null) },
    querySelectorAll( sel) { const r = el._q[sel]; return Array.isArray(r) ? r : (r ? [r] : []) },
    find( sel)             { return el.querySelector( sel) || makeEl() },
    closest( sel)          { return el._closest[sel] || null },
    getAttribute( name)    { return el._attrs[name] ?? null },
    setAttribute( name, v) { el._attrs[name] = v },

    addEventListener() {},
    event() {},
    focus() {}
  }

  let text = ''

  Object.defineProperty( el, 'textContent', {
    get: () => text,
    set: v => { text = String( v ?? ''); el.innerHTML = text }
  })

  if( props.textContent !== undefined )
    el.textContent = props.textContent

  return el
}

// The page slice the tests need: three group bodies in the food grid, everything
// else resolves to a blank stub

function loadController( initialStates )
{
  const bodies = initialStates.map(( state, i) =>
    makeEl({ id: `group${i}Collapse`, classes: state === 'shown' ? ['collapse', 'show'] : ['collapse'] }))

  const label = makeEl({ id: 'foldAllLabel', textContent: 'Fold all' })
  const icon  = makeEl({ id: 'foldAllIcon',  classes: ['bi'] })

  const document =
  {
    querySelectorAll( sel)
    {
      sel = sel.replace(/\s+/g, ' ').trim()

      if( sel === '#layout .tab-content .collapse')  return bodies
      if( sel === '#layout .collapse.mobile-fold')   return []

      return [ makeEl()]   // unknown selector: a blank, harmless node
    },

    getElementById: () => makeEl({ textContent: '{}' }),
    createElement:  () => makeEl(),
    activeElement:  null,
    addEventListener() {}
  }

  globalThis.document  = document
  globalThis.window    = { location: { search: '' }, addEventListener(){},
                           matchMedia: () => ({ matches: false }) }
  function ModalStub()    { this.show = () => {}; this.hide = () => {}; this._element = makeEl() }
  function PopoverStub()  { this.show = () => {}; this.hide = () => {} }

  PopoverStub.getInstance = () => null

  // Deliberately no Collapse stub: the fold-all path must not depend on it

  globalThis.bootstrap = { Modal: ModalStub, Popover: PopoverStub }
  globalThis.ajax      = { send: () => {} }
  globalThis.event     = () => {}
  globalThis.PointerSortable = function() {}   // drag and drop of the entries list, nothing these tests touch

  globalThis.query = function( sel, returnSingle = false )
  {
    sel = sel.replace(/\s+/g, ' ').trim()

    if( sel.charAt(0) === '#' && sel.indexOf(' ') === -1 )
    {
      if( sel === '#foldAllLabel' ) return label
      if( sel === '#foldAllIcon' )  return icon

      return document.querySelectorAll( sel )[0] ?? makeEl()
    }

    const r = document.querySelectorAll( sel )

    return returnSingle && r.length === 1 ? r[0] : r
  }

  globalThis.queryOne = globalThis.queryFirst = sel => globalThis.query( sel, true)
  globalThis.queryAll = sel => globalThis.query( sel, false)

  const src  = fs.readFileSync('MainController.js', 'utf8')
  const Ctor = new Function(`${src}\n; return MainController`)()

  const crl = new Ctor()

  crl.updSummary = () => {}   // paints widgets all over the page, says nothing here

  return { crl, bodies, label, icon }
}

const statesOf = bodies => bodies.map( b => b.classList.contains('show'))


// Tests
// ----------------------------------------------------------

console.log('\nThe reported bug: the first fold-all click took seconds\n')
{
  // On mobile every group starts folded (#foldGroupsOnMobile), so "Unfold all"
  // is the first thing the menu does after a page load

  const { crl, bodies } = loadController([ 'folded', 'folded', 'folded' ])

  crl.toggleFoldAll()

  eq('unfold all shows every group', statesOf( bodies), [true, true, true])

  crl.toggleFoldAll()

  eq('fold all hides every group again', statesOf( bodies), [false, false, false])
}

console.log('\nMixed state folds everything\n')
{
  const { crl, bodies } = loadController([ 'shown', 'folded', 'shown' ])

  crl.toggleFoldAll()

  eq('any unfolded group -> all folded', statesOf( bodies), [false, false, false])
}

console.log('\nThe menu entry knows what the next click will do\n')
{
  const { crl, label, icon, bodies } = loadController([ 'folded', 'folded' ])

  crl.updFoldMenu()
  eq('all folded -> offers unfolding', label.textContent, 'Unfold all')
  check('expand icon', icon.classList.contains('bi-arrows-expand'))

  crl.toggleFoldAll()

  crl.updFoldMenu()
  eq('anything unfolded -> offers folding', label.textContent, 'Fold all')
  check('collapse icon', icon.classList.contains('bi-arrows-collapse'))

  eq('updFoldMenu did not touch the groups', statesOf( bodies), [true, true])
}

console.log(`\n${pass} passed, ${fail} failed`)
process.exit( fail ? 1 : 0 )
