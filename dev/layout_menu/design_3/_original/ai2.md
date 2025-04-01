# Responsive Bootstrap 5.3 Layout with Dynamic Navigation

## Technology Stack
- Bootstrap 5.3
- Bootstrap Icons
- Responsive design principles

## Navigation Requirements

### Landscape Mode (PC, tablets, or smartphones in landscape)
- **Navbar Position:** Vertical, fixed to right side
- **Design:**
  - Compact width to maximize content space
  - Two distinct icon groups:
    - Top group for secondary/utility navigation
    - Bottom group for primary navigation
  - No collapsible functionality
- **Visual Style:**
  - Background: Sophisticated dark/black theme
  - Text/Icons: Light colored
  - Icons: Bootstrap Icons library

### Portrait Mode (smartphones in portrait)
- **Navbar Position:** Horizontal, fixed to bottom
- **Design:**
  - Icons distributed evenly across the bar
  - Single row layout
  - No collapsible functionality
- **Visual Style:** 
  - Maintains same dark theme as landscape mode

## Page Layout Structure

### Landscape Mode
- **Two-Column Layout:**
  - Left column: 33.33% (1/3) of viewport width
  - Right column: 66.67% (2/3) of viewport width
- **Content Flow:** Horizontal

### Portrait Mode
- **Single-Column Layout:**
  - Columns stack vertically
  - First column (previously left) appears above
  - Second column (previously right) appears below
- **Content Flow:** Vertical

## Implementation Details
- Full responsive implementation
- Include sample content in both columns
- Use Bootstrap's grid system and utility classes
- Implement proper breakpoints for mode switching
- Ensure consistent spacing and alignment
- No JavaScript-based toggling required for navigation

## Browser Compatibility
- Support all modern browsers
- Ensure proper display on various device sizes
- Maintain layout integrity across different screen resolutions
