/*

The nutrition advisor's panel.

Opens from the nav, asks the server what the last weeks were short of, and shows the
answer next to the numbers it was built on - so the advice can always be checked against
the app's own figures rather than taken on trust.

Everything the model wrote is put on the page as text, never as markup. The food names in
it are the user's own and can hold anything; the sentences around them come from a model.
Same rule as AgentOverlayController.

The two row actions go through MainController: "Show" is the jump the voice agent's
findFood does, and logging taps the grid's own amount button, so a recommendation is
logged by exactly the path a finger would take. See dev_info/Nutrition_Advisor_Plan.md

*/
class AdvisorController
{
  constructor()
  {
    const el = query('#advisorModal')

    if( ! el )  return   // advisor off: the markup is not rendered

    this.modal   = new bootstrap.Modal( el )
    this.body    = query('#advisorBody')
    this.data    = null
    this.running = false
  }


  // From the nav. A cached answer comes back at once, otherwise the server asks the model

  open( event )
  {
    if( event )
      event.preventDefault()

    this.modal.show()

    if( ! this.data && ! this.running )
      this.#run( false )
    else if( this.data )
      this.#render( this.data )
  }


  // Ask again, ignoring what was cached for this day

  refresh()
  {
    if( ! this.running )
      this.#run( true )
  }


  /*@

  Log one recommendation by tapping the grid's own amount button.

  Not logFoodAmount(): the advisor names an amount the food actually offers ("1/3", "100g"),
  and clicking the button reproduces the entry the user would get, label and all, without
  turning that label back into a value and a unit here

  */
  logFood( name, amount, button ) /*@*/
  {
    const rec = this.#recordFor( name )
    const btn = rec && Array.from( rec.itemEl.querySelectorAll('.amount-btn'))
                            .find( b => b.dataset.amountLabel === amount )

    if( ! btn )
    {
      this.#say( button, 'not found', false)
      return
    }

    btn.click()

    this.#say( button, 'logged', true)
  }


  // Close first: jumpToFood scrolls, and the grid is behind the modal

  showFood( name )
  {
    const rec = this.#recordFor( name )

    if( ! rec )
      return

    query('#advisorModal').event('hidden.bs.modal', () => {

      if( typeof widgetsCrl !== 'undefined' && widgetsCrl )
        widgetsCrl.switchToNav('day')      // the grid may not be the open section

      mainCrl.jumpToFood( rec )

    }, { once: true })

    this.modal.hide()
  }


  // The grid record of an exact food name, the same lookup the agent's tools do

  #recordFor( name )
  {
    const wanted = (name || '').trim().toLowerCase()

    return mainCrl.findFoods( name ).find( rec => rec.food.toLowerCase() === wanted ) || null
  }


