# Qard Frontend Testing Guide

## Quick Test Flow (5 Minutes)

### 1. Landing Page Test
**URL:** `http://qard.test/`
**What to verify:**
- [ ] Hero section displays with gradient background
- [ ] "Create Your Card Free" button is visible
- [ ] Features section shows 4 features with icons
- [ ] Pricing table shows 3 plans (Free, Pro, Business)
- [ ] Footer is present at bottom
- [ ] Login/Register buttons work in header

### 2. Registration & Login
**URL:** `http://qard.test/register`
**What to verify:**
- [ ] Registration form works
- [ ] Can create new account
- [ ] Redirects to dashboard after registration

### 3. Dashboard Test
**URL:** `http://qard.test/dashboard`
**What to verify:**
- [ ] See 5 stats cards: Total Cards, Published, Views, Themes, **NFC Taps**
- [ ] Subscription status panel visible
- [ ] 3 quick action cards (Create Card, Manage Themes, View Cards)
- [ ] Recent cards list (empty if new user)

### 4. Create Card Test
**URL:** `http://qard.test/cards/create`
**What to verify:**
- [ ] Form displays with title, subtitle, theme selector, custom slug
- [ ] Can enter card details
- [ ] Submit redirects to edit page

### 5. Edit Card with Section Builder
**URL:** `http://qard.test/cards/{id}/edit`
**What to verify:**
- [ ] Basic info form on left side
- [ ] **Section Builder component visible**
- [ ] Click "Add Section" button
- [ ] Modal opens with section type dropdown
- [ ] Can select from 8 types: Contact, Social, Services, Products, Testimonials, Hours, Appointments, Gallery
- [ ] Add section and see it in list
- [ ] Can reorder sections with up/down arrows
- [ ] Can edit section
- [ ] Can delete section
- [ ] Sidebar shows publish status and stats

### 6. Theme Management Test
**URL:** `http://qard.test/themes`
**What to verify:**
- [ ] Theme grid displays
- [ ] "Create New Theme" button works
- [ ] Can see system themes

### 7. Theme Editor with Live Preview
**URL:** `http://qard.test/themes/{id}/edit`
**What to verify:**
- [ ] Left panel has theme editor
- [ ] Right panel has **Live Preview**
- [ ] Change color and see preview update
- [ ] **Device toggle** (Desktop/Mobile) buttons work
- [ ] Font selector changes preview font
- [ ] Image upload sections for background, header, logo
- [ ] Layout controls (card style, border radius, alignment)
- [ ] Custom CSS textarea (if pro user)
- [ ] Preview shows sample card with applied styles

### 8. Payments Test
**URL:** `http://qard.test/payments`
**What to verify:**
- [ ] Current subscription status shown
- [ ] Available plans displayed (3 cards)
- [ ] Payment history table (empty if new)
- [ ] "Choose Plan" buttons work

### 9. Checkout Flow Test
**URL:** `http://qard.test/payments/checkout/{plan_id}`
**What to verify:**
- [ ] Order summary shows plan details
- [ ] Payment method selection (Cash radio button)
- [ ] Terms checkbox
- [ ] "Pay $X" button visible
- [ ] Submit processes payment

### 10. Payment Confirmation
**URL:** `http://qard.test/payments/confirmation/{payment_id}`
**What to verify:**
- [ ] Success checkmark and message
- [ ] Payment details displayed
- [ ] Next steps guide
- [ ] "Go to Dashboard" button works

### 11. Public Card View Test
**URL:** `http://qard.test/u/{slug}` or `http://qard.test/c/{shareUrl}`
**What to verify:**
- [ ] Card displays with applied theme
- [ ] Sections render correctly
- [ ] Share buttons work
- [ ] Copy link button works
- [ ] Social share buttons open correct platforms
- [ ] Footer shows "Powered by TapIt"

## Component Testing Checklist

### SectionBuilder Component
- [ ] Opens add modal on "Add Section" button
- [ ] Dropdown shows all 8 section types with icons
- [ ] Title field required
- [ ] Can add multiple sections
- [ ] Sections appear in list after adding
- [ ] Up/down arrows reorder sections
- [ ] Edit button opens edit modal
- [ ] Delete button prompts confirmation
- [ ] Empty state shows when no sections

