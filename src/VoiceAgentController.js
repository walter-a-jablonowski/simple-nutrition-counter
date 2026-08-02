// AI voice agent (Gemini Live)
//
// The mic button in the sidebar / bottom nav is the whole ui: click to talk, click again
// to stop. State is shown on the button itself, errors as a tooltip next to it (a phone
// has no console, so a failure has to reach the screen).
//
// The browser opens the Live websocket to google directly - php can't proxy a websocket -
// using a short lived token from the getAgentToken ajax call, so the api key stays on the
// server.
//
// The audio handling is ported from an agent that works on desktops and phones. Read
// dev_info/Voice_Agent_Plan.md before changing any of it, several parts look outdated
// but are exactly what mobile browsers need.

class VoiceAgentController
{
  static STATUS = {
    idle:       'Voice assistant',
    connecting: 'Voice assistant: connecting …',
    listening:  'Voice assistant: listening (click to stop)',
    speaking:   'Voice assistant: speaking (click to stop)',
    error:      'Voice assistant: failed'
  }

  constructor()
  {
    this.buttons = Array.from( query('.js-agentBtn'))   // sidebar + bottom nav

    this.ws              = null
    this.processor       = null
    this.mediaStream     = null
    this.audioContext    = null   // capture, forced to 16 kHz - what Gemini Live expects
    this.playbackContext = null   // output at the hardware rate, see start()
    this.session         = null   // token, model, voice, systemInstruction from the server

    this.state = 'idle'           // idle | connecting | listening | speaking | error
    this.debug = false            // from config.yml, agent.debug

    // Gapless playback scheduling, see playScheduled()

    this.nextStartTime = 0        // playhead on the playback context's clock
    this.activeSources = new Set()
    this.turnComplete  = true
    this.scheduleLead  = 0.08     // lead in s before the first chunk of a turn

    this.errorTimer = null
  }

  toggle(event)
  {
    if( event )
      event.preventDefault()

    if( this.state === 'idle' || this.state === 'error' )
      this.start()
    else
      this.stop()
  }

  // IMPORTANT: both audio contexts are created and resumed before the first await, so
  // they still run inside the click handler. Ios only allows that from a user gesture,
  // and everything after an await has left the gesture behind

  async start()
  {
    this.setState('connecting')

    try {
      const AudioCtx = window.AudioContext || window.webkitAudioContext

      // Two contexts on purpose: the mic has to be 16 kHz, while the model's 24 kHz
      // audio plays on a hardware rate context so it is resampled exactly once

      this.audioContext    = new AudioCtx({ sampleRate: 16000 })
      this.playbackContext = new AudioCtx()

      if( this.audioContext.state === 'suspended' )
        this.audioContext.resume()

      if( this.playbackContext.state === 'suspended' )
        this.playbackContext.resume()

      // Plain constraints: phones ignore or reject exotic ones like sampleRate
      this.mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true })

      this.session = await this.loadSession()
      this.debug   = ! ! this.session.debug

      await this.connect()

      this.sendUserTurn('Greet me in one short sentence and ask how you can help.')
      this.startCapture()

