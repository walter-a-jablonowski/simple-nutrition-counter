document.addEventListener('DOMContentLoaded', function() {
  // Get the nutrition widgets container and scroll arrow
  const widgetsContainer = document.querySelector('.nutrition-widgets .overflow-auto');
  const scrollArrow = document.querySelector('.nutrition-widgets .scroll-arrow');
  
  // Function to check if scrolling is needed and update arrow visibility
  function checkScroll() {
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
  window.addEventListener('resize', checkScroll);
  
  // Handle scroll events to hide arrow when at the end
  widgetsContainer.addEventListener('scroll', checkScroll);
  
  // Handle click on arrow to scroll right
  scrollArrow.addEventListener('click', function() {
    widgetsContainer.scrollBy({ 
      left: 200, 
      behavior: 'smooth' 
    });
  });
});
