// Tablet detection function

function isTablet()
{
  const userAgent = navigator.userAgent.toLowerCase()
  const screenWidth = window.screen.width
  const screenHeight = window.screen.height
  const minDimension = Math.min(screenWidth, screenHeight)
  const maxDimension = Math.max(screenWidth, screenHeight)
  
  // Check for tablet user agents
  const isTabletUA = /ipad|android(?!.*mobile)|tablet|kindle|silk|playbook|bb10/i.test(userAgent)
  
  // Check for tablet screen dimensions (typically 768px - 1366px)
  const isTabletSize = minDimension >= 768 && maxDimension <= 1366
  
  // Additional check for touch capability
  const hasTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0
  
  return isTabletUA || (isTabletSize && hasTouch)
}

// Error overlay functionality for tablets

function createErrorOverlay()
{
  if( ! isTablet() ) return
  
  // Create overlay container
  const overlay = document.createElement('div')
  overlay.id = 'jsErrorOverlay'
  overlay.style.cssText = `
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    z-index: 9999;
    display: none;
    justify-content: center;
    align-items: center;
    font-family: monospace;
  `
  
  // Create error content container
  const errorContent = document.createElement('div')
  errorContent.style.cssText = `
    background-color: #fff;
    border: 2px solid #dc3545;
    border-radius: 8px;
    padding: 20px;
    max-width: 90%;
    max-height: 80%;
    overflow-y: auto;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  `
  
  // Create header
  const header = document.createElement('div')
  header.style.cssText = `
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #dee2e6;
  `
  
  const title = document.createElement('h4')
  title.textContent = 'JavaScript Error (Tablet Debug)'
  title.style.cssText = 'margin: 0; color: #dc3545;'
  
  const closeBtn = document.createElement('button')
  closeBtn.textContent = '×'
  closeBtn.style.cssText = `
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #6c757d;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
  `
  closeBtn.onclick = () => overlay.style.display = 'none'
  
  header.appendChild(title)
  header.appendChild(closeBtn)
  
  // Create error message container
  const errorMsg = document.createElement('div')
  errorMsg.id = 'errorMessage'
  errorMsg.style.cssText = `
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 15px;
    font-size: 14px;
    line-height: 1.5;
    white-space: pre-wrap;
    word-break: break-word;
  `
  
  errorContent.appendChild(header)
  errorContent.appendChild(errorMsg)
  overlay.appendChild(errorContent)
  document.body.appendChild(overlay)
  
  return overlay
}

// Global error handler for tablets
function setupTabletErrorHandler()
{
  if( ! isTablet() ) return
  
  const overlay = createErrorOverlay()
  if( ! overlay ) return
  
  // Handle uncaught JavaScript errors
  window.addEventListener('error', function(event) {
    const errorMsg = document.getElementById('errorMessage')
    if( errorMsg )
    {
      const errorInfo = `Error: ${event.error?.message || event.message}

File: ${event.filename || 'Unknown'}
Line: ${event.lineno || 'Unknown'}
Column: ${event.colno || 'Unknown'}

Stack Trace:
${event.error?.stack || 'Not available'}

Timestamp: ${new Date().toLocaleString()}`
      
      errorMsg.textContent = errorInfo
      overlay.style.display = 'flex'
    }
  })
  
  // Handle unhandled promise rejections
  window.addEventListener('unhandledrejection', function(event) {
    const errorMsg = document.getElementById('errorMessage')
    if( errorMsg )
    {
      const errorInfo = `Unhandled Promise Rejection:

Reason: ${event.reason}

Timestamp: ${new Date().toLocaleString()}`
      
      errorMsg.textContent = errorInfo
      overlay.style.display = 'flex'
    }
  })
}
