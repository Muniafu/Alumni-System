# Alumni & Student Management Platform

A full-featured alumni engagement platform for the Muniafu community: approval-based registration, job boards, events with RSVPs, a moderated forum, structured mentorship, and an admin console with reporting — built on Laravel 10.

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 10.49 (PHP 8.1+) |
| Auth scaffolding | Laravel Breeze (Blade) with a custom approval pipeline |
| Authorization | spatie/laravel-permission 6.21 (role-based, single-source-of-truth matrix) |
| Database | MySQL |
| Frontend | Blade + Bootstrap 5 |
| Queues | Database-backed queue (`QUEUE_CONNECTION=database`) |
| Exports | rap2hpoutre/fast-excel (CSV/Excel reports) |

## Features

### Auth & Approval Pipeline
- Registration with automatic role assignment (alumni vs student by graduation year).
- **First registered user becomes the bootstrap admin** and is auto-approved.
- Every other user registers into a *pending approval* state — they see a clean pending page, never a broken one.
- Admins approve or reject applicants (individually or in bulk); approval/rejection notifications are queued and delivered by email **and** in-app.
- Soft-deleted accounts with restore support.

### Roles & Permissions
- Three roles: `admin`, `alumni`, `student` (plus the pending state).
- Permission names are hyphenated and match every `Gate::authorize()` / `@can` call exactly — the matrix lives in `RolesAndPermissionsSeeder::MATRIX` and is the single source of truth. Re-seeding converges to exactly that matrix and removes stale permissions.

### Jobs
- Alumni and admins post jobs and internships (remote flag, contact email, skills, application deadline).
- Students and alumni apply once (unique constraint per posting + user).
- Resume uploads stored on a private disk.
- `active()` scope: only postings with a future deadline count as active.

### Events
- Public event listings for guests; capacity-limited RSVPs (one per user, enforced in the DB).
- Online events keep their meeting URL visible to attendees only.

### Forum
- Categories, threads, and replies with pinned and locked threads.
- Author-only editing (admins moderate everything), auto-subscribe on reply, subscribe/unsubscribe.
- Soft deletes: removing a thread's last post removes the thread.

### Mentorship
- Alumni sign up as mentors via mentor profiles (expertise + bio) — only signed-up mentors appear in "Find Mentors".
- Students send mentorship requests; mentors accept or reject. Rejection preserves the original message and allows re-requesting.
- Accepted requests become linked mentorships with goal, start date, and end date. Status changes notify both parties exactly once.

### Profiles
- Editable profile details: education, certifications, skills, interests, and named social links (LinkedIn, GitHub, Twitter, etc.).
- Password changes and account deletion (soft delete, password-confirmed).

### Admin Console
- Dashboard with aggregate stats (users, active jobs, average applications, average attendance).
- Pending approvals with individual and bulk approve/reject.
- User management with search, role/status filters (including a deleted-users view with restore), pagination, create/edit users.
- Per-user detail view: roles, profile, academic info, and activity (events, job applications, forum posts) with eager loading.
- CSV/Excel exports of users and other reports.

## Getting Started

### Requirements
- PHP 8.1+ with required Laravel extensions
- Composer
- Node.js & npm
- MySQL

### Installation

```bash
git clone https://github.com/Muniafu/Alumni-System.git
cd Alumni-System

composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate
```

Configure the database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alumni_system
DB_USERNAME=laravel
DB_PASSWORD=secret

QUEUE_CONNECTION=database   # required — notifications are queued
MAIL_MAILER=log             # local testing; use a real SMTP provider in production
```

Create the database, then migrate and seed:

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS alumni_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate:fresh --seed
php artisan permission:cache-reset
php artisan storage:link
```

Run the app (two terminals):

```bash
php artisan serve       # terminal 1 — http://127.0.0.1:8000
php artisan queue:work  # terminal 2 — REQUIRED for queued email notifications
```

### Demo Accounts

All seeded accounts use the password `password`.

| Email | Role | State |
|---|---|---|
| `admin@alumni.edu` | admin | approved |
| `alumni@alumni.edu` | alumni | approved |
| `student@alumni.edu` | student | approved |
| `pending@alumni.edu` | student | **pending** — approve her from the admin panel to demo the pipeline |

Seed data also includes events (with RSVPs), job postings with deadlines, forum threads, and an active mentorship with a mentor profile, so every dashboard and admin view has real content.

## Testing

The suite (45 tests) covers the auth/approval pipeline, the permission matrix, profiles, and the forum module:

```bash
php artisan test
```

Tests run against a separate `alumni_system_test` database. Grant the test user access once:

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS alumni_system_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON alumni_system_test.* TO 'laravel'@'localhost';
GRANT ALL PRIVILEGES ON alumni_system_test.* TO 'laravel'@'127.0.0.1';
FLUSH PRIVILEGES;"
```

## Project Structure

```
app/
├── Console/            # Commands
├── Http/
│   ├── Controllers/    # Per-module controllers (Admin, Auth, Forum, ...)
│   └── Middleware/     # Approved + role middleware
├── Models/              # User, Profile, Event, JobPosting, ForumThread, Mentorship, ...
├── Notifications/      # Queued (mail + database channel)
└── Policies/           # Per-model authorization
database/
├── migrations/
└── seeders/            # RolesAndPermissionsSeeder (the MATRIX) + demo data
resources/views/         # Blade templates (Bootstrap 5)
routes/web.php
```

## Notes

- **Queue worker:** approval/rejection and other notifications are queued — run `php artisan queue:work` (Supervisor in production).
- **Caches:** after permission changes run `php artisan permission:cache-reset`.
- **Laravel 10** is past upstream end-of-life; an upgrade to 11/12 is planned post-deployment.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).