// The voice agent's overlay
//
// Voice stays the main channel - the hands are busy, that is the point of the agent - so
// this is only an aid for lists too long to hold in your head. The agent asks out loud
// either way; here the options can also be read and tapped.
//
// One host, one renderer per content type, so a later tool adds a type and nothing else.
// It knows nothing about foods: the caller passes ready-made rows and gets the picked id
// back. See dev_info/Voice_Logging_Plan.md

class AgentOverlayController
{
  constructor()
  {
    this.modal  = new bootstrap.Modal( query('#agentOverlay'))
    this.onPick = null

    // Content types. Each returns a node for the body - adding one is adding a line here

    this.renderers = {
      choice: spec => this.#renderChoice( spec ),
      info:   spec => this.#renderInfo( spec )
    }
  }

  /*@

  choose()

  A list the user can tap while the agent asks about it out loud.

  ARGS:

    spec:   { title, items: [{ id, title, sub }] }
    onPick: called with the id of the tapped row

  */
  choose( spec, onPick ) /*@*/
  {
    this.onPick = onPick

    this.#show('choice', spec)
  }

  // Read-only content, for tools that just want to put something on screen

  info( spec )
  {
    this.onPick = null

    this.#show('info', spec)
  }

  hide()
  {
    this.onPick = null

    this.modal.hide()
  }

  // Wired in the markup, so the rows need no listener bookkeeping. The callback runs after
  // the overlay is gone, so whatever it triggers can open a new one

  pick( event )
  {
    const id       = event.currentTarget.dataset.id
    const answered = this.onPick

    this.hide()

    if( answered )
      answered( id )
  }

  #show( type, spec )
  {
    const render = this.renderers[type]

    if( ! render )
      return

    query('#agentOverlayTitle').textContent = spec.title || ''
    query('#agentOverlayBody').replaceChildren( render( spec ))

    this.modal.show()
  }

  // Built as dom, not as html: the rows carry food names the user wrote, and text nodes
  // can't turn a name with a quote or a bracket in it into markup

  #renderChoice( spec )
  {
    const list = document.createDocumentFragment()

    ;(spec.items || []).forEach( item => {

      const button = document.createElement('button')

      button.type            = 'button'
      button.className       = 'agent-choice'
      button.dataset.id      = item.id
      button.onclick         = event => this.pick( event )

      const title = document.createElement('span')

      title.className   = 'agent-choice-title'
      title.textContent = item.title

      button.appendChild( title )

      if( item.sub )
      {
        const sub = document.createElement('span')

        sub.className   = 'agent-choice-sub'
        sub.textContent = item.sub

        button.appendChild( sub )
      }

      list.appendChild( button )
    })

    return list
  }

  #renderInfo( spec )
  {
    const box = document.createElement('div')

    box.className = 'agent-overlay-info'
    box.innerHTML = spec.html || ''   // our own markup, never anything the model wrote

    return box
  }
}