  #run( refresh )
  {
    this.running = true

    this.#renderStatus('Reading your last weeks …', true)

    // The model needs 20-40 s, so say something before the user gives up

    const watchdog = setTimeout(() => this.#renderStatus('Still thinking, this can take a minute …', true), 15000)

    ajax.send('getAdvice', { step: 'analyse', date: mainCrl.date, refresh: refresh ? 1 : 0 }, (result, data) => {

      clearTimeout( watchdog )
      this.running = false

      if( result !== 'success')
      {
        this.#renderStatus((data && data.message) || 'Could not read the advice', false)
        return
      }

      this.data = data
      this.#render( data )
    })
  }


  #renderStatus( text, busy )
  {
    const box = document.createElement('div')

    box.className = 'advisor-status text-center py-4'

    if( busy )
    {
      const spinner = document.createElement('div')

      spinner.className = 'spinner-border spinner-border-sm me-2'
      box.appendChild( spinner )
    }

    box.appendChild( document.createTextNode( text ))

    this.body.replaceChildren( box )
  }


  #render( data )
  {
    const advice = data.advice || {}
    const report = data.report || {}
    const out    = document.createDocumentFragment()

    out.appendChild( this.#rangeLine( report, data.cached ))

    if( advice.summary )
      out.appendChild( this.#el('p', 'advisor-summary', advice.summary ))

    // What is short, with the numbers the advice was built on next to each one

    if( advice.deficits && advice.deficits.length )
      out.appendChild( this.#nutrientSection('What is short', advice.deficits, report.deficits || [],
                       row => `${this.#num(row.perDay)} of ${this.#num(row.ideal)} ${row.unit} a day`
                            + (row.coverage < 100 ? `, measured from ${row.coverage} % of the calories` : '')))

    if( advice.excesses && advice.excesses.length )
      out.appendChild( this.#nutrientSection('More than needed', advice.excesses, report.excesses || [],
                       row => `${this.#num(row.perDay)} ${row.unit} a day, the range ends at ${this.#num(row.upper)}`))

    if( advice.recommended && advice.recommended.length )
      out.appendChild( this.#foodSection('Foods to use today', advice.recommended, true))

    if( advice.avoid && advice.avoid.length )
      out.appendChild( this.#foodSection('Where the excess comes from', advice.avoid, false))

    if( advice.dataNotes && advice.dataNotes.length )
    {
      const notes = this.#section('About the data')

      advice.dataNotes.forEach( note => notes.appendChild( this.#el('p', 'advisor-note', note )))
      out.appendChild( notes )
    }

    if( data.warnings && data.warnings.length )
    {
      const box = this.#section('Left out')

      data.warnings.forEach( text => box.appendChild( this.#el('p', 'advisor-warning', text )))
      out.appendChild( box )
    }

    this.body.replaceChildren( out )
  }


  #rangeLine( report, cached )
  {
    const parts = []

    if( report.range )
      parts.push(`${report.range}, ${report.daysWithData} of ${report.days} days logged`)

    if( cached )
      parts.push('from the last run')

    return this.#el('p', 'advisor-range', parts.join('  ·  '))
  }


  /* One nutrient per row: what the model said about it, and the app's own figure under
     it. The figures are looked up by name, so a nutrient the model named but the report
     does not carry simply shows without numbers rather than breaking the row */

  #nutrientSection( title, notes, figures, describe )
  {
    const box  = this.#section( title )
    const byName = {}

    figures.forEach( row => byName[ row.nutrient ] = row )

    notes.forEach( note => {

      const row = document.createElement('div')
      row.className = 'advisor-row'

      row.appendChild( this.#el('span', 'advisor-nutrient', note.nutrient ))

      const figure = byName[ note.nutrient ]

      if( figure )
        row.appendChild( this.#el('span', 'advisor-figure', describe( figure )))

      if( note.comment )
        row.appendChild( this.#el('div', 'advisor-comment', note.comment ))

      box.appendChild( row )
    })

    return box
  }


  #foodSection( title, foods, withActions )
  {
    const box = this.#section( title )

    foods.forEach( food => {

      const row = document.createElement('div')
      row.className = 'advisor-row advisor-food'

      const head = document.createElement('div')
      head.className = 'advisor-food-head'

      head.appendChild( this.#el('span', 'advisor-food-name', food.food ))

      if( food.amount )
        head.appendChild( this.#el('span', 'advisor-amount', food.amount ))

      if( withActions )
      {
        head.appendChild( this.#button('Show', 'btn-outline-secondary', () => this.showFood( food.food )))

        if( food.amount )
          head.appendChild( this.#button('Log', 'btn-outline-secondary',
                            (event, btn) => this.logFood( food.food, food.amount, btn )))
      }

      row.appendChild( head )

      if( food.because && food.because.length )
        row.appendChild( this.#el('div', 'advisor-because', food.because.join(', ')))

      if( food.comment )
        row.appendChild( this.#el('div', 'advisor-comment', food.comment ))

      box.appendChild( row )
    })

    return box
  }


  // Say what a tap did on the button itself - a phone has no other place for it

  #say( button, text, ok )
  {
    if( ! button )  return

    const was = button.textContent

    button.textContent = text
    button.disabled    = true
    button.classList.toggle('btn-outline-success', ok)

    setTimeout(() => {
      button.textContent = was
      button.disabled    = false
      button.classList.remove('btn-outline-success')
    }, 1800)
  }


  #section( title )
  {
    const box = document.createElement('div')

    box.className = 'advisor-section'
    box.appendChild( this.#el('div', 'advisor-section-title', title ))

    return box
  }


  #button( text, variant, onClick )
  {
    const btn = document.createElement('button')

    btn.type      = 'button'
    btn.className = `btn btn-sm ${variant} advisor-btn`
    btn.textContent = text
    btn.onclick   = event => onClick( event, btn )

    return btn
  }


  // textContent, never innerHTML: the food names are the user's own and the sentences
  // around them were written by a model

  #el( tag, className, text )
  {
    const node = document.createElement( tag )

    node.className   = className
    node.textContent = text

    return node
  }


  #num( value )
  {
    const n = Number( value )

    if( ! isFinite(n) || n === 0 )  return '0'
    if( Math.abs(n) >= 10 )         return String( Math.round( n ))
    if( Math.abs(n) >= 1 )          return String( Math.round( n * 10) / 10)

    return String( Number( n.toPrecision(2)))
  }
}
