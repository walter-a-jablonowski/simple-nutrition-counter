/*

Standalone tests for the advisor's panel (see AdvisorController.js).

Run from the `src` directory:

  php tools/test_advisor.php        first - it writes the payload this reads
  node tools/test_advisor_panel.mjs

The payload is a contract written in two languages: php builds it in ajax/get_advice.php,
javascript reads it here. Nothing else runs both, so this renders the real payload through
the real controller against a fake dom and looks at what came out.

What it watches above all: every sentence in that payload was written by a model and every
food name belongs to the user, so none of it may reach the page as markup.

*/

import { readFileSync } from 'fs'

let pass = 0
let fail = 0

function check( name, ok, detail = '' )
{
  if( ok ) { pass++; console.log(`  PASS  ${name}`) }
  else     { fail++; console.log(`  FAIL  ${name}${detail ? `  (${detail})` : ''}`) }
}

/* A dom that does just what the renderer touches. Keeping it this small is the point:
   anything the controller needs beyond it would be a dependency worth knowing about */

function makeEl( tag = 'div' )
{
  const el = {
    tagName: tag, className: '', _text: '', children: [], disabled: false, type: '',
    onclick: null,

    classList: {
      toggle( name, on ) { on ? el.classList.add( name ) : el.classList.remove( name ) },
      add( name )    { if( ! el.className.split(' ').includes(name)) el.className = (el.className + ' ' + name).trim() },
      remove( name ) { el.className = el.className.split(' ').filter( c => c !== name ).join(' ') }
    },

    appendChild( child ) { el.children.push( child ); return child },
    replaceChildren( ...nodes ) { el.children = nodes.flatMap( n => n && n._fragment ? n.children : [n]).filter(Boolean) },

    get textContent() { return el._text },
    set textContent( value ) { el._text = String( value ); el.children = [] }
  }

  return el
}

globalThis.document = {
  createElement: makeEl,
  createTextNode: text => { const n = makeEl('#text'); n._text = String(text); return n },
  createDocumentFragment: () => { const f = makeEl('#fragment'); f._fragment = true; return f }
}

const body   = makeEl()
const modal  = makeEl()
let   hidden = null

modal.event = ( name, fn ) => { if( name === 'hidden.bs.modal') hidden = fn }

globalThis.query = sel => sel === '#advisorBody' ? body : modal

let shown = 0
globalThis.bootstrap = { Modal: class { show() { shown++ } hide() { if( hidden ) hidden() } } }

let sent = null
globalThis.ajax = { send: ( id, data, cb ) => { sent = { id, data }; globalThis._cb = cb } }

let jumped  = null
let clicked = null

// A few more grid foods so a whole menu can be logged

const logged = []

function gridFood( name, label )
{
  const btn = makeEl()
  btn.dataset = { amountLabel: label }
  btn.click   = () => { logged.push( name ); if( name === 'Brokkoli R') clicked = label }

  const item = makeEl()
  item.querySelectorAll = () => [ btn ]

  return { food: name, itemEl: item }
}

const grid = [ gridFood('Brokkoli R', '100g'), gridFood('Linsen R Bio', '1/3'),
               gridFood('Olivenöl', '15ml'),   gridFood('Knoblauch R', '1') ]

globalThis.mainCrl = {
  date: '2026-08-25',
  findFoods: name => grid.filter( rec => rec.food.toLowerCase().includes( name.toLowerCase())),
  jumpToFood: rec => jumped = rec.food
}

globalThis.widgetsCrl = { switchToNav: () => {} }

// The controller, loaded the way the browser gets it

new Function( readFileSync('AdvisorController.js', 'utf8') + '\n; globalThis.AdvisorController = AdvisorController')()

// Everything the rendered panel says, flattened

function text( node = body )
{
  return [ node._text, ...node.children.map( text )].filter( Boolean).join(' ')
}

function walk( node = body, out = [] )
{
  out.push( node )
  node.children.forEach( child => walk( child, out ))
  return out
}


const payload = JSON.parse( readFileSync('tools/advisor/payload_good.json', 'utf8'))
const crl     = new AdvisorController()

// 1) Opening asks the server once, with the day the app shows

crl.open()

check('modal is shown',   shown === 1)
check('asks for advice',  sent && sent.id === 'getAdvice', JSON.stringify( sent ))
check('asks for the open day', sent.data.date === '2026-08-25' && sent.data.step === 'analyse')
check('says it is working',    text().includes('Reading your last weeks'), text())

// 2) The answer

globalThis._cb('success', payload )

const shown_ = text()

check('summary is shown',   shown_.includes('Über 30 Tage'), shown_.slice(0, 80))
check('range line',         shown_.includes('30days') && shown_.includes('28 of 30 days logged'), shown_.slice(0, 120))

check('deficit named',      shown_.includes('Fibre'))
check('deficit comment',    shown_.includes('18 g statt 40 g'))
check('deficit figures from the report', shown_.includes('17 of 40 g a day'), shown_)
check('coverage only when partial', ! shown_.includes('measured from 100 %'), shown_)

check('excess named',       shown_.includes('Vitamin B12') && shown_.includes('Vitamin C'), shown_.slice(0, 200))

