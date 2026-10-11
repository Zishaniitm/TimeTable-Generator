# StudySync — Timetable & Study Planner Generator

StudySync builds a balanced weekly study timetable from your subjects and the hours each one needs.
Generate it in one click, drag blocks to fine-tune it, tick off sessions as you finish them, and watch your
weekly adherence on the Insights page.

**Stack:** HTML5 · CSS3 · JavaScript (ES6, no frameworks) · PHP 8 (PDO) · MySQL / MariaDB

## Features

- **Accounts** – email + password sign-up / sign-in (passwords hashed with `password_hash`, PHP sessions, CSRF protection)
- **Subjects** – add subjects with weekly hours and a colour tag; delete them any time
- **Auto-generated timetable** – a round-robin scheduler spreads each subject across your available days
- **Drag & drop** – move a block to any empty cell or drop it on another block to swap (works with mouse and touch)
- **Progress tracking** – mark sessions done; Insights shows planned vs. completed hours per subject, adherence %, and busiest days
- **Settings** – choose study days and daily start/end hours; delete your account (all data is removed)
- **Responsive + dark mode** – plain CSS3 (Grid, Flexbox, custom properties, `prefers-color-scheme`)

## Project structure

```
studysync/
├── index.php            Landing page
├── auth.php             Sign in / create account
├── logout.php
├── dashboard.php        Subjects + "Generate timetable"
├── timetable.php        Weekly grid (drag & drop)
├── insights.php         Charts & stats
├── settings.php         Study window + delete account
├── api/                 JSON endpoints used by the JavaScript
│   ├── subjects.php     add / delete / list subjects
│   ├── timetable.php    generate / move (swap) / toggle done / list slots
│   └── settings.php     save settings / delete account
├── includes/
│   ├── config.php       DB credentials (edit this)
│   ├── db.php           PDO connection
│   ├── auth.php         sessions, CSRF, helpers
│   ├── api.php          JSON/CSRF bootstrap for api/*
│   ├── scheduler.php    timetable generation algorithm
│   └── header.php / footer.php
├── assets/
│   ├── css/style.css
│   ├── js/              app.js (toasts + fetch helper), dashboard.js, timetable.js, settings.js
│   └── img/
├── database/schema.sql  Tables: users, subjects, timetable_slots
└── docs/PRD Thesis.pdf
```

## Setup (XAMPP / WAMP / LAMP)

**Requirements:** PHP 8.0+ with the `pdo_mysql` extension, and MySQL 5.7+ or MariaDB 10.3+.

1. **Copy the project** into your web root, e.g. `C:\xampp\htdocs\studysync` (or `/var/www/html/studysync`).
2. **Start Apache and MySQL** (from the XAMPP control panel).
3. **Create the database** – either open phpMyAdmin → *Import* → choose `database/schema.sql`, or run:
   ```sh
   mysql -u root -p < database/schema.sql
   ```
4. **Configure credentials** – open `includes/config.php` and set `DB_USER` / `DB_PASS` if they differ from the XAMPP defaults
   (`root` with an empty password). You can also set the `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` environment variables.
5. **Open** `http://localhost/studysync/` and create an account.

### Quick run without Apache

```sh
mysql -u root -p < database/schema.sql
php -S localhost:8000
# then open http://localhost:8000
```

## How the scheduler works

`includes/scheduler.php` takes your subjects, study days and daily hour window. It loops over subjects
(largest weekly hours first) placing one hour at a time on the **least-loaded day that doesn't already contain
that subject**, falling back to any open day only when unavoidable. Any hours that don't fit are reported
so you can widen your window in Settings.

## Security notes

- All SQL uses PDO prepared statements.
- Passwords are stored with `password_hash()` / checked with `password_verify()`; the session ID is regenerated on login.
- Every state-changing request (forms and API calls) requires a CSRF token.
- All output is escaped with `htmlspecialchars` (or `textContent` in JavaScript).
- Every query is scoped to the signed-in user's ID.
- `.htaccess` blocks direct web access to `includes/` and `database/` on Apache.
- For production: serve over HTTPS, use a dedicated DB user with a strong password (not `root`), and set `display_errors=Off`.

## What changed from the original version

This project was originally a React / TanStack Start / Supabase / Tailwind app generated with Lovable.
It has been rewritten as a classic PHP application:

| Before | Now |
| --- | --- |
| React + TypeScript + TanStack Router | HTML5 pages rendered by PHP |
| Tailwind CSS + shadcn/ui | Hand-written CSS3 (`assets/css/style.css`) |
| Supabase (Postgres, Auth, RLS) | MySQL/MariaDB + PHP sessions; per-user checks in PHP |
| `@dnd-kit` | Native Pointer Events drag & drop |
| Recharts | CSS-based bar chart and heat grid |
| Google sign-in via Lovable OAuth | **Removed** (email + password only) |
| Server functions for account deletion | `api/settings.php` (`ON DELETE CASCADE`) |

Existing Supabase data is **not** migrated automatically; users need to sign up again.

## Roadmap

- [x] Email/password sign-up, sign-in and sign-out
- [x] Subjects, timetable generation, drag-to-swap, mark-as-done
- [x] Insights and settings, account deletion
- [ ] Google sign-in (OAuth 2.0)
- [ ] Password reset by email
- [ ] Export timetable to PDF / iCal
