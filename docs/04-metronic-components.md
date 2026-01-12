# Metronic Theme Components & Plugins

## Overview

The application uses the **Metronic 8** premium admin theme built on **Bootstrap 5.2.2**. The theme provides comprehensive UI components, layouts, and JavaScript plugins that are deeply integrated with Vue 3.

## Component Architecture

### Core Structure

```
resources/metronic/
├── assets/                 # Theme assets (SCSS, TypeScript, images)
│   ├── sass/              # SCSS styles and variables
│   └── ts/                # TypeScript components and utilities
├── components/            # Vue components organized by category
├── core/                  # Core services and configurations
│   ├── config/           # Theme configuration files
│   ├── helpers/          # Utility functions
│   ├── plugins/          # Plugin integrations
│   └── services/         # Core services (API, JWT, etc.)
├── layouts/              # Layout components
├── router/               # Vue Router configuration
├── stores/               # Pinia state management
└── views/                # Page components
```

## JavaScript/TypeScript Components

### Core Theme Components (`assets/ts/components/`)

The theme includes several TypeScript-based interactive components that are initialized globally:

#### 1. **MenuComponent**
- **Purpose**: Handles sidebar navigation and dropdown menus
- **Features**: 
  - Accordion-style menu expansion
  - Multi-level nesting support
  - Active state management
  - Responsive behavior

#### 2. **ScrollComponent** 
- **Purpose**: Custom scrollbar implementation
- **Features**:
  - Custom-styled scrollbars
  - Smooth scrolling animations
  - Mobile touch support
  - Auto-hide functionality

#### 3. **StickyComponent**
- **Purpose**: Makes elements stick during scroll
- **Features**:
  - Dynamic sticky positioning
  - Configurable offset values
  - Responsive breakpoints
  - CSS class management

#### 4. **ToggleComponent**
- **Purpose**: Toggle functionality for collapsible elements
- **Features**:
  - Show/hide animations
  - State persistence
  - Multiple target support
  - Event callbacks

#### 5. **DrawerComponent**
- **Purpose**: Side drawer/offcanvas functionality
- **Features**:
  - Slide-in animations
  - Backdrop overlay
  - ESC key support
  - Touch gestures

#### 6. **SwapperComponent**
- **Purpose**: Dynamic content switching
- **Features**:
  - Smooth transitions
  - Multiple target support
  - Conditional visibility
  - Animation timing controls

### Component Initialization

Components are initialized in `core/plugins/keenthemes.ts`:

```typescript
const initializeComponents = () => {
    ThemeModeComponent.init();
    setTimeout(() => {
        ToggleComponent.bootstrap();
        StickyComponent.bootstrap();
        MenuComponent.bootstrap();
        ScrollComponent.bootstrap();
        DrawerComponent.bootstrap();
        SwapperComponent.bootstrap();
    }, 0);
};
```

## Vue Components Library

### 1. **Data Tables**

#### Magic Datatable (`components/magic-datatable/`)
Advanced data table component with extensive features:

**Core Features:**
- **Server-side Pagination**: Efficient large dataset handling
- **Advanced Filtering**: Multi-column filtering with various operators
- **Sorting**: Multi-column sorting capabilities
- **Column Management**: Dynamic show/hide columns
- **Export Functions**: CSV/PDF export capabilities
- **Global Search**: Intelligent search across all columns
- **Bulk Actions**: Mass operations on selected rows

**Component Structure:**
```vue
<MagicDatatable 
    :config="datatableConfig"
    :store="indexStore"
    :module="moduleConfig"
/>
```

**Sub-components:**
- `ColumnChooser.vue`: Column visibility controls
- `GlobalSearch.vue`: Search functionality
- `DatatableActions.vue`: Row action buttons
- `ConfigDropdown.vue`: Table configuration menu
- `FilterControls/`: Various filter input types

#### KT DataTable (`components/kt-datatable/`)
Simpler table component for basic use cases:
- Standard pagination
- Basic sorting
- Simple filtering
- Loading states

### 2. **Widget System**

#### Dashboard Widgets (`components/dashboard-default-widgets/`)
Pre-built dashboard components (Widget1.vue - Widget10.vue):
- **Widget1**: Sales summary with charts
- **Widget2**: Revenue statistics  
- **Widget3**: Activity timeline
- **Widget4**: Performance metrics
- **Widget5**: Recent orders
- **Widget6**: Customer statistics
- **Widget7**: Product analytics
- **Widget8**: Revenue charts
- **Widget9**: Target progress
- **Widget10**: Team performance

#### Specialized Widget Categories

**Charts Widgets** (`components/widgets/charts/`):
- Line charts, bar charts, pie charts
- ApexCharts integration
- Real-time data updates
- Interactive legends

**Statistics Widgets** (`components/widgets/statistics/`):
- KPI display components
- Progress indicators
- Comparison metrics
- Trend indicators

**Lists Widgets** (`components/widgets/lists/`):
- Activity feeds
- Recent items
- User lists
- Task lists

**Mixed Widgets** (`components/widgets/mixed/`):
- Complex multi-element widgets
- Combined chart and data displays
- Custom business logic components

### 3. **Form Components**

#### Modal System (`components/modals/`)

**Form Modals** (`components/modals/forms/`):
- `AddCustomerModal.vue`: Customer creation form
- `CreateAPIKeyModal.vue`: API key management
- `ExportCustomerModal.vue`: Data export options
- `NewAddressModal.vue`: Address management
- `NewCardModal.vue`: Payment card forms
- `NewEventModal.vue`: Calendar event creation

**General Modals** (`components/modals/general/`):
- `InviteFriendsModal.vue`: User invitation system
- `ShareAndEarnModal.vue`: Referral program
- `UpgradePlanModal.vue`: Subscription management
- `ViewUsersModal.vue`: User directory

