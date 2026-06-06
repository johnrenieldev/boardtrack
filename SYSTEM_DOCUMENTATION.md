# BoardTrack — System Documentation

> A PHP-based boarding house management system for landlords and tenants.

---

## Overview

BoardTrack is a web application that digitizes the day-to-day operations of a boarding house. It provides two role-based portals — one for the landlord and one for tenants — covering everything from tenant registration and room assignment to billing, payments, complaints, and announcements.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8+ (no framework) |
| Routing | Custom `Router` class (query-string based: `?url=controller/action`) |
| Database | MySQL via PDO |
| Frontend | Tailwind CSS + custom `dashboard.css` design system |
| Email | Custom `BoardTrackMail` helper wrapping PHPMailer or similar |
| Auth | Session-based; optional TOTP 2FA via Google Authenticator |
| File Uploads | Government IDs stored in `UPLOAD_IDS` path constant; GCash QR in `uploads/` |

---

## Project Structure

```
boardtrack/
├── app/
│   ├── controllers/        # Business logic (AuthController, LandlordController, TenantController, …)
│   ├── models/             # PDO data access (User, Bill, Payment, Tenant, Room, …)
│   ├── views/
│   │   ├── auth/           # Login, register, OTP, password reset
│   │   ├── landlord/       # All landlord-facing pages
│   │   ├── tenant/         # All tenant-facing pages
│   │   ├── layouts/        # Shared HTML shells (main.php, landlord.php, tenant.php)
│   │   └── components/     # Navbar, sidebar, alerts, mobile nav
│   ├── helpers/            # BoardTrackMail, Mailer, TOTP
│   ├── services/           # CompatibilityService (roommate matching)
│   └── cron/               # process_overdue_penalties.php (scheduled job)
├── config/
│   ├── config.php          # App constants (ROOT_PATH, UPLOAD_MAX_SIZE, VERIFY_TOKEN_TTL, …)
│   ├── database.php        # PDO connection
│   └── mail.php            # SMTP credentials
├── core/                   # Base Controller, Model, Router classes
├── public/
│   └── assets/             # CSS (dashboard.css, output.css), JS, fonts
└── database/               # Exported phpMyAdmin SQL dumps (local reference only; not needed on Hostinger)
```

---

## User Roles

### Landlord
Single account (created directly in the database). Has full administrative access:
- Approve or reject tenant registration applications
- Assign tenants to rooms
- Create and manage bills (room-based or individual)
- Approve or reject payment submissions with optional notes
- Post announcements visible to all tenants
- Manage maintenance requests and complaints
- View audit logs of all system actions
- Manage the waiting list for room vacancies

### Tenant
Self-registers through the public registration form. Goes through a sequential approval flow before gaining full access:

1. **Registers** → account created with status `unverified`
2. **Verifies email** → status transitions to `pending`
3. **Completes personality questionnaire** (used for roommate compatibility matching)
4. **Landlord reviews and approves** → status becomes `approved`
5. **Landlord assigns a room** → tenant gains full portal access
6. **Moves out** → status set to `moved_out`; login is blocked

---

## Core Features

### Authentication
- Email + password login
- Email verification gate on registration (newly registered tenants cannot log in until email is verified)
- Optional TOTP 2FA (Google Authenticator); recovery codes generated on setup
- Password reset via email token
- Role hint on login form prevents cross-role credential use

### Billing
- Landlord creates bills either per room (shared among occupants) or per individual tenant
- Duplicate prevention: same bill name + period + room/tenant cannot be billed twice
- Bill statuses: `unpaid` → `pending_verification` (tenant pays) → `paid` / `partial`
- Overdue detection: cron job and computed column check `due_date < CURDATE()`
- 10% overdue penalty applied automatically via `process_overdue_penalties.php`
- `pending_verification` status is system-controlled only; landlords cannot set it manually

### Payments
- Tenant uploads payment proof (GCash screenshot, cash receipt, bank transfer)
- Bill transitions to `pending_verification`; landlord is notified and sees "Awaiting Review" count on the billing dashboard
- Landlord approves or rejects with optional note
- Partial payments supported; `amount_paid` field accumulates approved amounts
- On rejection, bill reverts to previous unpaid/partial/overdue status

### Rooms
- Room types: single or shared
- Configurable max occupancy and air-conditioning flag
- Roommate compatibility scoring via `CompatibilityService` (personality quiz answers)
- Rooms can be marked vacant/occupied automatically based on tenant assignments

### Complaints
- Tenant files complaint → landlord responds via in-thread conversation
- Status flow: `pending` → `in_progress` → `resolved`
- Both parties receive notifications on new messages

### Maintenance
- Tenant submits maintenance request with category and description
- Landlord marks as `acknowledged`, `in_progress`, or `completed`

### Announcements
- Landlord posts notices with priority level and optional event date
- Active/inactive toggle; visible to all approved tenants

### Notifications
- In-app notification bell for both roles
- Events: new bill, payment approved/rejected, complaint update, maintenance update, announcement posted
- Email notifications via `BoardTrackMail` for key events (bill issued, payment confirmed, guardian notice)

---

## Database Key Tables

| Table | Purpose |
|---|---|
| `users` | Both landlord and tenant accounts; includes status, role, TOTP fields |
| `tenants` | Extended tenant profile: room assignment, gender, preferences, guardian info |
| `rooms` | Room inventory with type, capacity, occupancy |
| `bills` | Billing records; supports room-based and individual billing types |
| `payments` | Tenant payment submissions linked to bills |
| `announcements` | Landlord notices |
| `complaints` | Tenant complaints with conversation thread |
| `complaint_messages` | Messages within a complaint thread |
| `maintenance` | Maintenance request tickets |
| `notifications` | In-app notification records per user |
| `audit_logs` | Immutable record of all significant system actions |
| `email_verifications` | One-time tokens for email verification |
| `password_resets` | One-time tokens for password reset |
| `waiting_list` | Tenants waiting for room assignment |
| `personality_answers` | Questionnaire answers used for roommate matching |
| `testimonials` | Tenant reviews visible on the public landing page |

### `users.status` Values

| Value | Meaning |
|---|---|
| `unverified` | Registered but email not yet confirmed — login blocked |
| `pending` | Email verified; awaiting landlord approval |
| `approved` | Approved by landlord; full tenant access |
| `rejected` | Registration rejected by landlord — login blocked |
| `moved_out` | Tenancy ended — login blocked |

---

## Session Variables

| Key | Set By | Purpose |
|---|---|---|
| `user_id` | `completeLogin()` | Authenticated user's ID |
| `user_name` | `completeLogin()` | Display name |
| `user_role` | `completeLogin()` | `'landlord'` or `'tenant'` |
| `user_email` | `completeLogin()` | User's email |
| `2fa_pending_user` | `loginPost()` | Temporary store during TOTP verification |
| `2fa_pending_expires` | `loginPost()` | Expiry timestamp (5 minutes) |
| `flash` | `flash()` helper | One-time UI messages |
| `form_old` | Registration error path | Repopulate form inputs after validation failure |

---

## Cron Job

`app/cron/process_overdue_penalties.php` should be scheduled to run daily (e.g., via crontab at midnight):

```bash
0 0 * * * php /path/to/boardtrack/app/cron/process_overdue_penalties.php
```

It marks past-due bills as `overdue` and applies a 10% compounding monthly penalty charge to qualifying bills.

---


*BoardTrack — internal documentation · last updated June 2026*
