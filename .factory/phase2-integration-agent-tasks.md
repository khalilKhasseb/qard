# Phase 2 - Integration Agent Tasks (Inertia/Vue Frontend)

## Context
TapIt digital business card application at C:\Users\user\Herd\qard
- Inertia.js 2.0 + Vue 3.4 already installed
- Existing pages: Welcome, Dashboard (minimal), Auth pages (complete), Profile pages
- Existing layouts: AuthenticatedLayout, GuestLayout
- Existing components: Breeze UI components (Button, TextInput, etc.)
- API endpoints will be created by api-layer-agent

## Objective
Build complete Inertia.js/Vue frontend for:
1. User Dashboard with statistics
2. Business Cards management (list, create, edit, preview)
3. Theme Editor with live preview
4. Payment/Subscription flow
5. Reusable Vue components

## Task 1: Enhanced Dashboard

### File: `resources/js/Pages/Dashboard.vue`

**Requirements**:
- Replace minimal dashboard with rich stats and quick actions
- Show:
  - Subscription status and tier (free/pro/business)
  - Card count and limits
  - Theme count and limits
  - Recent card views (last 7 days chart)
  - Quick action buttons

**Components to use**:
- StatCard component (create)
- LineChart component (create)
- QuickActionButton component (create)

**Data from backend**:
```php
// DashboardController should return:
return Inertia::render('Dashboard', [
    'stats' => [
        'cards_count' => $user->cards()->count(),
        'cards_limit' => $user->getCardLimit(),
        'themes_count' => $user->themes()->count(),
        'themes_limit' => $user->getThemeLimit(),
        'total_views' => $user->cards()->sum('views_count'),
        'subscription_tier' => $user->subscription_tier,
        'subscription_status' => $user->subscription_status,
        'subscription_expires_at' => $user->subscription_expires_at,
    ],
    'recentViews' => // 7 days analytics data
    'recentCards' => $user->cards()->latest()->take(3)->get(),
]);
```

## Task 2: Cards Management Pages

### 2.1 Cards Index Page
**File**: `resources/js/Pages/Cards/Index.vue`

**Requirements**:
- List all user's cards in grid/list view
- Show: card image/preview, title, subtitle, views, shares, published status
- Actions: Edit, Preview, Duplicate, Delete
- Filter: Published/Unpublished
- Search by title
- Create new card button

**API Integration**:
```javascript
// GET /api/cards
axios.get('/api/cards').then(response => {
    cards.value = response.data.data
})
```

**Components**:
- CardListItem component
- CardGridItem component
- FilterDropdown component

### 2.2 Card Create/Edit Page
**File**: `resources/js/Pages/Cards/Form.vue`

**Requirements**:
- Form with fields:
  - Title (required)
  - Subtitle
  - Theme selector (dropdown with previews)
  - Template selector (if templates exist)
  - Custom slug
  - Publish toggle
- Sections builder (drag & drop)
- Preview panel (split screen)

**Form Structure**:
```javascript
const form = useForm({
    title: '',
    subtitle: '',
    theme_id: null,
    template_id: null,
    custom_slug: '',
    is_published: false,
    sections: []
})
```

**Components**:
- SectionBuilder component (drag & drop)
- ThemeSelector component
- SlugInput component (with availability check)

### 2.3 Card Sections Builder
**Component**: `resources/js/Components/SectionBuilder.vue`

**Requirements**:
- List of sections with drag handles
- Add section button (opens modal with section types)
- Section types: contact, social, services, products, testimonials, hours, appointments, gallery
- Each section has:
  - Title input
  - Content fields (dynamic based on type)
  - Active toggle
  - Delete button
  - Drag handle for reordering

**Section Type Components** (create for each):
- `SectionContact.vue` - Email, phone, address
- `SectionSocial.vue` - Social media links (icon picker)
- `SectionServices.vue` - Service list with descriptions
- `SectionProducts.vue` - Product grid with images
- `SectionTestimonials.vue` - Testimonial cards
- `SectionHours.vue` - Operating hours
- `SectionAppointments.vue` - Booking link/calendar
- `SectionGallery.vue` - Image gallery

## Task 3: Theme Editor

### 3.1 Themes Index Page
**File**: `resources/js/Pages/Themes/Index.vue`

**Requirements**:
- Grid of theme cards with previews
- Show: theme name, preview image, used count, visibility (public/private)
- Filter: My Themes / Public / System
- Actions: Edit, Duplicate, Delete, Apply to Card
- Create new theme button

### 3.2 Theme Editor Page
**File**: `resources/js/Pages/Themes/Editor.vue`

**Requirements** (CRITICAL - This is the main feature):

**Split Screen Layout**:
- Left: Controls panel (scrollable)
- Right: Live preview (sticky)

