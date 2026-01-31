# Task: User Dashboard Implementation

**Agent:** integration-agent  
**Priority:** P1 (High)  
**Estimated Time:** 2-3 hours  
**Depends On:** Task 01 (Inertia setup), Task 02 (Authentication)

## Objective
Build the main user dashboard page with overview statistics, quick actions, and navigation to all features.

## Requirements

### 1. Dashboard Page

**Location:** `resources/js/Pages/Dashboard.vue`

**Layout:**
- Welcome message with user name
- Subscription status card
- Statistics overview (4 stat cards)
- Quick actions section
- Recent cards list
- Recent activity feed

### 2. Dashboard Route

**Location:** `routes/web.php`

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
```

### 3. Dashboard Controller

**Location:** `app/Http/Controllers/DashboardController.php`

**Data to pass to Inertia:**
```php
return Inertia::render('Dashboard', [
    'stats' => [
        'total_cards' => $user->cards()->count(),
        'published_cards' => $user->cards()->published()->count(),
        'total_views' => $user->cards()->sum('views_count'),
        'total_themes' => $user->themes()->count(),
    ],
    'subscription' => [
        'tier' => $user->subscription_tier,
        'status' => $user->subscription_status,
        'plan_name' => $user->subscription?->plan->name,
        'expires_at' => $user->subscription?->ends_at,
    ],
    'recent_cards' => $user->cards()
        ->latest()
        ->limit(5)
        ->with('theme')
        ->get(),
    'analytics' => [
        'views_today' => // Calculate views today
        'views_this_week' => // Calculate views this week
        'views_this_month' => // Calculate views this month
    ],
]);
```

### 4. Dashboard Components

**Create components in:** `resources/js/Components/Dashboard/`

#### StatsCard.vue
- Display stat label and value
- Icon support
- Trend indicator (up/down)
- Color variants

#### SubscriptionCard.vue
- Current plan name
- Status badge
- Expiry date
- Upgrade button
- Renewal date

#### QuickActions.vue
- "Create New Card" button
- "Create Theme" button
- "View Analytics" button
- "Manage Subscription" button

#### RecentCardsList.vue
- List of 5 most recent cards
- Card title, status, views
- Thumbnail preview
- Quick actions: Edit, View, Share
- "View all cards" link

#### ActivityFeed.vue
- Recent activity list
- Event types: card created, card viewed, theme applied
- Timestamps
- Activity icons

### 5. Stat Cards Data

**Statistics to display:**
1. **Total Cards**
   - Icon: ID Card
   - Value: Count of all cards
   - Trend: Cards created this week

2. **Published Cards**
   - Icon: Eye
   - Value: Count of published cards
   - Trend: Publication rate

3. **Total Views**
   - Icon: Chart Bar
   - Value: Sum of all card views
   - Trend: Views this week

4. **Active Themes**
   - Icon: Paint Brush
   - Value: Count of user themes
   - Trend: Themes created

### 6. Quick Actions

**Actions to include:**
1. Create New Card → `/cards/create`
2. Create Theme → `/themes/create`
3. View Analytics → `/analytics`
4. Manage Subscription → `/subscription`

### 7. Subscription Status Widget

**Display:**
- Free tier: "Upgrade to Pro" CTA
- Pro/Business: Plan name, renewal date
- Expired: "Renew Subscription" CTA
- Pending payment: "Complete Payment" link

### 8. Empty States

**If user has no cards:**
- Show welcome message
- "Get Started" guide
- "Create Your First Card" button

### 9. Responsive Design

- Desktop: 4-column stats grid
- Tablet: 2-column stats grid
- Mobile: Single column layout

## Deliverables

1. ✅ Dashboard.vue page created
2. ✅ DashboardController implemented
3. ✅ 5 dashboard components created
4. ✅ Stats calculation working
5. ✅ Recent cards list working
6. ✅ Quick actions functional
7. ✅ Subscription widget showing correct data
8. ✅ Empty states handled
9. ✅ Responsive on all devices

## Validation Steps

1. ✅ Visit `/dashboard` - renders correctly
2. ✅ Stats show correct numbers
3. ✅ Recent cards display properly
4. ✅ Quick action buttons navigate correctly
5. ✅ Subscription status shows correct info
6. ✅ Empty state displays when no cards
7. ✅ Responsive on mobile/tablet/desktop
8. ✅ Loading states work
9. ✅ No console errors

## Design Guidelines

- Use Tailwind CSS v4
- Follow TapIt color scheme (blue primary)
- Card-based layout
- Icons from Heroicons
- Smooth transitions
- Loading skeletons for data

## API Endpoints Needed

None - all data fetched via Inertia props from controller.

## Dependencies
- Task 01 (Inertia setup) complete
- Task 02 (Authentication) complete

## Next Tasks
After completion, integration-agent will build card management UI.