      this.setState('listening')
    }
    catch( error ) {
      // stop() resets the state, so it has to run before the error is shown
      this.stop()
      this.showError( this.errorMessage( error ))
    }
  }

  stop()
  {
    if( this.ws ) {
      this.ws.onclose = null      // an explicit stop is not a failure
      this.ws.close()
      this.ws = null
    }

    if( this.processor ) {
      this.processor.onaudioprocess = null
      this.processor.disconnect()
      this.processor = null
    }

    if( this.mediaStream ) {
      this.mediaStream.getTracks().forEach( track => track.stop())
      this.mediaStream = null
    }

    if( agentOverlay )   // a question on screen has nobody left to answer it
      agentOverlay.hide()

    this.activeSources.forEach( source => { try { source.stop() } catch(e) {} })
    this.activeSources.clear()
    this.nextStartTime = 0
    this.turnComplete  = true

    if( this.audioContext ) {
      this.audioContext.close()
      this.audioContext = null
    }

    if( this.playbackContext ) {
      this.playbackContext.close()
      this.playbackContext = null
    }

    this.setState('idle')
  }

  // Session credentials and the prompt, see ajax/get_agent_token.php

  loadSession()
  {
    return new Promise(( resolve, reject) => {

      ajax.send('getAgentToken', {}, ( result, data) => {

        if( result === 'success' && data && data.token )
          resolve( data )
        else
          reject( new Error( (data && data.message) || 'Could not get a session token'))
      })
    })
  }

  connect()
  {
    return new Promise(( resolve, reject) => {

      // An ephemeral token goes in as access_token, not as key, and only the Constrained
      // method accepts it. The plain BidiGenerateContent ignores the token and closes the
      // socket with 1008 "Method doesn't allow unregistered callers"

      const url = 'wss://generativelanguage.googleapis.com/ws/'
                + 'google.ai.generativelanguage.v1beta.GenerativeService.BidiGenerateContentConstrained'
                + '?access_token=' + encodeURIComponent( this.session.token )

      this.ws = new WebSocket( url )

      this.ws.onopen = () => {
        this.ws.send( JSON.stringify( this.buildSetup()))
        resolve()
      }

      this.ws.onerror = () => reject( new Error('Could not reach the Gemini Live api'))

      this.ws.onmessage = async event => {
        // Frames may arrive as a Blob
        const text = event.data instanceof Blob ? await event.data.text() : event.data
        this.handleMessage( text )
      }

      this.ws.onclose = event => this.handleClose( event )
    })
  }

  // Tools have to be declared here, at session start - they can't be added later

  buildSetup()
  {
    return {
      setup: {
        model: `models/${this.session.model}`,
        generationConfig: {
          responseModalities: ['AUDIO'],
          speechConfig: {
            voiceConfig: { prebuiltVoiceConfig: { voiceName: this.session.voice }}
          }
        },
        systemInstruction: { parts: [{ text: this.session.systemInstruction
                                           + '\n\n## The food list\n\n'
                                           + mainCrl.foodVocabulary() + '\n' }]},
        tools: [{ functionDeclarations: this.toolDeclarations() }]
      }
    }
  }

  // Text put into the conversation as if the user had said it. Used to open the session,
  // and for a tap on the overlay - a tap has to reach the model the same way an answer
  // does, so the model stays the only thing that acts and the transcript still adds up

  sendUserTurn( text )
  {
    if( ! this.ws || this.ws.readyState !== WebSocket.OPEN )
      return

    this.ws.send( JSON.stringify({
      clientContent: {
        turns: [{ role: 'user', parts: [{ text: text }]}],
        turnComplete: true
      }
    }))
  }

  handleMessage( data )
  {
    let message

    try {
      message = JSON.parse( data )
    }
    catch( error ) {
      this.log('Unparsable message:', data)
      return
    }

    this.log('Received:', message)

    if( message.setupComplete )
      return

    if( message.toolCall ) {
      this.handleToolCall( message.toolCall )
      return
    }

    if( message.error ) {
      this.stop()
      this.showError('Server error: ' + (message.error.message || 'unknown'))
      return
    }

    const content = message.serverContent

    if( ! content )
      return

    // Barge in: the user talked over the model, so it dropped its turn. The queued
    // audio is stale and has to go before anything from this message is scheduled

    if( content.interrupted )
      this.stopPlayback()

    const parts = content.modelTurn?.parts || []

    parts.forEach( part => {

      if( ! part.inlineData )
        return

      const mime = part.inlineData.mimeType || ''

      if( ! mime.startsWith('audio/pcm') && ! mime.startsWith('audio/wav') ) {
        this.log('Unsupported audio type:', mime)
        return
      }

      const rate = mime.match(/rate=(\d+)/)   // mime may carry it, e.g. ;rate=24000

      this.scheduleAudio( part.inlineData.data, mime, rate ? parseInt( rate[1], 10) : 24000)
    })

    // Back to listening only once the scheduled audio drained, see finishPlayback()

    if( content.turnComplete ) {
      this.turnComplete = true

      if( this.activeSources.size === 0 )
        this.setState('listening')
    }
  }

  handleClose( event )
  {
    this.log('Websocket closed:', event.code, event.reason)

    // Hints for the codes a wrong model or a bad setup message produce

    let hint = ''

    if( event.code === 1006 )      hint = 'connection lost'
    else if( event.code === 1007 ) hint = 'audio or setup format not accepted by this model'
    else if( event.code === 1008 ) hint = 'policy violation, check the api key and the model name'
    else if( event.code === 1011 ) hint = 'server error, invalid setup or model'

    // stop() resets the state, so it has to run before the error is shown

    if( this.state !== 'idle' )
      this.stop()

    if( event.code !== 1000 )
      this.showError('Connection closed (' + [event.code, event.reason, hint].filter(Boolean).join(' - ') + ')')
  }

  // Tools

  toolDeclarations()
  {
    return [
      {
        name: 'findFood',
        description: 'Find a food in the user\'s food grid and jump to it. Use it when the user '
                   + 'asks where a food is or asks to be taken to one.',
        parameters: {
          type: 'OBJECT',
          properties: {
            name: { type: 'STRING', description: 'The food name as the user said it' },
            tab:  { type: 'STRING', description: 'Optional grid tab, to pick one occurrence when the food sits on several tabs' }
          },
          required: ['name']
        }
      },
      {
        name: 'openFoodSearch',
        description: 'Open the food search overlay prefilled with a query, so the user sees all '
                   + 'matches at once and can pick one by hand.',
        parameters: {
          type: 'OBJECT',
          properties: {
            query: { type: 'STRING', description: 'What to search for' }
          },
          required: ['query']
        }
      },
      {
        name: 'logFoods',
        description: 'Log one or more foods the user ate or is cooking with into today\'s entries. '
                   + 'Use the exact food name from the food list in your instructions.',
        parameters: {
          type: 'OBJECT',
          properties: {
            items: {
              type: 'ARRAY',
              description: 'One entry per ingredient the user named',
              items: {
                type: 'OBJECT',
                properties: {
                  food:  { type: 'STRING', description: 'Exact food name from the food list' },
                  value: { type: 'NUMBER', description: 'Amount, e.g. 200 for "200g", 0.5 for "half a can"' },
                  unit:  { type: 'STRING', description: 'g | ml | piece | pack | x' }
                },
                required: ['food']
              }
            }
          },
          required: ['items']
        }
      },
      {
        name: 'showChoices',
        description: 'Put a list of foods on the screen while you ask which one is meant, so the '
                   + 'user can tap instead of listening to a long list. Ask out loud as well.',
        parameters: {
          type: 'OBJECT',
          properties: {
            title: { type: 'STRING', description: 'The question, e.g. "Welches Gemüse?"' },
            foods: {
              type: 'ARRAY',
              description: 'The exact food names to offer',
              items: { type: 'STRING' }
            }
          },
          required: ['foods']
        }
      },
      {
        name: 'undoLastLog',
        description: 'Take back the entries the last logFoods call added. Use it when the user '
                   + 'says the last logging was wrong, for example a misheard amount.',
        parameters: { type: 'OBJECT', properties: {} }
      }
    ]
  }

  // Every call must be answered or the model waits forever - the Live api has no
  // automatic tool handling

  handleToolCall( toolCall )
  {
    const calls = toolCall.functionCalls || []

    calls.forEach( call => {

      const args = call.args || {}

      // Any other call means the question was answered, by voice or by tapping

      if( call.name !== 'showChoices' && agentOverlay )
        agentOverlay.hide()

      let response

      if( call.name === 'findFood' )
        response = this.findFood( args )
      else if( call.name === 'openFoodSearch' )
        response = this.openFoodSearch( args )
      else if( call.name === 'logFoods' )
        response = this.logFoods( args )
      else if( call.name === 'showChoices' )
        response = this.showChoices( args )
      else if( call.name === 'undoLastLog' )
        response = mainCrl.undoLastLog()
      else
        response = { result: 'error', message: `Unknown tool ${call.name}` }

      this.ws.send( JSON.stringify({
        toolResponse: { functionResponses: [{ id: call.id, name: call.name, response }]}
      }))
    })
  }

  // Only plain values go back to the model, never the dom nodes the food index carries

  findFood( args )
  {
    let matches = mainCrl.findFoods( args.name )

    if( args.tab )
    {
      const wanted = args.tab.trim().toLowerCase()
      const onTab  = matches.filter( rec => rec.tabLabel.toLowerCase().includes( wanted ))

      if( onTab.length )   // a tab name we don't know shouldn't drop every match
        matches = onTab
    }

    if( ! matches.length )
      return { result: 'none' }

    const places = matches.map( rec => ({ food: rec.food, tab: rec.tabLabel, group: rec.groupName }))

    if( matches.length > 1 )
      return { result: 'multiple', matches: places }

    mainCrl.jumpToFood( matches[0])

    return { result: 'jumped', ...places[0] }
  }

  openFoodSearch( args )
  {
    mainCrl.openSearch( null, args.query || '')

    return { result: 'opened' }
  }

  // The whole spoken list arrives in one call, so one bad item can be reported back
  // without holding up the ones that were understood. Each result echoes the name that
  // was asked for, so the model can tell which ingredient it belongs to

  logFoods( args )
  {
    const items = Array.isArray( args.items) ? args.items : []

    if( ! items.length )
      return { result: 'error', message: 'No food was named' }

    const results = mainCrl.logFoods( items ).map(( result, i) => ({ said: items[i].food, ...result }))
    const logged  = results.filter( item => item.result === 'logged')

    return {
      result: logged.length === results.length ? 'ok' : 'partial',
      items:  results
    }
  }

  // The agent puts the options on screen while it asks about them out loud. This answers
  // at once: a toolCall keeps the model silent until it is answered, so waiting here for a
  // tap would freeze the conversation, and a user who never taps would hang it. The tap
  // arrives later as an ordinary user turn instead

  showChoices( args )
  {
    const names = Array.isArray( args.foods) ? args.foods : []

    if( ! names.length )
      return { result: 'error', message: 'No options were given' }

    // Only names come from the model; vendor and amounts are read off the grid, so the
    // rows can't say anything the app doesn't actually hold

    const items = names.map( name => {

      const wanted = name.trim().toLowerCase()
      const rec    = mainCrl.findFoods( name ).find( match => match.food.toLowerCase() === wanted )

      return {
        id:    name,
        title: name,
        sub:   rec ? [rec.vendor, rec.amounts.join(' | ')].filter( Boolean).join('   ') : ''
      }
    })

    agentOverlay.choose({ title: args.title || '', items: items },
                        food => this.sendUserTurn(`The user picked "${food}" from the list on screen.`))

    return { result: 'shown' }
  }

  // Microphone

  startCapture()
  {
    const source    = this.audioContext.createMediaStreamSource( this.mediaStream )
    const processor = this.audioContext.createScriptProcessor(4096, 1, 1)

    // Deprecated, but works everywhere we care about including ios safari. Both
    // connects are needed or onaudioprocess never fires in some browsers

    source.connect( processor )
    processor.connect( this.audioContext.destination )

    processor.onaudioprocess = e => {

      // Gated on listening, so the model's own voice can't feed back into the mic
      if( ! this.ws || this.ws.readyState !== WebSocket.OPEN || this.state !== 'listening' )
        return

      const samples = e.inputBuffer.getChannelData(0)
      const pcm     = new Int16Array( samples.length )

      for( let i = 0; i < samples.length; i++ )
        pcm[i] = Math.max(-32768, Math.min(32767, samples[i] * 32768))

      // realtimeInput.audio is the format Gemini 3.1 Live wants; the older
      // mediaChunks was removed and closes the socket with 1007

      this.ws.send( JSON.stringify({
        realtimeInput: {
          audio: { mimeType: 'audio/pcm;rate=16000', data: this.toBase64( pcm.buffer ) }
        }
      }))
    }

    this.processor = processor
  }

  // Playback

  scheduleAudio( base64, mime, rate )
  {
    if( ! this.playbackContext )
      return

    try {
      const bytes = this.fromBase64( base64 )

      if( mime.startsWith('audio/pcm') )
        this.playScheduled( this.pcmToBuffer( bytes, rate))
      else
        // audio/wav fallback (rare). decodeAudioData needs its own buffer, so slice
        this.playbackContext.decodeAudioData( bytes.buffer.slice(0))
          .then( buffer => this.playScheduled( buffer ))
          .catch( error => this.log('decodeAudioData failed:', error))
    }
    catch( error ) {
      this.showError('Could not play the answer: ' + error.message)
    }
  }

  // Chunks go back to back on the playback clock, so decode jitter on a slow phone
  // can't tear gaps into the speech

  playScheduled( buffer )
  {
    const ctx = this.playbackContext

    if( ! ctx )
      return

    if( this.state !== 'speaking' ) {
      this.setState('speaking')
      this.turnComplete = false
    }

    // Playhead fell behind real time (first chunk or an underrun): restart it a little
    // in the future, so scheduling has some headroom

    if( this.nextStartTime < ctx.currentTime )
      this.nextStartTime = ctx.currentTime + this.scheduleLead

    const source = ctx.createBufferSource()

    source.buffer = buffer
    source.connect( ctx.destination )
    source.start( this.nextStartTime )

    this.nextStartTime += buffer.duration

    this.activeSources.add( source )

    source.onended = () => {
      this.activeSources.delete( source )
      this.finishPlayback()
    }
  }

  // Staying in speaking through brief underruns keeps the mic gated, so the agent
  // doesn't hear itself

  finishPlayback()
  {
    if( this.activeSources.size === 0 && this.turnComplete && this.state === 'speaking' )
      this.setState('listening')
  }

  stopPlayback()
  {
    this.activeSources.forEach( source => {
      try { source.onended = null; source.stop() } catch(e) { /* already ended */ }
    })

    this.activeSources.clear()
    this.nextStartTime = 0
    this.turnComplete  = true

    if( this.state === 'speaking' )
      this.setState('listening')
  }

  // Mono AudioBuffer straight from raw 16 bit little endian pcm (no wav, no async decode)

  pcmToBuffer( bytes, rate )
  {
    const count = Math.floor( bytes.length / 2)
    const int16 = new Int16Array( bytes.buffer, bytes.byteOffset, count)

    const buffer  = this.playbackContext.createBuffer(1, count, rate)
    const channel = buffer.getChannelData(0)

    for( let i = 0; i < count; i++ )
      channel[i] = int16[i] / 32768

    return buffer
  }

  fromBase64( base64 )
  {
    const binary = atob( base64 )
    const bytes  = new Uint8Array( binary.length )

    for( let i = 0; i < binary.length; i++ )
      bytes[i] = binary.charCodeAt(i)

    return bytes
  }

  toBase64( buffer )
  {
    const bytes = new Uint8Array( buffer )
    let binary  = ''

    for( let i = 0; i < bytes.byteLength; i++ )
      binary += String.fromCharCode( bytes[i])

    return btoa( binary )
  }

  // Ui

  setState( state )
  {
    this.state = state

    const icon = state === 'error'                             ? 'bi-mic-mute-fill'
               : state === 'listening' || state === 'speaking' ? 'bi-mic-fill'
               : 'bi-mic'

    this.buttons.forEach( btn => {

      btn.classList.remove('state-connecting', 'state-listening', 'state-speaking', 'state-error')

      if( state !== 'idle' )
        btn.classList.add(`state-${state}`)

      const i = btn.querySelector('i')

      if( i )
        i.className = `bi ${icon}`

      btn.title = VoiceAgentController.STATUS[state] || ''
      btn.setAttribute('aria-label', btn.title)
    })
  }

  // A phone has no console, so failures have to be visible on screen. Reuses the
  // tooltip from lib/overlay_MOV.js, which only needs event.currentTarget

  showError( message )
  {
    console.error('Voice agent:', message)

    this.setState('error')

    // The sidebar button is hidden on mobile and the bottom nav one on desktop
    const btn = this.buttons.find( btn => btn.offsetParent !== null ) || this.buttons[0]

    if( ! btn )
      return

    const previous = document.getElementById('agentError')

    if( previous )   // showOverlayInfo toggles on a second call, so clear it first
      previous.remove()

    showOverlayInfo({ currentTarget: btn }, {
      content:      `<div class="p-2">${message}</div>`,
      tooltipId:    'agentError',
      position:     'auto',
      closeOnClick: true
    })

    clearTimeout( this.errorTimer )

    this.errorTimer = setTimeout(() => {
      const tooltip = document.getElementById('agentError')

      if( tooltip )
        tooltip.remove()

      if( this.state === 'error' )
        this.setState('idle')
    }, 8000)
  }

  errorMessage( error )
  {
    switch( error && error.name )
    {
      case 'NotAllowedError':
      case 'SecurityError':
        return 'No microphone access. Allow it for this page and try again.'
      case 'NotFoundError':
      case 'OverconstrainedError':
        return 'No microphone found.'
      case 'NotReadableError':
        return 'The microphone is used by another app.'
      default:
        return (error && error.message) || 'Could not start the voice assistant.'
    }
  }

  log(...args)
  {
    if( this.debug )
      console.log('Voice agent:', ...args)
  }
}