**Wizard Modals** (`components/modals/wizards/`):
- `CreateAccountModal.vue`: Multi-step account creation
- `TwoFactorAuthModal.vue`: Security setup

### 4. **Layout Components**

#### Cards (`components/cards/`)
Reusable card components with consistent styling:
- `Card.vue`: Base card component
- `Card1.vue` - `Card4.vue`: Specialized card variants
- Header/body/footer sections
- Loading states
- Action buttons

#### Timeline (`components/activity-timeline-items/`)
Activity timeline components (Item1.vue - Item8.vue):
- User actions tracking
- System events logging
- Visual timeline display
- Interactive elements

### 5. **Navigation Components**

#### Menu System (`components/menu/`)
- `MenuComponent.vue`: Main navigation component
- Dynamic menu generation from configuration
- Permission-based visibility
- Breadcrumb integration

#### Dropdowns (`components/dropdown/`)
Specialized dropdown components:
- `Dropdown1.vue` - `Dropdown4.vue`: Various dropdown styles
- User account menus
- Notification panels
- Quick action menus

### 6. **Utility Components**

#### File Management (`components/files/`)
- `File.vue`: File display component
- `Folder.vue`: Directory navigation
- Upload progress indicators
- File type icons

#### Code Display (`components/highlighters/`)
- `CodeHighlighter.vue`: Syntax highlighting
- `CodeHighlighter2.vue`: Advanced code display
- Language detection
- Copy functionality

#### Messaging (`components/messenger-parts/`)
- `MessageIn.vue`: Incoming messages
- `MessageOut.vue`: Outgoing messages  
- Chat bubble styling
- Timestamp display

## Theme Configuration

### Layout Configuration (`core/config/`)

#### DefaultLayoutConfig.ts
Comprehensive layout settings:

```typescript
const DefaultLayoutConfig = {
    themeName: "Metronic",
    themeVersion: "8.2.1",
    themeMode: "dark",
    sidebar: {
        display: true,
        theme: "dark",
        minimize: false,
    },
    header: {
        display: true,
        menuIcon: "font",
    },
    toolbar: {
        display: true,
        width: "fluid",
    },
    footer: {
        display: true,
    },
};
```

#### MainMenuConfig.ts
Dynamic menu configuration loaded from common data:

```typescript
export interface MenuItem {
    heading?: string;
    sectionTitle?: string;
    route?: string;
    gate?: string;
    pages?: Array<MenuItem>;
    svgIcon?: string;
    fontIcon?: string;
    sub?: Array<MenuItem>;
}

const MainMenuConfig: Array<MenuItem> = mainMenuPages;
```

### Theme Management

#### Theme Mode (`core/layout/ThemeMode.ts`)
- Light/dark mode switching
- User preference persistence
- System theme detection
- Dynamic CSS variable updates

#### Asset Management (`core/helpers/assets.ts`)
- Dynamic asset loading
- Theme-based asset switching
- Optimization helpers
- CDN integration

## SCSS Architecture

### Theme Styling (`assets/sass/`)

#### Core Styles
- `style.scss`: Main stylesheet entry point
- `custom.scss`: Custom overrides and additions
- `plugins.scss`: Third-party plugin styles

#### Component Styles
- `components/`: Component-specific styles
- `layout/`: Layout-specific styles
- `core/`: Core theme styles

#### Variables System
- `_variables.custom.scss`: Custom color variables
- Bootstrap 5 variable overrides
- Dark/light theme variations
- Responsive breakpoint customizations

### Color System
```scss
// Primary color palette
$primary: #0066cc;
$secondary: #6c757d;
$success: #28a745;
$info: #17a2b8;
$warning: #ffc107;
$danger: #dc3545;

// Dark theme variations
$dark-bg: #1a1a1a;
$dark-surface: #2d2d2d;
$dark-border: #404040;
```

## Plugin Integrations

### Core Plugins (`core/plugins/`)

#### 1. **ApexCharts** (`apexcharts.ts`)
- Chart component integration
- Theme-aware styling
- Responsive configurations
- Real-time data updates

#### 2. **Vue i18n** (`i18n.ts`)
- Multi-language support
- Dynamic locale switching
- Translation management
- Number/date formatting

#### 3. **VeeValidate** (`vee-validate.ts`)
- Form validation integration
- Custom validation rules
- Error message handling
- Field-level validation

#### 4. **Inline SVG** (`inline-svg.ts`)
- SVG icon management
- Dynamic icon loading
- Theme-aware icons
- Performance optimization

#### 5. **Prism.js** (`prismjs.ts`)
- Code syntax highlighting
- Multiple language support
- Theme integration
- Copy functionality

## Performance Optimizations

### Component Loading
- Lazy loading for large components
- Dynamic imports for widgets
- Tree shaking unused components
- Bundle splitting

### Theme Assets
- SCSS compilation optimization
- Asset minification
- CSS purging
- Image optimization

### JavaScript Components
- Event delegation
- Efficient DOM manipulation
- Memory leak prevention
- Resize observers

## Customization Guidelines

### Adding New Components
1. Create component in appropriate category folder
2. Follow naming conventions (PascalCase for Vue, kebab-case for files)
3. Include TypeScript types
4. Add SCSS styles if needed
5. Export from index files

### Theme Modifications
1. Use custom SCSS variables for colors
2. Override Bootstrap variables before compilation
3. Create mixins for reusable styles
4. Maintain responsive design patterns

### Component Extension
1. Extend base components rather than modifying
2. Use composition over inheritance
3. Maintain backward compatibility
4. Document custom features

This Metronic theme integration provides a comprehensive UI foundation with extensive customization capabilities and modern development practices.