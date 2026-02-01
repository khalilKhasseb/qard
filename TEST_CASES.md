# TapIt Application - Manual Test Cases

This document contains comprehensive test cases for manual testing of the TapIt application.

---

## Table of Contents

1. [Authentication & Registration](#1-authentication--registration)
2. [Email & Phone Verification](#2-email--phone-verification)
3. [Subscription Flow](#3-subscription-flow)
4. [Payment Gateway](#4-payment-gateway)
5. [Dashboard](#5-dashboard)
6. [Business Cards](#6-business-cards)
7. [Themes](#7-themes)
8. [Analytics](#8-analytics)
9. [Profile Management](#9-profile-management)
10. [Language Switching](#10-language-switching)
11. [Admin Panel (Filament)](#11-admin-panel-filament)
12. [Admin Panel Arabic Translation](#12-admin-panel-arabic-translation)
13. [Subscription Limits](#13-subscription-limits)
14. [Public Card View](#14-public-card-view)

---

## 1. Authentication & Registration

### 1.1 Registration Page

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 1.1.1 | Access registration page | Navigate to `/register` | Registration form displays with plan selection | ☐ |
| 1.1.2 | Plan selection required | Try to submit without selecting a plan | Validation error: plan selection required | ☐ |
| 1.1.3 | Display subscription plans | Load registration page | All active subscription plans are displayed with prices and features | ☐ |
| 1.1.4 | Valid registration | Fill all fields correctly and select a plan, submit | User created, redirected to verification | ☐ |
| 1.1.5 | Duplicate email validation | Register with existing email | Validation error: email already taken | ☐ |
| 1.1.6 | Duplicate phone validation | Register with existing phone | Validation error: phone already taken | ☐ |
| 1.1.7 | Password confirmation | Enter mismatched passwords | Validation error: passwords don't match | ☐ |
| 1.1.8 | Required fields validation | Submit empty form | Validation errors for all required fields | ☐ |

### 1.2 Login Page

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 1.2.1 | Access login page | Navigate to `/login` | Login form displays | ☐ |
| 1.2.2 | Valid login | Enter valid credentials | User logged in, redirected appropriately | ☐ |
| 1.2.3 | Invalid credentials | Enter wrong password | Error: invalid credentials | ☐ |
| 1.2.4 | Non-existent user | Enter non-existent email | Error: invalid credentials | ☐ |
| 1.2.5 | Remember me | Check "Remember me" and login | Session persists after browser close | ☐ |

### 1.3 Logout

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 1.3.1 | Logout functionality | Click logout button | User logged out, redirected to login | ☐ |
| 1.3.2 | Session cleared | After logout, try accessing protected route | Redirected to login | ☐ |

---

## 2. Email & Phone Verification

### 2.1 Email Verification

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 2.1.1 | Verification email sent | Register new user | Verification email sent | ☐ |
| 2.1.2 | Resend verification | Click resend verification link | New verification email sent | ☐ |
| 2.1.3 | Valid verification link | Click link in email | Email marked as verified | ☐ |
| 2.1.4 | Expired verification link | Use old/expired link | Error message displayed | ☐ |
| 2.1.5 | Already verified | Click verification link again | Appropriate message shown | ☐ |

### 2.2 Phone Verification

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 2.2.1 | OTP sent | Request OTP | OTP SMS sent to phone | ☐ |
| 2.2.2 | Valid OTP | Enter correct OTP | Phone marked as verified | ☐ |
| 2.2.3 | Invalid OTP | Enter wrong OTP | Error: invalid OTP | ☐ |
| 2.2.4 | Resend OTP | Click resend OTP | New OTP sent | ☐ |
| 2.2.5 | OTP expiry | Wait for OTP to expire, then enter | Error: OTP expired | ☐ |

### 2.3 Post-Verification Redirect

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 2.3.1 | Redirect to checkout | Verify user with pending plan | Redirected to payment checkout | ☐ |
| 2.3.2 | Redirect to subscription | Verify user without pending plan | Redirected to subscription selection | ☐ |
| 2.3.3 | Redirect to dashboard | Verify user with active subscription | Redirected to dashboard | ☐ |

---

## 3. Subscription Flow

### 3.1 Subscription Page

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 3.1.1 | Access subscription page | Navigate to `/subscription` | All active plans displayed | ☐ |
| 3.1.2 | Plan details displayed | View subscription page | Each plan shows: name, price, features, limits | ☐ |
| 3.1.3 | Current plan highlighted | User with subscription views page | Current plan is highlighted/marked | ☐ |
| 3.1.4 | Select plan | Click on a plan | Redirected to checkout | ☐ |

### 3.2 Subscription Middleware

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 3.2.1 | No subscription - blocked | User without subscription accesses `/dashboard` | Redirected to subscription page | ☐ |
| 3.2.2 | Active subscription - allowed | User with active subscription accesses `/dashboard` | Dashboard loads successfully | ☐ |
| 3.2.3 | Expired subscription - blocked | User with expired subscription accesses `/dashboard` | Redirected to subscription page | ☐ |
| 3.2.4 | Admin bypass | Admin user without subscription accesses `/dashboard` | Dashboard loads (admin bypasses check) | ☐ |

---

## 4. Payment Gateway

### 4.1 Payment Checkout

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 4.1.1 | Access checkout | Select a plan | Checkout page displays with plan details | ☐ |
| 4.1.2 | Plan summary shown | View checkout page | Correct plan name, price, and features displayed | ☐ |
| 4.1.3 | Payment methods shown | View checkout page | Available payment methods displayed | ☐ |

### 4.2 Card Payment

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 4.2.1 | Initiate card payment | Select card payment, submit | Redirected to payment gateway | ☐ |
| 4.2.2 | Successful payment | Complete payment on gateway | Subscription activated, redirected to confirmation | ☐ |
| 4.2.3 | Failed payment | Cancel/fail payment on gateway | Payment marked failed, error shown | ☐ |
| 4.2.4 | Payment confirmation page | After successful payment | Confirmation page shows subscription details | ☐ |

### 4.3 Cash Payment

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 4.3.1 | Initiate cash payment | Select cash payment option | Cash payment instructions shown | ☐ |
| 4.3.2 | Cash payment pending | Submit cash payment request | Payment marked as pending | ☐ |
| 4.3.3 | Cash payment confirmation | Admin confirms cash payment | Subscription activated | ☐ |
| 4.3.4 | Cash payment rejection | Admin rejects cash payment | Payment marked as rejected | ☐ |

### 4.4 Payment History

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 4.4.1 | View payment history | Navigate to payments page | All user payments listed | ☐ |
| 4.4.2 | Payment details | View individual payment | Amount, status, date, plan shown | ☐ |
| 4.4.3 | Filter by status | Filter payments by status | Correct payments displayed | ☐ |

---

## 5. Dashboard

### 5.1 Dashboard Access

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 5.1.1 | Access dashboard | Login with active subscription | Dashboard loads | ☐ |
| 5.1.2 | Stats displayed | View dashboard | User stats shown (cards, views, etc.) | ☐ |
| 5.1.3 | Quick actions | View dashboard | Create card, view analytics buttons work | ☐ |
| 5.1.4 | Recent cards | View dashboard | Recent business cards displayed | ☐ |

---

## 6. Business Cards

### 6.1 Card Listing

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 6.1.1 | View cards list | Navigate to cards page | All user cards displayed | ☐ |
| 6.1.2 | Empty state | User with no cards | Empty state message shown | ☐ |
| 6.1.3 | Card preview | View cards list | Card preview/thumbnail shown | ☐ |
| 6.1.4 | Card status | View cards list | Published/draft status visible | ☐ |

### 6.2 Create Card

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 6.2.1 | Access create form | Click "Create Card" | Card creation form loads | ☐ |
| 6.2.2 | Required fields | Submit empty form | Validation errors shown | ☐ |
| 6.2.3 | Create basic card | Fill required fields, submit | Card created successfully | ☐ |
| 6.2.4 | Add profile image | Upload profile image | Image uploaded and displayed | ☐ |
| 6.2.5 | Add social links | Add social media links | Links saved correctly | ☐ |
| 6.2.6 | Add contact info | Add email, phone, address | Contact info saved | ☐ |
| 6.2.7 | Select theme | Choose a theme | Theme applied to card | ☐ |

### 6.3 Edit Card

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 6.3.1 | Access edit form | Click edit on existing card | Edit form loads with data | ☐ |
| 6.3.2 | Update card | Modify fields and save | Changes saved successfully | ☐ |
| 6.3.3 | Change theme | Select different theme | New theme applied | ☐ |
| 6.3.4 | Update image | Upload new profile image | New image replaces old | ☐ |

### 6.4 Delete Card

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 6.4.1 | Delete confirmation | Click delete button | Confirmation dialog shown | ☐ |
| 6.4.2 | Confirm delete | Confirm deletion | Card deleted, removed from list | ☐ |
| 6.4.3 | Cancel delete | Cancel deletion | Card not deleted | ☐ |

### 6.5 Publish Card

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 6.5.1 | Publish card | Click publish on draft card | Card published, status updated | ☐ |
| 6.5.2 | Unpublish card | Click unpublish on published card | Card unpublished | ☐ |
| 6.5.3 | Public URL | View published card | Public URL accessible | ☐ |

### 6.6 Card Builder

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 6.6.1 | Access builder | Open card builder | Builder interface loads | ☐ |
| 6.6.2 | Add sections | Add different section types | Sections added to card | ☐ |
| 6.6.3 | Reorder sections | Drag and drop sections | Order updated | ☐ |
| 6.6.4 | Remove sections | Delete a section | Section removed | ☐ |
| 6.6.5 | Edit section content | Edit section details | Content updated | ☐ |
| 6.6.6 | Preview card | Click preview | Card preview shown | ☐ |

---

## 7. Themes

### 7.1 Theme Listing

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 7.1.1 | View themes list | Navigate to themes page | All user themes displayed | ☐ |
| 7.1.2 | Default themes | View themes | System default themes shown | ☐ |
| 7.1.3 | Custom themes | View themes | User's custom themes shown | ☐ |
| 7.1.4 | Theme preview | View themes list | Theme preview/colors visible | ☐ |

### 7.2 Create Theme

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 7.2.1 | Access create form | Click "Create Theme" | Theme creation form loads | ☐ |
| 7.2.2 | Set colors | Choose primary, secondary colors | Colors applied | ☐ |
| 7.2.3 | Set fonts | Choose font family | Font applied | ☐ |
| 7.2.4 | Save theme | Submit form | Theme created successfully | ☐ |
| 7.2.5 | Theme preview | During creation | Live preview updates | ☐ |

### 7.3 Edit Theme

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 7.3.1 | Edit custom theme | Edit user-created theme | Changes saved | ☐ |
| 7.3.2 | Cannot edit default | Try editing default theme | Edit not allowed or creates duplicate | ☐ |

### 7.4 Delete Theme

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 7.4.1 | Delete custom theme | Delete user-created theme | Theme deleted | ☐ |
| 7.4.2 | Cannot delete in-use | Delete theme used by cards | Warning or prevented | ☐ |
| 7.4.3 | Cannot delete default | Try deleting default theme | Not allowed | ☐ |

### 7.5 Duplicate Theme

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 7.5.1 | Duplicate theme | Click duplicate on any theme | New theme created with copied settings | ☐ |

---

## 8. Analytics

### 8.1 Analytics Page

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 8.1.1 | Access analytics | Navigate to analytics page | Analytics dashboard loads | ☐ |
| 8.1.2 | View stats | View analytics | Total views, unique visitors shown | ☐ |
| 8.1.3 | Card-specific stats | Select a card | Stats for that card shown | ☐ |
| 8.1.4 | Date range filter | Change date range | Stats update accordingly | ☐ |
| 8.1.5 | Charts display | View analytics | Charts render correctly | ☐ |

---

## 9. Profile Management

### 9.1 View Profile

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 9.1.1 | Access profile | Navigate to profile page | Profile information displayed | ☐ |
| 9.1.2 | Current info shown | View profile | Name, email, phone displayed | ☐ |

### 9.2 Update Profile

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 9.2.1 | Update name | Change name, save | Name updated | ☐ |
| 9.2.2 | Update email | Change email, save | Email updated (may require re-verification) | ☐ |
| 9.2.3 | Update phone | Change phone, save | Phone updated | ☐ |
| 9.2.4 | Update profile image | Upload new image | Image updated | ☐ |

### 9.3 Change Password

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 9.3.1 | Valid password change | Enter current + new password | Password changed | ☐ |
| 9.3.2 | Wrong current password | Enter wrong current password | Error shown | ☐ |
| 9.3.3 | Password mismatch | New passwords don't match | Error shown | ☐ |

### 9.4 Delete Account

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 9.4.1 | Delete confirmation | Click delete account | Confirmation dialog shown | ☐ |
| 9.4.2 | Confirm delete | Enter password, confirm | Account deleted | ☐ |
| 9.4.3 | Cancel delete | Cancel deletion | Account not deleted | ☐ |

---

## 10. Language Switching

### 10.1 Frontend Language

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 10.1.1 | Default language | Fresh visit to app | App displays in default language (from config) | ☐ |
| 10.1.2 | Switch to Arabic | Change language to Arabic | UI switches to Arabic, RTL layout | ☐ |
| 10.1.3 | Switch to English | Change language to English | UI switches to English, LTR layout | ☐ |
| 10.1.4 | Language persists | Change language, navigate | Language setting persists across pages | ☐ |
| 10.1.5 | Language in session | Change language, refresh | Language setting persists | ☐ |

### 10.2 Card Language

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 10.2.1 | Multi-language card | Create card with translations | Translations saved | ☐ |
| 10.2.2 | Switch card language | View card, switch language | Card content changes | ☐ |
| 10.2.3 | Default card language | View card without switching | Displays in default language | ☐ |

---

## 11. Admin Panel (Filament)

### 11.1 Admin Access

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 11.1.1 | Access admin login | Navigate to `/admin` | Admin login page displays | ☐ |
| 11.1.2 | Admin login | Login with admin credentials | Admin dashboard loads | ☐ |
| 11.1.3 | Non-admin blocked | Login with regular user | Access denied | ☐ |

### 11.2 Admin Dashboard

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 11.2.1 | Stats overview | View admin dashboard | Total users, cards, payments shown | ☐ |
| 11.2.2 | Revenue chart | View dashboard | Revenue chart displays | ☐ |
| 11.2.3 | Card views chart | View dashboard | Card views chart displays | ☐ |
| 11.2.4 | Latest payments | View dashboard | Recent payments widget shows | ☐ |
| 11.2.5 | Unverified users | View dashboard | Unverified users table shows | ☐ |

### 11.3 User Management

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 11.3.1 | List users | Navigate to Users | All users listed | ☐ |
| 11.3.2 | Search users | Search by name/email | Matching users shown | ☐ |
| 11.3.3 | Filter users | Filter by role/status | Filtered results shown | ☐ |
| 11.3.4 | Create user | Create new user | User created | ☐ |
| 11.3.5 | Edit user | Edit existing user | Changes saved | ☐ |
| 11.3.6 | Delete user | Delete a user | User deleted | ☐ |
| 11.3.7 | Toggle admin | Set/remove admin role | Role updated | ☐ |

### 11.4 Subscription Plans

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 11.4.1 | List plans | Navigate to Subscription Plans | All plans listed | ☐ |
| 11.4.2 | Create plan | Create new plan | Plan created with all limits | ☐ |
| 11.4.3 | Edit plan | Edit existing plan | Changes saved | ☐ |
| 11.4.4 | Toggle plan active | Activate/deactivate plan | Status updated | ☐ |
| 11.4.5 | Delete plan | Delete a plan | Plan deleted (if not in use) | ☐ |

### 11.5 User Subscriptions

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 11.5.1 | List subscriptions | Navigate to User Subscriptions | All subscriptions listed | ☐ |
| 11.5.2 | View subscription | View subscription details | User, plan, status shown | ☐ |
| 11.5.3 | Edit subscription | Modify subscription | Changes saved | ☐ |
| 11.5.4 | Cancel subscription | Cancel a subscription | Status updated to cancelled | ☐ |

### 11.6 Payments

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 11.6.1 | List payments | Navigate to Payments | All payments listed | ☐ |
| 11.6.2 | Filter by status | Filter pending/completed/failed | Correct payments shown | ☐ |
| 11.6.3 | View payment | View payment details | All details shown | ☐ |
| 11.6.4 | Confirm cash payment | Confirm pending cash payment | Payment confirmed, subscription activated | ☐ |
| 11.6.5 | Reject cash payment | Reject pending cash payment | Payment marked rejected | ☐ |

### 11.7 Business Cards

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 11.7.1 | List all cards | Navigate to Business Cards | All cards listed | ☐ |
| 11.7.2 | Search cards | Search by name/slug | Matching cards shown | ☐ |
| 11.7.3 | Filter by user | Filter by card owner | Filtered results shown | ☐ |
| 11.7.4 | View card | View card details | All details shown | ☐ |
| 11.7.5 | Edit card | Edit card details | Changes saved | ☐ |
| 11.7.6 | Delete card | Delete a card | Card deleted | ☐ |

### 11.8 Themes

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 11.8.1 | List themes | Navigate to Themes | All themes listed | ☐ |
| 11.8.2 | Create theme | Create new theme | Theme created | ☐ |
| 11.8.3 | Edit theme | Edit existing theme | Changes saved | ☐ |
| 11.8.4 | Toggle default | Set as default theme | Default status updated | ☐ |
| 11.8.5 | Delete theme | Delete a theme | Theme deleted | ☐ |

### 11.9 Languages

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 11.9.1 | List languages | Navigate to Languages | All languages listed | ☐ |
| 11.9.2 | Create language | Create new language | Language created | ☐ |
| 11.9.3 | Edit language | Edit existing language | Changes saved | ☐ |
| 11.9.4 | Toggle active | Activate/deactivate language | Status updated | ☐ |
| 11.9.5 | Set RTL | Mark language as RTL | RTL flag set | ☐ |

### 11.10 Translation History

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 11.10.1 | List history | Navigate to Translation History | All translations listed | ☐ |
| 11.10.2 | Filter by card | Filter by business card | Filtered results shown | ☐ |
| 11.10.3 | View details | View translation entry | Source, target, content shown | ☐ |

### 11.11 Settings

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 11.11.1 | Access settings | Navigate to Settings | Settings page loads | ☐ |
| 11.11.2 | Update app name | Change application name | Setting saved | ☐ |
| 11.11.3 | Update payment settings | Change payment gateway settings | Settings saved | ☐ |
| 11.11.4 | Update SMS settings | Change SMS provider settings | Settings saved | ☐ |

---

## 12. Admin Panel Arabic Translation

### 12.1 Arabic Display

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 12.1.1 | Admin in Arabic | Set APP_LOCALE=ar, access admin | Admin panel displays in Arabic | ☐ |
| 12.1.2 | Navigation groups | View sidebar | Groups show in Arabic (إدارة المستخدمين, etc.) | ☐ |
| 12.1.3 | Resource labels | View resources | Labels in Arabic (المستخدمون, البطاقات, etc.) | ☐ |
| 12.1.4 | Form labels | Open any form | All field labels in Arabic | ☐ |
| 12.1.5 | Table headers | View any table | Column headers in Arabic | ☐ |
| 12.1.6 | Actions | View action buttons | Actions in Arabic (إنشاء, تعديل, حذف) | ☐ |
| 12.1.7 | Widgets | View dashboard widgets | Widget titles in Arabic | ☐ |
| 12.1.8 | Settings page | View settings | All sections/fields in Arabic | ☐ |
| 12.1.9 | RTL layout | View admin in Arabic | Layout is right-to-left | ☐ |

### 12.2 English Display

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 12.2.1 | Admin in English | Set APP_LOCALE=en, access admin | Admin panel displays in English | ☐ |
| 12.2.2 | All translations work | Navigate all resources | No missing translations (no keys shown) | ☐ |
| 12.2.3 | LTR layout | View admin in English | Layout is left-to-right | ☐ |

---

## 13. Subscription Limits

### 13.1 Card Limits

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 13.1.1 | Under limit | User with 1 card (limit 3) creates card | Card created successfully | ☐ |
| 13.1.2 | At limit | User at card limit tries to create | Error: limit reached | ☐ |
| 13.1.3 | No subscription | User without subscription tries to create | Blocked or redirected | ☐ |
| 13.1.4 | Admin bypass | Admin creates cards | No limit enforced | ☐ |

### 13.2 Theme Limits

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 13.2.1 | Under limit | User under theme limit creates theme | Theme created | ☐ |
| 13.2.2 | At limit | User at theme limit tries to create | Error: limit reached | ☐ |
| 13.2.3 | No subscription | User without subscription tries to create | Blocked or redirected | ☐ |

### 13.3 Translation Limits

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 13.3.1 | Credits available | User with credits translates | Translation successful, credits deducted | ☐ |
| 13.3.2 | No credits | User with 0 credits tries to translate | Error: no credits remaining | ☐ |
| 13.3.3 | Unlimited plan | User with unlimited translations | Translation works, no deduction | ☐ |

---

## 14. Public Card View

### 14.1 Public Access

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 14.1.1 | View published card | Access public card URL | Card displays correctly | ☐ |
| 14.1.2 | View unpublished | Access unpublished card URL | 404 or error | ☐ |
| 14.1.3 | Card with theme | View card with custom theme | Theme styles applied | ☐ |
| 14.1.4 | Profile image | View card with image | Image displays | ☐ |
| 14.1.5 | Social links | View card with socials | Links work correctly | ☐ |
| 14.1.6 | Contact info | View card with contact | Contact info displayed | ☐ |

### 14.2 Card Interactions

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 14.2.1 | Click social link | Click on a social link | Opens correct URL | ☐ |
| 14.2.2 | Click phone | Click phone number | Opens dialer (on mobile) | ☐ |
| 14.2.3 | Click email | Click email address | Opens email client | ☐ |
| 14.2.4 | Click website | Click website link | Opens website | ☐ |
| 14.2.5 | Download vCard | Click save contact | vCard downloads | ☐ |

### 14.3 Card Analytics Tracking

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 14.3.1 | View tracked | Visit public card | View count increases | ☐ |
| 14.3.2 | Unique visitor | New visitor views card | Unique visitor counted | ☐ |
| 14.3.3 | Link click tracked | Click a link on card | Event tracked in analytics | ☐ |

### 14.4 Card Language Switcher

| # | Test Case | Steps | Expected Result | Status |
|---|-----------|-------|-----------------|--------|
| 14.4.1 | Language switcher visible | View multilingual card | Language switcher shown | ☐ |
| 14.4.2 | Switch language | Click different language | Card content changes | ☐ |
| 14.4.3 | RTL language | Switch to Arabic | RTL layout applied to card | ☐ |

---

## Test Execution Summary

| Section | Total Tests | Passed | Failed | Blocked |
|---------|-------------|--------|--------|---------|
| 1. Authentication & Registration | 17 | | | |
| 2. Email & Phone Verification | 13 | | | |
| 3. Subscription Flow | 8 | | | |
| 4. Payment Gateway | 14 | | | |
| 5. Dashboard | 4 | | | |
| 6. Business Cards | 24 | | | |
| 7. Themes | 12 | | | |
| 8. Analytics | 5 | | | |
| 9. Profile Management | 10 | | | |
| 10. Language Switching | 7 | | | |
| 11. Admin Panel (Filament) | 46 | | | |
| 12. Admin Panel Arabic Translation | 12 | | | |
| 13. Subscription Limits | 10 | | | |
| 14. Public Card View | 14 | | | |
| **TOTAL** | **196** | | | |

---

## Notes

- **Testing Environment**: Test on staging/development before production
- **Test Data**: Use test accounts and test payment credentials
- **Browser Testing**: Test on Chrome, Firefox, Safari, and mobile browsers
- **Clear Cache**: Run `php artisan config:clear && php artisan cache:clear` before testing locale changes

---

## Bug Report Template

When reporting a bug, include:

```
**Test Case ID**: [e.g., 4.3.1]
**Test Case Name**: [e.g., Initiate cash payment]
**Expected Result**: [What should happen]
**Actual Result**: [What actually happened]
**Steps to Reproduce**:
1.
2.
3.

**Screenshots**: [Attach if applicable]
**Browser/Device**: [e.g., Chrome 120, Windows 11]
**User Account**: [Test account used]
```
