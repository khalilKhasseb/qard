# Quick Start Guide - TapIt API

## 🚀 Getting Started in 3 Steps

### Step 1: Create an API Token

Open terminal and run:
```bash
php artisan tinker
```

Then execute:
```php
$user = \App\Models\User::first();
$token = $user->createToken('api-test')->plainTextToken;
echo "Token: " . $token;
exit;
```

Copy the token that appears.

### Step 2: Test the API

#### Option A: Using cURL

```bash
# List all cards
curl -X GET http://qard.test/api/cards \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"

# Create a card
curl -X POST http://qard.test/api/cards \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d "{\"title\":\"My Business Card\",\"subtitle\":\"Developer\"}"

# List themes
curl -X GET http://qard.test/api/themes \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

#### Option B: Using the Test Script

```bash
php test_api.php
```

When prompted, paste your API token.

### Step 3: Explore the Documentation

Open `API_ENDPOINTS.md` for complete endpoint documentation.

---

## 📋 Quick Reference

### Base URL
```
http://qard.test/api
```

### Authentication Header
```
Authorization: Bearer {your-token}
```

### All Available Endpoints

**Cards (8):**
- GET `/cards` - List
- POST `/cards` - Create
- GET `/cards/{id}` - Show
- PUT `/cards/{id}` - Update
- DELETE `/cards/{id}` - Delete
- POST `/cards/{id}/publish` - Publish
- POST `/cards/{id}/duplicate` - Duplicate
- GET `/cards/{id}/analytics` - Analytics

**Sections (4):**
- POST `/cards/{card}/sections` - Create
- PUT `/sections/{id}` - Update
- DELETE `/sections/{id}` - Delete
- POST `/cards/{card}/sections/reorder` - Reorder

**Themes (8):**
- GET `/themes` - List
- POST `/themes` - Create
- GET `/themes/{id}` - Show
- PUT `/themes/{id}` - Update
- DELETE `/themes/{id}` - Delete
- POST `/themes/{id}/duplicate` - Duplicate
- POST `/themes/{id}/apply/{card}` - Apply to card
- POST `/themes/upload` - Upload image

**Payments (5):**
- GET `/subscription-plans` - List plans
- POST `/payments` - Create payment
- POST `/payments/{id}/confirm` - Confirm
- GET `/payments/history` - History
- GET `/subscription` - Current subscription

---

## 🎯 Example Flow

### Create a Complete Business Card

**1. Create the card:**
```bash
curl -X POST http://qard.test/api/cards \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"John Doe","subtitle":"Full Stack Developer"}'
```

Save the `id` from the response (e.g., `"id": 1`).

**2. Add a contact section:**
```bash
curl -X POST http://qard.test/api/cards/1/sections \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "section_type":"contact",
    "title":"Contact Me",
    "content":{
      "email":"john@example.com",
      "phone":"+1234567890"
    }
  }'
```

**3. Add a social section:**
```bash
curl -X POST http://qard.test/api/cards/1/sections \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "section_type":"social",
    "title":"Social Media",
    "content":{
      "twitter":"https://twitter.com/johndoe",
      "linkedin":"https://linkedin.com/in/johndoe"
    }
  }'
```

**4. Create or get a theme:**
```bash
# List available themes
curl -X GET http://qard.test/api/themes \
  -H "Authorization: Bearer YOUR_TOKEN"

# Or create a new theme
curl -X POST http://qard.test/api/themes \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name":"Blue Professional",
    "config":{
      "colors":{
        "primary":"#3B82F6",
        "secondary":"#1E40AF"
      }
    }
  }'
```

**5. Apply the theme:**
```bash
curl -X POST http://qard.test/api/themes/1/apply/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**6. Publish the card:**
```bash
curl -X POST http://qard.test/api/cards/1/publish \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"is_published":true}'
```

**7. View the final card:**
```bash
curl -X GET http://qard.test/api/cards/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🐛 Troubleshooting

### "Unauthenticated" Error
- Check your token is correct
- Ensure you included the `Authorization: Bearer TOKEN` header
- Verify the user exists and has an active token

### "403 Forbidden" Error
- Check if you've reached your card/theme quota
- Verify you own the resource you're trying to modify
- System default themes cannot be modified

### "422 Validation Error"
- Check the response for specific validation errors
- Ensure required fields are provided
- Verify field formats (e.g., email, slug)

### "500 Server Error"
- Check Laravel logs: `storage/logs/laravel.log`
- Ensure database is migrated: `php artisan migrate`
- Clear cache: `php artisan cache:clear`

---

## 📚 Additional Resources

- **Full Documentation:** `API_ENDPOINTS.md`
- **Implementation Details:** `API_IMPLEMENTATION_SUMMARY.md`
- **Automated Tests:** `test_api.php`

---

## ✅ Verification Checklist

Before using in production:

- [ ] Run `php test_api.php` successfully
- [ ] Test card creation
- [ ] Test section management
- [ ] Test theme application
- [ ] Test payment flow
- [ ] Verify authorization rules
- [ ] Check validation errors
- [ ] Review error logging

---

## 🎉 You're Ready!

All 26 API endpoints are implemented and ready to use. Happy coding! 🚀
