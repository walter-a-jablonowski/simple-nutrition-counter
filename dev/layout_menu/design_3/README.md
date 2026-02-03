
## Structure

### 1. Navigation

- **Desktop Sidebar** (`#sidebar`): Vertical navigation with icons
- **Mobile Bottom Nav** (`#bottomNav`): Horizontal bottom navigation with same functionality

### 2. Main Content Area

- **Container**: Fluid container with flex layout
- **Main Layout** (`#mainLayout`): Primary view with left/right columns
  - **Left Column** (16.67%): 
    - Main header with user select and date switcher
      - tab 3 has own header, see below
    - Mobile action buttons
    - Main content area (for day entries)
    - Desktop action buttons toolbar (duplicate)
  - **Right Column** (83.33%):
    - Nutrition widgets section with scrollable widgets
    - Food grid layout with tab content areas

### 3. Favorites Layout (`#favoritesLayout`)

- Special layout for "Last days", hidden by default (`d-none`)
- has own header section

### 4. Modals and Scripts

- BS modals for info/tips
- External CSS and JS files
- Controller instantiation
