document.addEventListener('DOMContentLoaded', function() {
  // Get the nutrition widgets container and scroll arrow
  const widgetsContainer = document.querySelector('.nutrition-widgets .overflow-auto');
  const scrollArrow = document.querySelector('.nutrition-widgets .scroll-arrow');
  
  // Track scroll position to hide arrow after user starts scrolling
  let lastScrollPosition = 0;
  let userHasScrolled = false;
  
  // Function to check if scrolling is needed and update arrow visibility
  function checkScroll() {
    // If user has manually scrolled more than 20px, hide the arrow
    if (userHasScrolled && widgetsContainer.scrollLeft > 20) {
      scrollArrow.classList.remove('visible');
      return;
    }
    
    if(widgetsContainer.scrollWidth > widgetsContainer.clientWidth) {
      // Content is wider than container, show arrow
      scrollArrow.classList.add('visible');
    } else {
      // No scrolling needed, hide arrow
      scrollArrow.classList.remove('visible');
    }
    
    // Hide arrow if scrolled to the end
    if(widgetsContainer.scrollLeft + widgetsContainer.clientWidth >= widgetsContainer.scrollWidth - 10) {
      scrollArrow.classList.remove('visible');
    }
  }
  
  // Check on load
  setTimeout(checkScroll, 100); // Small delay to ensure content is fully rendered
  
  // Check on window resize
  window.addEventListener('resize', function() {
    // Reset user scroll state on resize
    userHasScrolled = false;
    lastScrollPosition = widgetsContainer.scrollLeft;
    checkScroll();
  });
  
  // Handle scroll events
  widgetsContainer.addEventListener('scroll', function() {
    // Detect if this is a user-initiated scroll
    if (Math.abs(widgetsContainer.scrollLeft - lastScrollPosition) > 5) {
      userHasScrolled = true;
    }
    
    lastScrollPosition = widgetsContainer.scrollLeft;
    checkScroll();
  });
  
  // Handle click on arrow to scroll right
  scrollArrow.addEventListener('click', function() {
    widgetsContainer.scrollBy({ 
      left: 200, 
      behavior: 'smooth' 
    });
    
    // This is not a user-initiated scroll
    setTimeout(function() {
      lastScrollPosition = widgetsContainer.scrollLeft;
    }, 500);
  });
});
