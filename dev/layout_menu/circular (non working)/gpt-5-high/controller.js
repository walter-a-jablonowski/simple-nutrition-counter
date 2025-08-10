(() => {
  'use strict'

  const $ = s => document.querySelector(s)
  const menuEl = $('#radialMenu')
  const level1El = $('#level1')
  const level2El = $('#level2')
  const fabEl = $('#fab')
  const sectorEl = $('#sector')
  const refChk = $('#refChk')
  const refPane = $('#refPane')

  // Model tuned to match reference proportions
  const MENU = [
    { id: 'search', icon: '🔍', label: 'Search', badge: 2, children: [
      { id: 'by-name', icon: '🔠', label: 'By name' },
      { id: 'by-tag', icon: '🏷️', label: 'By tag' },
      { id: 'recent', icon: '🕒', label: 'Recent' }
    ]},
    { id: 'food', icon: '🍎', label: 'Food', children: [
      { id: 'add', icon: '➕', label: 'Add food' },
      { id: 'fav', icon: '⭐', label: 'Favorites' },
      { id: 'list', icon: '📋', label: 'My list' }
    ]},
    { id: 'water', icon: '💧', label: 'Water', children: [
      { id: 'cup', icon: '🥤', label: 'Add cup' },
      { id: 'bottle', icon: '🧴', label: 'Add bottle' }
    ]},
    { id: 'workout', icon: '🏃', label: 'Workout', children: [
      { id: 'start', icon: '▶️', label: 'Start' },
      { id: 'plan', icon: '🗓️', label: 'Plan' },
      { id: 'stats', icon: '📈', label: 'Stats' }
    ]},
    { id: 'sleep', icon: '😴', label: 'Sleep', children: [
      { id: 'log', icon: '📝', label: 'Log' },
      { id: 'alarm', icon: '⏰', label: 'Alarm' }
    ]},
    { id: 'settings', icon: '⚙️', label: 'Settings', children: [
      { id: 'prefs', icon: '🛠️', label: 'Preferences' },
      { id: 'theme', icon: '🎨', label: 'Theme' }
    ]}
  ]

  const STATE = {
    open: false,
    activeIndex: -1,
    level1Angles: []
  }

  // angles chosen to place L1 in the top-left quadrant similar to the reference
  const CONFIG = {
    r1: cssVar('--r1', 184),
    r2: cssVar('--r2', 120),
    arc1Start: 12,   // deg: left (0) -> up (90); keep strictly top-left
    arc1End: 88,     // ensures items stay between left and up
    arc2Span: 72     // secondary spread centered on parent
  }

  function cssVar(name, fallback) {
    const v = getComputedStyle(document.documentElement).getPropertyValue(name).trim()
    const n = parseFloat(v)
    return Number.isFinite(n) ? n : fallback
  }

  function toTranslate(r, deg) {
    const rad = (deg * Math.PI) / 180
    const x = -r * Math.cos(rad)
    const y = -r * Math.sin(rad)
    return { x, y }
  }

  function renderLevel1() {
    level1El.innerHTML = ''
    STATE.level1Angles = []

    const n = MENU.length
    for( let i = 0; i < n; i++ ) {
      const t = n === 1 ? 0.5 : i / (n - 1)
      const deg = CONFIG.arc1Start + t * (CONFIG.arc1End - CONFIG.arc1Start)
      STATE.level1Angles.push(deg)

      const { x, y } = toTranslate(CONFIG.r1, deg)
      const item = document.createElement('button')
      item.className = 'item l1'
      item.type = 'button'
      item.style.setProperty('--tx', `${x}px`)
      item.style.setProperty('--ty', `${y}px`)
      item.innerHTML = `
        <span class="icon">${MENU[i].icon}</span>
        ${MENU[i].badge ? `<span class=badge>${MENU[i].badge}</span>` : ''}
      `
      item.addEventListener('click', () => onLevel1Click(i, item))
      level1El.appendChild(item)
    }
  }

  function renderLevel2(parentIdx) {
    level2El.innerHTML = ''
    const parent = MENU[parentIdx]
    const children = parent.children || []
    if( children.length === 0 ) return

    const center = STATE.level1Angles[parentIdx]
    const start = center - CONFIG.arc2Span / 2
    const end = center + CONFIG.arc2Span / 2

    for( let i = 0; i < children.length; i++ ) {
      const t = children.length === 1 ? 0.5 : i / (children.length - 1)
      const deg = start + t * (end - start)
      const { x, y } = toTranslate(CONFIG.r2, deg)

      const item = document.createElement('button')
      item.className = 'item l2'
      item.type = 'button'
      item.style.setProperty('--tx', `${x}px`)
      item.style.setProperty('--ty', `${y}px`)
      item.innerHTML = `
        <span class=icon>${children[i].icon || '•'}</span>
        <span class=label>${children[i].label || ''}</span>
      `
      item.addEventListener('click', () => onLevel2Click(children[i], parentIdx))
      level2El.appendChild(item)
    }
  }

  function onLevel1Click(index, el) {
    if( ! STATE.open ) openMenu()

    if( STATE.activeIndex === index ) {
      clearActive()
      STATE.activeIndex = -1
      level2El.innerHTML = ''
      menuEl.classList.remove('level-2-open')
      updateSector(null)
      return
    }

    STATE.activeIndex = index
    setActive(el)
    renderLevel2(index)
    menuEl.classList.add('level-2-open')
    updateSector(STATE.level1Angles[index])
  }

  function onLevel2Click(node, parentIndex) {
    console.log('choose', node, 'from', MENU[parentIndex])
    closeMenu()
  }

  function setActive(el) {
    level1El.querySelectorAll('.item.l1').forEach(b => b.classList.remove('active'))
    el.classList.add('active')
  }
  function clearActive() {
    level1El.querySelectorAll('.item.l1').forEach(b => b.classList.remove('active'))
  }

  function openMenu() {
    menuEl.classList.add('open')
    STATE.open = true
  }
  function closeMenu() {
    menuEl.classList.remove('open', 'level-2-open')
    STATE.open = false
    STATE.activeIndex = -1
    clearActive()
    level2El.innerHTML = ''
    updateSector(null)
  }

  function updateSector(centerDeg) {
    // Convert our polar degrees (0: left, 90: up) to CSS conic degrees (0: right, 90: down)
    const toCssDeg = d => (180 + d) % 360
    if( centerDeg == null ) {
      const c = toCssDeg(45) // default center ≈ top-left
      sectorEl.style.setProperty('--sector-from', `${c - 40}deg`)
      sectorEl.style.setProperty('--sector-to', `${c + 40}deg`)
      return
    }
    const c = toCssDeg(centerDeg)
    sectorEl.style.setProperty('--sector-from', `${c - 40}deg`)
    sectorEl.style.setProperty('--sector-to', `${c + 40}deg`)
  }

  function setupFab() {
    fabEl.addEventListener('click', () => {
      if( STATE.open ) closeMenu()
      else openMenu()
    })
  }

  function setupOutsideClose() {
    document.addEventListener('click', e => {
      if( ! STATE.open ) return
      if( menuEl.contains(e.target) ) return
      closeMenu()
    })
  }

  function setupRefToggle() {
    refChk?.addEventListener('change', () => {
      if( refChk.checked ) refPane.classList.add('show')
      else refPane.classList.remove('show')
    })
  }

  function init() {
    renderLevel1()
    setupFab()
    setupOutsideClose()
    setupRefToggle()
    updateSector(null)
  }

  document.addEventListener('DOMContentLoaded', init)
})()
