/*

Standalone tests for the voice agent's day entry tools (no browser, no dependencies).
Run from the `src` directory:  node tools/test_voice_entries.mjs

Covers the case that made this necessary: a bowl logged in several logFoods calls, then a
correction to the *first* ingredient. That used to undo the last call and append a second
row; updateEntry has to change the one entry and leave the rest alone.

MainController is a browser script, so it is loaded through new Function() over a fake dom
built here. The fake is deliberately dumb: document.querySelectorAll answers from a
registry of selectors instead of matching css, and any selector nobody registered gets a
blank stub - enough for the dom-heavy constructor to run without describing a whole page.

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
  check( name, actual === expected, `expected ${JSON.stringify(expected)}, got ${JSON.stringify(actual)}`)
}


// Fake dom
// ----------------------------------------------------------

const escapeHtml = s => String(s).replace(/[&<>"']/g,
  c => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c]))

// An element carries its own answers for querySelector / closest, so a test says what a
// node returns instead of the fake having to understand selectors

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

    _q:       props.q       || {},   // selector -> element or array
    _closest: props.closest || {},   // selector -> element
    _attrs:   props.attrs   || {},

    classList: {
      _set: new Set(),
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
    focus() {},
    scrollIntoView() {},
    insertAdjacentHTML() {},

    appendChild( child)
    {
      child.parentNode  = el
      child.isConnected = true
      el.children.push( child)
      return child
    },

    replaceWith( next)
    {
      const at = el.parentNode ? el.parentNode.children.indexOf( el) : -1

      if( at === -1 )
        return

      next.parentNode  = el.parentNode
      next.isConnected = true
      el.parentNode.children[at] = next

      el.parentNode  = null
      el.isConnected = false
    },

    remove()
    {
      const at = el.parentNode ? el.parentNode.children.indexOf( el) : -1

      if( at !== -1 )
        el.parentNode.children.splice( at, 1)

      el.parentNode  = null
      el.isConnected = false
    }
  }

  // #esc() escapes by round tripping through a div

  let text = ''

  Object.defineProperty( el, 'textContent', {
    get: () => text,
    set: v => { text = String( v ?? ''); el.innerHTML = escapeHtml( text) }
  })

  if( props.textContent !== undefined )
    el.textContent = props.textContent

  return el
}

// One food row of the grid, with the amount buttons the scaling reads

function makeFood({ name, unit = 'g', packWeight = 0, pieces = 0, vendor = '', buttons })
{
  const btns = buttons.map( b => makeEl({ dataset: {
    food:              name,
    category:          'F',
    amountLabel:       b.label,
    amountWeight:      String( b.weight),
    calories:          String( b.calories),
    price:             String( b.price ?? 0),
    nutritionalvalues: JSON.stringify({ fat: b.fat ?? 0, carbs: b.carbs ?? 0, amino: b.amino ?? 0,
                                        salt: 0, fibre: 0, sugar: 0 }),
    fattyacids: '{}', aminoacids: '{}', vitamins: '{}', minerals: '{}', secondary: '{}', misc: '{}'
  }}))

  const header = makeEl({ textContent: 'Group' })
  const col    = makeEl({ q: { '.group-header div': header }})
  const pane   = makeEl({ id: 'mealsLayoutPane' })

  return makeEl({
    dataset: { vendor, foodWeight: String( packWeight), foodPieces: String( pieces), foodUnit: unit },
    q:       { '.amount-btn': btns },
    closest: { '.tab-pane[id$="LayoutPane"]': pane, '[class*="col-md-6"]': col }
  })
}

// The whole page the tests need. Everything else resolves to a blank stub

function buildPage( foods )
{
  const list = makeEl({ id: 'dayEntriesList', dataset: { currencyIcon: 'bi-currency-euro' }})
  const tab  = makeEl({ textContent: 'Meals', attrs: { href: '#mealsLayoutPane' }})

  const registry =
  {
    '#dayEntriesList':                                    [list],
    '#dayEntriesList .day-entry':                         () => list.children,
    '#layout .layout-item':                               foods,
    '#layout .nav-pills .nav-link[data-bs-toggle="tab"]': [tab],
    '#layout .tab-content':                               [makeEl()]
  }

  const document =
  {
    querySelectorAll( sel)
    {
      const hit = registry[ sel.replace(/\s+/g, ' ').trim() ]

      if( hit )
        return typeof hit === 'function' ? hit() : hit

      return [ makeEl({ textContent: '{}' })]   // unknown selector: a blank, harmless node
    },

    getElementById: () => makeEl({ textContent: '{}' }),
    createElement:  () => makeEl(),
    activeElement:  null,
    addEventListener() {}
  }

  return { list, document }
}

// Load MainController.js as the browser would, over the stubs

function loadController( foods )
{
  const { list, document } = buildPage( foods )

  // Called with new, so these have to be constructors, not arrow functions

  function ModalStub()   { this.show = () => {}; this.hide = () => {}; this._element = makeEl() }
  function PopoverStub() { this.show = () => {}; this.hide = () => {} }

  PopoverStub.getInstance = () => null

  // Drag and drop of the entries list (lib/pointer_sortable.js), nothing the tools touch
  function PointerSortableStub() {}

  globalThis.PointerSortable = PointerSortableStub

  globalThis.document  = document
  globalThis.window    = { location: { search: '' }, addEventListener(){} }
  globalThis.bootstrap = { Modal: ModalStub, Popover: PopoverStub }
  globalThis.ajax      = { send: () => {} }            // #saveDayEntries must not reach the server
  globalThis.event     = () => {}                      // global event() helper from the frm lib
  globalThis.dayEntries = []

  globalThis.query = function( sel, returnSingle = false )
  {
    sel = sel.replace(/\s+/g, ' ').trim()
    const r = document.querySelectorAll( sel )

    if( sel.charAt(0) === '#' && sel.indexOf(' ') === -1 )
      return r[0]

    return returnSingle && r.length === 1 ? r[0] : r
  }

  globalThis.queryOne = globalThis.queryFirst = sel => globalThis.query( sel, true)
  globalThis.queryAll = sel => globalThis.query( sel, false)

  // The real one, so #serializeDayEntries writes what the server would be sent

  const yamlish = fs.readFileSync('lib/frm/YAMLish_241016.js', 'utf8')

  globalThis.YAMLish = new Function(`${yamlish}\n; return YAMLish`)()

  const src  = fs.readFileSync('MainController.js', 'utf8')
  const Ctor = new Function(`${src}\n; return MainController`)()

  const crl = new Ctor()

  // The day summary paints widgets all over the page and says nothing about entry
  // handling - the tests read the list itself

  crl.updSummary = () => {}

  globalThis.mainCrl = crl

  return { crl, list }
}


// The foods the tests log
// ----------------------------------------------------------

function testFoods()
{
  return [
    makeFood({ name: 'Gemüse R Bio', vendor: 'Rewe', packWeight: 600,
               buttons: [{ label: '50g', weight: 50, calories: 20.5 }, { label: '100g', weight: 100, calories: 41 }]}),
    makeFood({ name: 'Reis', packWeight: 500,
               buttons: [{ label: '100g', weight: 100, calories: 130 }]}),
    makeFood({ name: 'Knoblauch R', packWeight: 80, pieces: 8,
               buttons: [{ label: '1', weight: 10, calories: 15 }]})
  ]
}

const rows      = list => list.children.map( li => li.dataset.food )
const amountOf  = li   => (JSON.parse( li.dataset.nutrients || '{}').amount || {}).label
const entryFor  = ( crl, food) => crl.listDayEntries().entries.find( e => e.food === food )


// Tests
// ----------------------------------------------------------

console.log('\nThe reported bug: correcting the first of several ingredients\n')
{
  const { crl, list } = loadController( testFoods())

  // Two calls on purpose - that is what made undoLastLog hit the wrong ingredient

  crl.logFoods([{ food: 'Gemüse R Bio', value: 100, unit: 'g' },
                { food: 'Reis',         value: 100, unit: 'g' }])
  crl.logFoods([{ food: 'Knoblauch R',  value: 1,   unit: 'piece' }])

  eq('three ingredients logged', list.children.length, 3)

  const before = entryFor( crl, 'Gemüse R Bio')
  const result = crl.updateEntry( before.id, 200, 'g')

  eq('updateEntry reports updated', result.result, 'updated')
  eq('  .. says what it was',       result.from,   '100g')
  eq('  .. says what it is now',    result.label,  '200g')
  eq('  .. rescales the calories',  result.calories, 82)

  eq('still three entries, nothing added', list.children.length, 3)
  check('the other two are untouched',
        rows( list).join(' | ') === 'Gemüse R Bio | Reis | Knoblauch R',
        rows( list).join(' | '))
  eq('the corrected entry kept its place', amountOf( list.children[0]), '200g')
  eq('the vegetables are logged once', rows( list).filter( f => f === 'Gemüse R Bio').length, 1)
  eq('the last ingredient survived',   amountOf( list.children[2]), '1 pc')
}

// Why the prompt must not offer undo + log as a repair. This is the old path, and it still
// does exactly what it did - the damage is in the approach, not in a bug of undoLastLog

console.log('\nThe old repair path, for comparison\n')
{
  const { crl, list } = loadController( testFoods())

  crl.logFoods([{ food: 'Gemüse R Bio', value: 100, unit: 'g' },
                { food: 'Reis',         value: 100, unit: 'g' }])
  crl.logFoods([{ food: 'Knoblauch R',  value: 1,   unit: 'piece' }])

  crl.undoLastLog()                                                 // takes the wrong one
  crl.logFoods([{ food: 'Gemüse R Bio', value: 200, unit: 'g' }])   // appends, never replaces

  eq('an ingredient is lost',    rows( list).filter( f => f === 'Knoblauch R').length, 0)
  eq('and one is logged twice',  rows( list).filter( f => f === 'Gemüse R Bio').length, 2)
  eq('the wrong amount remains', amountOf( list.children[0]), '100g')
}

console.log('\nThe entry keeps its identity\n')
{
  const { crl, list } = loadController( testFoods())

  crl.logFoods([{ food: 'Reis', value: 100, unit: 'g' }])

  const li     = list.children[0]
  const time   = li.dataset.time
  const before = crl.listDayEntries().entries[0]

  crl.updateEntry( before.id, 250, 'g')

  eq('the time it was logged at is kept', list.children[0].dataset.time, time)
  eq('the id still points at it',         entryFor( crl, 'Reis').amount, '250g')
}

console.log('\nPieces rescale like grams\n')
{
  const { crl, list } = loadController( testFoods())

  crl.logFoods([{ food: 'Knoblauch R', value: 1, unit: 'piece' }])

  const result = crl.updateEntry( crl.listDayEntries().entries[0].id, 2, 'piece')

  eq('label follows the unit',   result.label,  '2 pc')
  eq('weight is two pieces',     result.weight, 20)
  eq('calories follow',          result.calories, 30)
  eq('one entry, not two',       list.children.length, 1)
}

console.log('\nremoveEntry takes exactly one row\n')
{
  const { crl, list } = loadController( testFoods())

  crl.logFoods([{ food: 'Gemüse R Bio', value: 100, unit: 'g' },
                { food: 'Reis',         value: 100, unit: 'g' },
                { food: 'Knoblauch R',  value: 1,   unit: 'piece' }])

  const result = crl.removeEntry( entryFor( crl, 'Reis').id )

  eq('reports what went',   result.result, 'removed')
  eq('  .. names the food', result.food,   'Reis')
  eq('  .. names the amount', result.amount, '100g')
  eq('two entries left',    list.children.length, 2)
  check('the right one went', rows( list).join(' | ') === 'Gemüse R Bio | Knoblauch R', rows( list).join(' | '))
}

console.log('\nA stale id never hits another entry\n')
{
  const { crl, list } = loadController( testFoods())

  crl.logFoods([{ food: 'Reis', value: 100, unit: 'g' }])

  const id = crl.listDayEntries().entries[0].id

  crl.removeEntry( id )

  eq('removing it again reports gone', crl.removeEntry( id ).result, 'gone')
  eq('updating it reports gone',       crl.updateEntry( id, 200, 'g').result, 'gone')
  eq('an id nobody handed out',        crl.updateEntry('e999', 200, 'g').result, 'gone')
  eq('and nothing was touched',        list.children.length, 0)
}

console.log('\nEntries the page rendered can be corrected too\n')
{
  const { crl, list } = loadController( testFoods())

  // What day_entries.php renders at page load: a row with no uid

  list.appendChild( makeEl({ dataset: {
    food: 'Reis', type: 'F', time: '12:30:00', calories: '130',
    nutrients: JSON.stringify({ amount: { label: '100g', weight: 100 }})
  }}))

  const first = crl.listDayEntries()

  eq('it is listed',        first.count, 1)
  check('and given an id',  !! first.entries[0].id, 'no id assigned')

  const id = first.entries[0].id

  eq('the id is stable',    crl.listDayEntries().entries[0].id, id)
  eq('and it can be fixed', crl.updateEntry( id, 200, 'g').label, '200g')
  eq('still one entry',     list.children.length, 1)
}

console.log('\nundoLastLog after a correction\n')
{
  const { crl, list } = loadController( testFoods())

  crl.logFoods([{ food: 'Reis', value: 100, unit: 'g' }])
  crl.updateEntry( crl.listDayEntries().entries[0].id, 200, 'g')

  const result = crl.undoLastLog()

  eq('the corrected row is taken back', result.result, 'undone')
  eq('  .. with the amount it had',     result.removed[0].amount, '200g')
  eq('the list is empty',               list.children.length, 0)
}

console.log('\nA correction cannot invent a food\n')
{
  const { crl, list } = loadController( testFoods())

  crl.logFoods([{ food: 'Reis', value: 100, unit: 'g' }])

  const id = crl.listDayEntries().entries[0].id

  eq('an absurd amount is refused', crl.updateEntry( id, 100000, 'g').result, 'error')
  eq('and the entry is unchanged',  entryFor( crl, 'Reis').amount, '100g')
  eq('still exactly one entry',     list.children.length, 1)
}


console.log(`\n${pass} passed, ${fail} failed\n`)

process.exit( fail ? 1 : 0)
