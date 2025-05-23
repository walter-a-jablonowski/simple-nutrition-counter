/**
 * Replacement for popover in modals (cause of problems)
 * 
 * MOV (Modal Overlay View) - A lightweight library for creating overlay tooltips
 * 
 * This library provides a simple way to create tooltips that work in any context,
 * including inside modals, tables, and other complex UI elements.
 */

/**
 * Show an overlay info tooltip near the specified element
 * 
 * @param   {Event}   event                The click event
 * @param   {Object}  options              Optional configuration
 * @param   {string}  options.content      HTML content to display (will use data-content attribute if not provided)
 * @param   {string}  options.position     Preferred position (auto, top, right, bottom, left)
 * @param   {string}  options.tooltipId    ID to use for the tooltip element
 * @param   {number}  options.zIndex       z-index for the tooltip
 * @param   {boolean} options.closeOnClick Whether to close when clicking outside
 * @returns {HTMLElement} The created tooltip element
 */
function showOverlayInfo(event, options = {}) {
  // Default options
  const defaults = {
    content: null,
    position: 'auto',
    tooltipId: 'overlay-info-tooltip',
    zIndex: 9999,
    closeOnClick: true
  }
  
  // Merge defaults with provided options
  const config = { ...defaults, ...options }
  
  // Get the clicked element
  const clickedElement = event.currentTarget
  
  // Check if tooltip already exists and remove it if it does
  const existingTooltip = document.getElementById(config.tooltipId)
  if( existingTooltip ) {
    existingTooltip.remove()
    return null
  }
  
  // Get tooltip content from options or data-content attribute
  const tooltipContent = config.content || clickedElement.getAttribute('data-content') || `
    <div class="p-2">
      No content provided<br>
      <small class="text-muted">Click anywhere to close</small>
    </div>
  `
  
  // Create tooltip element
  const tooltip = document.createElement('div')
  tooltip.id = config.tooltipId
  tooltip.className = 'popover popover-cus bs-popover-auto fade show'
  tooltip.setAttribute('role', 'tooltip')
  tooltip.style.position = 'absolute'
  tooltip.style.zIndex = config.zIndex
  tooltip.innerHTML = `
    <div class="popover-arrow"></div>
    <div class="popover-body">${tooltipContent}</div>
  `
  
  // Add to document body to get dimensions
  document.body.appendChild(tooltip)
  
  // Get dimensions
  const rect = clickedElement.getBoundingClientRect()
  const tooltipRect = tooltip.getBoundingClientRect()
  const viewportWidth = window.innerWidth
  const viewportHeight = window.innerHeight
  
  // Check if data-position is specified
  const preferredPosition = clickedElement.getAttribute('data-position') || config.position
  
  // Calculate available space in each direction
  const spaceRight = viewportWidth - rect.right
  const spaceLeft = rect.left
  const spaceTop = rect.top
  const spaceBottom = viewportHeight - rect.bottom
  
  // Determine best position
  let position
  
  if( preferredPosition !== 'auto' ) {
    // Use preferred position if specified
    position = preferredPosition
  } else {
    // Auto-determine best position based on available space
    const positions = [
      { pos: 'right', space: spaceRight },
      { pos: 'left', space: spaceLeft },
      { pos: 'bottom', space: spaceBottom },
      { pos: 'top', space: spaceTop }
    ]
    
    // Sort positions by available space (descending)
    positions.sort((a, b) => b.space - a.space)
    
    // Use position with most space
    position = positions[0].pos
  }
  
  // Set arrow class for proper direction
  const arrow = tooltip.querySelector('.popover-arrow')
  if( arrow ) {
    arrow.className = 'popover-arrow'
    arrow.classList.add(`position-${position}`)
  }
  
  // Position tooltip based on determined position
  switch( position ) {
    case 'right':
      tooltip.style.top = `${rect.top + window.scrollY + (rect.height / 2) - (tooltipRect.height / 2)}px`
      tooltip.style.left = `${rect.right + window.scrollX + 10}px`
      break
    case 'left':
      tooltip.style.top = `${rect.top + window.scrollY + (rect.height / 2) - (tooltipRect.height / 2)}px`
      tooltip.style.left = `${rect.left + window.scrollX - tooltipRect.width - 10}px`
      break
    case 'top':
      tooltip.style.top = `${rect.top + window.scrollY - tooltipRect.height - 10}px`
      tooltip.style.left = `${rect.left + window.scrollX + (rect.width / 2) - (tooltipRect.width / 2)}px`
      break
    case 'bottom':
      tooltip.style.top = `${rect.bottom + window.scrollY + 10}px`
      tooltip.style.left = `${rect.left + window.scrollX + (rect.width / 2) - (tooltipRect.width / 2)}px`
      break
  }
  
  // Add position class to tooltip
  tooltip.classList.add(`bs-popover-${position}`)
  
  // Close tooltip when clicking anywhere else
  if( config.closeOnClick ) {
    setTimeout(() => {
      document.addEventListener('click', function closeTooltip(e) {
        if( e.target !== clickedElement && !clickedElement.contains(e.target) ) {
          const tooltip = document.getElementById(config.tooltipId)
          if( tooltip ) tooltip.remove()
          document.removeEventListener('click', closeTooltip)
        }
      })
    }, 10)
  }
  
  return tooltip
}