/* The model named two nutrients the hand made report does not carry, so those rows show
   the comment and no figures. Better than a row that breaks, or one that invents them */

check('a nutrient with no figure still shows', shown_.includes('Kommt fast vollständig aus den B-Präparaten'), shown_)
check('and shows no figure for it',            ! shown_.includes('the range ends at'), shown_)

check('recommendation',     shown_.includes('Brokkoli R') && shown_.includes('100g'))
check('because line',       shown_.includes('Fibre, Calcium, Vitamin K'))
check('data note',          shown_.includes('Quark R'))
check('dropped food is reported', shown_.includes('Gibt es nicht'), 'warnings section')

// The invented food must not appear as a recommendation, only in the warning

const recRows = walk().filter( n => n.className.includes('advisor-food-name'))

check('only real foods are rows', recRows.every( n => n._text !== 'Gibt es nicht'),
      recRows.map( n => n._text).join(', '))

// 3) Nothing the model wrote may become markup

const markup = walk().filter( n => n._text && /<[a-z/]/i.test( n._text))

check('no node holds markup', markup.length === 0, markup.map( n => n._text).join(' | '))

// 4) The row actions go through MainController

const buttons = walk().filter( n => n.tagName === 'button')
const logBtn  = buttons.find( n => n._text === 'Log')
const showBtn = buttons.find( n => n._text === 'Show')

check('a log and a show button', !! logBtn && !! showBtn, buttons.map( n => n._text).join(', '))

logBtn.onclick( null, logBtn )

check('logging taps the grid button', clicked === '100g', String( clicked ))
check('the button says so',           logBtn._text === 'logged', logBtn._text)

showBtn.onclick( null, showBtn )

check('showing jumps to the food', jumped === 'Brokkoli R', String( jumped ))

// 4c) The menus: asked for, rendered, and logged as a whole

const menuBtn = walk().filter( n => n.tagName === 'button').find( n => n._text === 'Make menus')

check('a make menus button', !! menuBtn, walk().filter( n => n.tagName === 'button').map( n => n._text).join(', '))

menuBtn.onclick( null, menuBtn )

check('asks for menus', sent.data.step === 'menus' && sent.data.refresh === 0, JSON.stringify( sent.data ))

globalThis._cb('success', JSON.parse( readFileSync('tools/advisor/menus_payload.json', 'utf8')))

const withMenus = text()

check('menu title shown',   withMenus.includes('Linsenbowl mit Brokkoli'), withMenus.slice(-400))
check('ingredients shown',  withMenus.includes('Linsen R Bio') && withMenus.includes('Olivenöl'))
check('why shown',          withMenus.includes('Deckt Ballaststoffe'))
check('instructions shown', withMenus.includes('Brokkoli dämpfen'))
check('added ones marked',  withMenus.includes('for taste'), withMenus.slice(-300))

// The core ingredients carry no "for taste", so the two are told apart on screen

const tasteRows = walk().filter( n => n.className.includes('advisor-taste'))
const coreRows  = walk().filter( n => n.className.includes('advisor-core'))

// Three menus: 2 + 2 in the first, one core each in the other two

check('core and taste rows differ', coreRows.length === 4 && tasteRows.length === 2,
      `${coreRows.length} core, ${tasteRows.length} taste`)

// Asking again re-rolls rather than returning the same ones

const againBtn = walk().filter( n => n.tagName === 'button').find( n => n._text === 'Other menus')

check('the button now re-rolls', !! againBtn)

againBtn.onclick( null, againBtn )
check('re-roll asks for fresh menus', sent.data.refresh === 1, JSON.stringify( sent.data ))

globalThis._cb('success', JSON.parse( readFileSync('tools/advisor/menus_payload.json', 'utf8')))

// Logging a whole menu taps every ingredient's own grid button

logged.length = 0

const logAll = walk().filter( n => n.tagName === 'button').find( n => n._text === 'Log all')

logAll.onclick( null, logAll )

check('every ingredient logged', logged.length === 4, logged.join(', '))
check('the button says so',      logAll._text === 'logged', logAll._text)

// 4b) When the report does carry the nutrient, its figures are printed

crl.data = null
crl.running = false
crl.open()

globalThis._cb('success', {
  advice: { summary: '', dataNotes: [], deficits: [], recommended: [], avoid: [],
            excesses: [{ nutrient: 'Salt', comment: 'Comes from the sausage.' }] },
  report: { range: '30days', days: 30, daysWithData: 28,
            deficits: [], excesses: [{ nutrient: 'Salt', unit: 'g', perDay: 9, upper: 6 }] },
  warnings: [], cached: true
})

const withFigure = text()

check('excess figures',      withFigure.includes('9 g a day, the range ends at 6'), withFigure)
check('a cached run says so', withFigure.includes('from the last run'), withFigure)

// 5) An error reaches the screen instead of an empty panel

crl.running = false
crl.data    = null
crl.open()
globalThis._cb('error', { message: 'Not enough logged yet.' })

check('error is shown', text().includes('Not enough logged yet.'), text())

console.log(`\n  ${pass} passed, ${fail} failed`)

process.exit( fail ? 1 : 0)