**Controls Panel Sections**:

1. **Basic Info**:
   - Theme name input

2. **Color Pickers** (6 colors):
   - Primary color
   - Secondary color
   - Background color
   - Text color
   - Card background color
   - Border color
   
   Use `@vueform/multiselect` or native HTML color input

3. **Font Selectors**:
   - Heading font dropdown (Google Fonts)
   - Body font dropdown (Google Fonts)
   - Font preview text
   
4. **Image Uploads**:
   - Background image upload (with opacity & blur controls)
   - Header image upload
   - Logo upload (with size & shape controls)
   - Image preview thumbnails
   - Remove image button

5. **Layout Controls**:
   - Card style: elevated/outlined/filled (radio buttons)
   - Border radius slider (0-30px)
   - Alignment: left/center/right
   - Spacing: compact/normal/spacious

6. **Custom CSS** (if user.canUseCustomCss):
   - Code editor (use CodeMirror or Monaco)
   - CSS validation
   - Warning message about browser compatibility

**Live Preview Panel**:
- Device toggle: Desktop/Mobile
- Preview frame with sample card content
- Updates in real-time as controls change
- Apply theme styles dynamically

**API Integration**:
```javascript
// Preview CSS endpoint
const previewCSS = async () => {
    const response = await axios.post('/api/themes/preview-css', {
        config: form.config
    })
    cssStyles.value = response.data.css
}

// Upload image
const uploadImage = async (file, type) => {
    const formData = new FormData()
    formData.append('image', file)
    formData.append('type', type)
    formData.append('theme_id', theme.id)
    
    const response = await axios.post('/api/themes/upload', formData)
    form.config.images[type] = response.data.url
}

// Save theme
const saveTheme = () => {
    form.post(`/api/themes${theme ? `/${theme.id}` : ''}`)
}
```

**Components to Create**:
- `ColorPicker.vue` - Color input with hex display
- `FontSelector.vue` - Dropdown with Google Fonts
- `ImageUploader.vue` - Drag & drop image upload with preview
- `CSSEditor.vue` - Code editor wrapper
- `DeviceToggle.vue` - Desktop/Mobile toggle
- `ThemePreview.vue` - Live preview frame

### 3.3 Theme Preview Component
**Component**: `resources/js/Components/ThemePreview.vue`

**Requirements**:
- Renders sample business card with theme applied
- Props: config (theme config object)
- Sample content:
  - Name: "John Doe"
  - Subtitle: "Software Engineer"
  - 3 sections: Contact, Social, Services
- Applies all theme styles dynamically
- Responsive to device mode

## Task 4: Payment & Subscription Flow

### 4.1 Subscription Plans Page
**File**: `resources/js/Pages/Subscription/Plans.vue`

**Requirements**:
- Display 3 plan cards: Free, Pro, Business
- Show for each:
  - Name
  - Price (monthly/yearly toggle)
  - Features list
  - Card limit
  - Theme limit
  - Custom CSS availability
  - Current plan indicator
- Subscribe button (opens payment modal)

**Data from backend**:
```php
return Inertia::render('Subscription/Plans', [
    'plans' => SubscriptionPlan::all(),
    'currentPlan' => $user->subscription_tier,
]);
```

### 4.2 Payment Checkout Modal
**Component**: `resources/js/Components/PaymentCheckout.vue`

**Requirements** (Cash Payment Gateway):
- Show plan details
- Display total amount
- Payment instructions for cash payment:
  - "Please pay at reception"
  - Payment code/reference number
  - QR code for payment reference
- Submit button creates pending payment
- Redirect to confirmation page

**API Integration**:
```javascript
const createPayment = async () => {
    const response = await axios.post('/api/payments', {
        subscription_plan_id: selectedPlan.id,
        currency: 'USD'
    })
    
    payment.value = response.data.data
    // Show payment reference and instructions
}
```

### 4.3 Payment Confirmation Page
**File**: `resources/js/Pages/Payments/Confirmation.vue`

**Requirements**:
- Show payment status: pending/completed
- Display payment reference number
- Instructions for payment completion
- Admin will confirm payment manually
- Poll for payment status updates
- Redirect to dashboard when confirmed

### 4.4 Payment History Page
**File**: `resources/js/Pages/Payments/History.vue`

**Requirements**:
- Table of all payments
- Columns: Date, Plan, Amount, Status, Reference
- Filter by status
- Download receipt button (if available)

## Task 5: Reusable Components

### Create these shared components:

1. **`StatCard.vue`**
   - Props: title, value, icon, color
   - Display statistic with icon

2. **`Modal.vue`** (enhance existing)
   - Props: show, maxWidth, closeable
   - Slot for content

3. **`ConfirmationModal.vue`**
   - Props: show, title, message, confirmText, cancelText
   - Emits: confirm, cancel