### ThemePreview Component
- [ ] Displays sample card content
- [ ] Updates in real-time when colors change
- [ ] Font changes reflect immediately
- [ ] Layout changes apply instantly
- [ ] Button color matches primary color
- [ ] Card style (elevated/outlined/filled) applies correctly

### DeviceToggle Component
- [ ] Desktop button selects desktop view
- [ ] Mobile button selects mobile view
- [ ] Active button highlighted
- [ ] Preview container changes size

### ColorPicker Component
- [ ] Color input and hex input synced
- [ ] Can pick color with picker
- [ ] Can type hex value
- [ ] Both inputs update together

### FontSelector Component
- [ ] Dropdown shows all fonts
- [ ] Selection updates parent component
- [ ] Fonts are recognizable names

### ImageUpload Component
- [ ] File input works
- [ ] Image preview shows after upload
- [ ] Clear button removes image
- [ ] Upload button disabled during upload

## API Endpoint Testing

### Card Endpoints
```bash
# List cards
GET /api/cards

# Create card
POST /api/cards
{
  "title": "Test Card",
  "subtitle": "Test Subtitle"
}

# Publish card
POST /api/cards/{id}/publish
{
  "is_published": true
}
```

### Section Endpoints
```bash
# Add section
POST /api/cards/{card_id}/sections
{
  "section_type": "contact",
  "title": "Contact Me",
  "content": {}
}

# Update section
PUT /api/sections/{section_id}
{
  "title": "Updated Title"
}

# Delete section
DELETE /api/sections/{section_id}

# Reorder sections
POST /api/cards/{card_id}/sections/reorder
{
  "order": [
    {"id": 1, "order": 0},
    {"id": 2, "order": 1}
  ]
}
```

### Payment Endpoints
```bash
# Create payment
POST /api/payments
{
  "subscription_plan_id": 1,
  "payment_method": "cash"
}

# Get payment history
GET /api/payments/history
```

## Browser Testing

Test on these browsers:
- [ ] Chrome/Edge (Latest)
- [ ] Firefox (Latest)
- [ ] Safari (Latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

## Responsive Testing

Test on these breakpoints:
- [ ] Mobile (320px - 767px)
- [ ] Tablet (768px - 1023px)
- [ ] Desktop (1024px+)

## Performance Checks

- [ ] Landing page loads in < 2s
- [ ] Dashboard loads in < 1s
- [ ] Section builder is responsive
- [ ] Theme preview updates smoothly
- [ ] No console errors
- [ ] No 404s for assets

## Known Limitations

1. **Card payment method** - Marked as "Coming soon"
2. **Section content editing** - Basic edit only, detailed editors pending
3. **Custom CSS** - Requires pro subscription check
4. **Image uploads** - Need backend API endpoint `/api/themes/upload`
5. **Theme preview API** - Need backend endpoint `/api/themes/preview`

## Quick Fixes for Common Issues

### If Section Builder doesn't show:
- Check browser console for errors
- Verify axios is loaded
- Check API routes are registered

### If Theme Preview doesn't update:
- Check v-bind styles are working
- Verify config object structure
- Look for CSS conflicts

### If Payment flow fails:
- Check routes are registered in web.php
- Verify PaymentController has checkout/confirmation methods
- Check API endpoint `/api/payments/create` exists

### If Public card doesn't display:
- Verify blade template at `resources/views/cards/public.blade.php`
- Check PublicCardController returns view
- Verify sections are loaded with card

## Success Criteria

✅ **All 15+ pages load without errors**
✅ **Section Builder adds/edits/deletes sections**
✅ **Theme Editor preview updates in real-time**
✅ **Payment flow completes end-to-end**
✅ **Public card displays with theme**
✅ **Dashboard shows all 5 stats**
✅ **Landing page is beautiful and functional**
✅ **No console errors**
✅ **Responsive on mobile**

## Report Issues

If you find bugs, document:
1. **URL** where issue occurs
2. **Steps to reproduce**
3. **Expected behavior**
4. **Actual behavior**
5. **Browser** and version
6. **Console errors** (if any)
7. **Screenshots** (if visual)

## Next Steps After Testing

1. Fix any discovered bugs
2. Implement section-specific content editors
3. Add backend endpoints for image uploads
4. Implement card payment gateway
5. Add more themes
6. Enhance analytics
7. Mobile app integration
8. Performance optimizations

---

**Happy Testing! 🚀**
