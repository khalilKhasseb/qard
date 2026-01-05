# Task: Inertia.js + Vue 3 Frontend Setup

**Agent:** integration-agent  
**Priority:** P0 (Critical - Foundation)  
**Estimated Time:** 2-3 hours

## Objective
Setup complete Inertia.js + Vue 3 + SSR stack for TapIt user-facing application.

## Current State
- Laravel 12 installed
- Filament admin panel working
- No Inertia.js or Vue installed
- Only Blade templates for public cards
- Tailwind CSS v4 installed via Vite

## Requirements

### 1. Install Laravel Breeze with Inertia Vue Stack
```bash
composer require laravel/breeze --dev
php artisan breeze:install vue --ssr
npm install
npm run build
```

### 2. Configure Vite for Vue 3 + TypeScript
- Add TypeScript support to package.json
- Configure tsconfig.json
- Update vite.config.js for Vue 3 with SSR
- Ensure hot module replacement works

### 3. Create Base Layout (AppLayout.vue)
**Location:** `resources/js/Layouts/AppLayout.vue`

**Features:**
- Top navbar with logo
- Navigation links: Dashboard, My Cards, Themes, Analytics, Settings
- User dropdown (profile, logout)
- Mobile responsive menu
- Breadcrumbs support
- Toast notification area
- Loading indicator for Inertia requests

### 4. Setup Pinia State Management
```bash
npm install pinia
```

**Create stores:**
- `stores/auth.js` - User authentication state
- `stores/cards.js` - Business cards state
- `stores/themes.js` - Themes state
- `stores/ui.js` - UI state (modals, toasts, loading)

### 5. Create Reusable Components

**Component Library Location:** `resources/js/Components/`

**Required Components:**
1. **Button.vue** - Primary, secondary, danger variants
2. **Card.vue** - Container with header, body, footer slots
3. **Input.vue** - Text input with label and error message
4. **TextArea.vue** - Multi-line text input
5. **Select.vue** - Dropdown with options
6. **ColorPicker.vue** - Color selection with hex input
7. **FileUpload.vue** - File upload with drag-drop
8. **Modal.vue** - Reusable modal dialog
9. **Toggle.vue** - On/off switch
10. **Badge.vue** - Status badges
11. **Spinner.vue** - Loading spinner
12. **Alert.vue** - Success, error, warning, info alerts

### 6. Configure SSR
- Setup `resources/js/ssr.js`
- Configure Vite for SSR build
- Test SSR rendering
- Verify SEO meta tags work

### 7. Update package.json Scripts
```json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build && vite build --ssr",
    "preview": "vite preview"
  }
}
```

## Deliverables

1. ✅ Inertia.js + Vue 3 installed and configured
2. ✅ SSR working (test with `node bootstrap/ssr/ssr.js`)
3. ✅ AppLayout.vue with navigation
4. ✅ Pinia stores setup
5. ✅ 12 reusable components created
6. ✅ TypeScript configured
7. ✅ Vite HMR working
8. ✅ Sample dashboard page rendering

## Validation Steps

1. Run `npm run dev` - should start without errors
2. Visit `/dashboard` - should render AppLayout
3. Check HMR - modify component, should hot reload
4. Run `npm run build` - should build both client and SSR
5. Check browser console - no errors
6. Verify navigation links work

## Notes
- DO NOT create authentication pages yet - auth-agent will handle that
- Use Tailwind CSS v4 classes
- Follow Vue 3 Composition API patterns
- Ensure all components are TypeScript compatible
- Add proper prop validation

## Dependencies
None - this is the foundation task

## Next Tasks
After completion, auth-agent will implement authentication flows.