4. **`LoadingSpinner.vue`**
   - Props: size, color
   - Animated spinner

5. **`EmptyState.vue`**
   - Props: title, message, actionText, actionUrl
   - Display when no data

6. **`Badge.vue`**
   - Props: variant (success, warning, danger, info), text
   - Display status badges

7. **`Dropdown.vue`** (enhance existing)
   - More flexible dropdown with search

8. **`SearchInput.vue`**
   - Debounced search input
   - Clear button

9. **`Pagination.vue`**
   - Props: links (from Laravel pagination)
   - Navigate pages

10. **`Toast.vue`**
    - Success/error notifications
    - Auto-dismiss after 3 seconds

## Task 6: Web Routes (Inertia)

### Update `routes/web.php`

```php
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    
    // Cards
    Route::get('/cards', [CardController::class, 'index'])->name('cards.index');
    Route::get('/cards/create', [CardController::class, 'create'])->name('cards.create');
    Route::get('/cards/{card}/edit', [CardController::class, 'edit'])->name('cards.edit');
    // API handles create/update/delete
    
    // Themes
    Route::get('/themes', [ThemeController::class, 'index'])->name('themes.index');
    Route::get('/themes/create', [ThemeController::class, 'create'])->name('themes.create');
    Route::get('/themes/{theme}/edit', [ThemeController::class, 'edit'])->name('themes.edit');
    // API handles create/update/delete
    
    // Subscription
    Route::get('/subscription/plans', [SubscriptionController::class, 'plans'])->name('subscription.plans');
    Route::get('/payments/confirmation/{payment}', [PaymentController::class, 'confirmation'])
        ->name('payments.confirmation');
    Route::get('/payments/history', [PaymentController::class, 'history'])->name('payments.history');
    
    // Profile (already exists)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
});

// Public card view (already exists)
Route::get('/u/{slug}', [PublicCardController::class, 'show'])->name('card.public');
```

## Task 7: Controllers (Inertia)

### Create/Update these controllers:

1. **`app/Http/Controllers/DashboardController.php`**
   - index(): Render Dashboard with stats

2. **`app/Http/Controllers/CardController.php`** (Inertia, not API)
   - index(): List cards page
   - create(): Create form page
   - edit($id): Edit form page

3. **`app/Http/Controllers/ThemeController.php`** (Inertia, not API)
   - index(): List themes page
   - create(): Create editor page
   - edit($id): Edit editor page

4. **`app/Http/Controllers/SubscriptionController.php`** (Inertia)
   - plans(): Show plans page

5. **`app/Http/Controllers/PaymentController.php`** (Inertia)
   - confirmation($id): Show payment confirmation
   - history(): Show payment history

## Task 8: Navigation Updates

### Update `resources/js/Layouts/AuthenticatedLayout.vue`

Add navigation items:
- Dashboard
- My Cards
- Themes
- Subscription
- Profile

Add user dropdown:
- Profile
- Payment History
- Logout

## Task 9: Styling & UX

**Requirements**:
- Use Tailwind CSS (already configured)
- Consistent color scheme (blue primary)
- Smooth transitions
- Loading states for API calls
- Error handling with toast notifications
- Responsive design (mobile-first)
- Accessibility (ARIA labels, keyboard navigation)

## Task 10: Integration with API

**Patterns to follow**:

```javascript
// Using Inertia form helper
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    title: '',
    subtitle: ''
})

const submit = () => {
    form.post('/api/cards', {
        preserveScroll: true,
        onSuccess: () => {
            // Show success toast
        },
        onError: () => {
            // Show error toast
        }
    })
}

// Using axios for API calls
import axios from 'axios'

const fetchCards = async () => {
    try {
        loading.value = true
        const response = await axios.get('/api/cards')
        cards.value = response.data.data
    } catch (error) {
        // Handle error
    } finally {
        loading.value = false
    }
}
```

## Success Criteria

✅ User can navigate entire app via Inertia (SPA feel)
✅ Dashboard shows meaningful statistics
✅ Cards can be created, edited, previewed, deleted
✅ Sections can be added, reordered, edited, deleted
✅ Theme editor has all controls working
✅ Live preview updates in real-time
✅ Images can be uploaded and previewed
✅ Subscription plans displayed correctly
✅ Payment flow works (create, confirm, view history)
✅ All pages are responsive
✅ Loading states and error handling present
✅ Navigation works smoothly
✅ No page refreshes (true SPA)

## Dependencies

- API endpoints from api-layer-agent
- Tailwind CSS (configured)
- Axios (installed)
- Inertia.js helpers
- Existing Breeze components

## Testing

- Manual testing of all flows
- Browser compatibility testing
- Mobile responsiveness testing
- Accessibility testing
