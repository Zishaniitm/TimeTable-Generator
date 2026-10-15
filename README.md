# StudySync — Timetable & Study Planner Generator

<p align="center">
  <img src="assets/img/apple-touch-icon.png" width="100" height="100" alt="StudySync Logo" style="border-radius: 20px;">
</p>

<p align="center">
  <strong>Intelligent, clash-free weekly study scheduling tailored for college and university students.</strong><br>
  Generate balanced routines in one click, drag-and-drop to adjust, tick off completed sessions with instant undo protection, and track your weekly academic adherence.
</p>

<p align="center">
  <a href="https://studysync.site.je/"><img src="https://img.shields.io/badge/Live%20Demo-studysync.site.je-4f46e5?style=for-the-badge&logo=googlechrome&logoColor=white" alt="Live Demo"></a>
  <img src="https://img.shields.io/badge/PHP-8.0%2B-777bb4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8">
  <img src="https://img.shields.io/badge/MySQL-5.7%2B-4479a1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/JavaScript-ES6%20Vanilla-f7df1e?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript Vanilla">
  <img src="https://img.shields.io/badge/CSS3-Modern%20Design-1572b6?style=for-the-badge&logo=css3&logoColor=white" alt="CSS3">
  <img src="https://img.shields.io/badge/Hosting-InfinityFree-00c853?style=for-the-badge" alt="InfinityFree">
</p>

---

## 🌐 Live Website

- **Live URL:** [https://studysync.site.je/](https://studysync.site.je/)
- **Hosting Platform:** InfinityFree (LAMP Stack: Linux, Apache, MySQL, PHP 8)
- **Status:** Active & Publicly Accessible

---

## ✨ Features & Highlights

### ⚡ 1-Click Clash-Free Auto Scheduler
- A deterministic **round-robin algorithm** cycles through your subjects (largest weekly target hours first).
- Every study hour is placed on the **least-loaded day that does not already hold that subject**, preventing exam-week cramming and burnout.
- Fully respects custom study days (e.g., weekdays only or all 7 days) and daily study hours (e.g., 4:00 PM to 10:00 PM).

### 🖐️ Fluid Drag, Drop & Swap
- Move any study session to an empty slot or drop it onto another block to **swap both sessions instantly**.
- Built with the native **HTML5 Pointer Events API** (`pointerdown`, `pointermove`, `pointerup`), providing seamless support across desktop mouse, trackpads, and mobile touchscreens.

### 🎯 One-Tap Mark Done with Instant Undo
- Check off study blocks as you complete them to track progress.
- **Accidental Click Protection:** Accidentally marked a block as done? Simply click the block or checkmark again to **immediately undo** and restore it to active.
- **Interactive Toast Notification:** A toast appears on completion with an actionable **`[↩ Undo]`** button for 1-tap instant rollback.

### 📅 Actionable Student Dashboard with Today's Schedule
- View today's active study sessions right on your dashboard based on the current day of the week.
- Tick off or undo today's tasks directly without switching pages.
- Real-time stat pills show: enrolled courses, total weekly target hours, scheduled blocks, and completed sessions.

### 📊 Visual Analytics & Burnout Heatmap
- **Planned vs. Completed Bar Chart:** Compare required target hours against actual completed study time per subject.
- **Weekly Adherence Meter:** Dynamic adherence percentage tracking with student milestone badges (*"Top Performer"*, *"On Track"*, *"In Progress"*).
- **Daily Workload Heatmap:** Visual representation of which days are heaviest to prevent fatigue.

### 🌓 Eye-Friendly Dark & Light Mode
- One-click toggle switch in the navigation bar to switch between calm light mode and high-contrast dark mode for late-night study sessions.
- Flash-free theme initialization script prevents bright white flashes on reload.
- Remembers user theme preferences via `localStorage`.

### 🔒 Enterprise-Grade Security
- All database queries use **PDO prepared statements** with bound parameters (SQL injection immune).
- Secure passwords hashed with `password_hash(PASSWORD_DEFAULT)` and verified with `password_verify()`.
- Session ID regenerated on login (`session_regenerate_id(true)`) to prevent session fixation.
- **CSRF Protection:** Cryptographic tokens (`random_bytes(32)`) validated on every state-changing POST and JSON API request.
- Strict user-isolation: all queries are hard-scoped to `$_SESSION['user_id']`.
- Apache `.htaccess` directives deny public HTTP access to internal backend and SQL schema directories.

---

## 🛠️ Technology Stack

| Layer | Technology | Details |
| :--- | :--- | :--- |
| **Backend** | **PHP 8.0+ (PDO)** | Object-oriented PDO database wrapper, native sessions, JSON REST endpoints. |
| **Database** | **MySQL 5.7+ / MariaDB 10.3+** | InnoDB storage engine with foreign keys (`ON DELETE CASCADE`) and `utf8mb4` unicode charset. |
| **Frontend UI** | **Semantic HTML5 & Modern CSS3** | Custom CSS design system, CSS Grid, Flexbox, glassmorphic headers, OKLCH and sRGB color gradients. |
| **Client Scripts** | **Vanilla ES6 JavaScript** | Zero npm dependencies or heavy build steps; uses Fetch API, native Pointer Events, and asynchronous DOM manipulation. |
| **Typography** | **Google Fonts** | `Sora` for display headers and `Plus Jakarta Sans` for body typography. |
| **Hosting** | **InfinityFree / Apache** | Shared LAMP architecture with `.htaccess` security and SSL certificates. |

---

## 📁 Project Structure

```
studysync/
├── index.php                 # Aesthetic landing page with interactive hero demo & FAQ
├── landing.php               # Routing alias pointing to index.php
├── auth.php                  # Tabbed Sign In / Create Account page
├── dashboard.php             # Student dashboard, today's schedule & course manager
├── timetable.php             # Weekly interactive grid (drag, drop, mark done & undo)
├── insights.php              # Academic performance charts, adherence & heatmap
├── settings.php              # Study days, window hours configuration & account deletion
├── logout.php                # Session destruction and secure sign out
├── api/                      # JSON REST API endpoints (CSRF + session secured)
│   ├── subjects.php          # Add, delete, and list courses
│   ├── timetable.php         # Generate, move/swap, and toggle completion/undo
│   └── settings.php          # Save study windows and delete account
├── includes/                 # Core backend modules (protected by .htaccess)
│   ├── .htaccess             # Denies direct HTTP web access to includes/
│   ├── config.php            # Environment configuration (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS)
│   ├── db.php                # Singleton PDO connection handler
│   ├── auth.php              # Session management, CSRF helpers, and user authentication
│   ├── api.php               # JSON response handler and API CSRF validation
│   ├── scheduler.php         # Core round-robin timetable distribution algorithm
│   └── header.php / footer.php # Shared topbar, navigation, theme toggle, and toast system
├── assets/
│   ├── css/style.css         # Complete responsive design system (light & dark mode)
│   ├── js/
│   │   ├── app.js            # Toast notifications with undo callback, API helper, theme switcher
│   │   ├── dashboard.js      # Course management, today's schedule toggle, and generation triggers
│   │   ├── timetable.js      # Weekly grid renderer, pointer drag-and-drop, and click-to-undo
│   │   └── settings.js       # Study window chips and deletion dialogs
│   └── img/
│       ├── favicon.svg       # Scalable vector favicon
│       ├── favicon.ico       # Multi-resolution ICO (16x16 to 256x256)
│       ├── favicon-32x32.png # Standard 32px PNG favicon
│       └── apple-touch-icon.png # 180x180 High-DPI mobile touch icon
└── database/
    ├── .htaccess             # Protects database schema from direct downloads
    └── schema.sql            # Relational tables: users, subjects, timetable_slots
```

---

## 💻 Local Development Setup

### Prerequisites
- PHP 8.0 or higher with `pdo_mysql` extension enabled
- MySQL 5.7+ or MariaDB 10.3+
- Web server: Apache (XAMPP / WAMP / LAMP) or the built-in PHP development server

### Option A: Using XAMPP / WAMP
1. Copy or clone the project folder into your web root:
   ```sh
   # For XAMPP on Windows:
   C:\xampp\htdocs\studysync
   ```
2. Start **Apache** and **MySQL** from your XAMPP Control Panel.
3. Open **phpMyAdmin** (`http://localhost/phpmyadmin/`), create a database named `studysync`, and import `database/schema.sql`.
4. Check `includes/config.php` — the default credentials (`root` with an empty password) will work out of the box.
5. Open your browser and navigate to:
   ```text
   http://localhost/studysync/
   ```

### Option B: Quick Run with Built-in PHP Server (No Apache needed)
1. Import the database schema:
   ```sh
   mysql -u root -p < database/schema.sql
   ```
2. Navigate into the `studysync` directory and run:
   ```sh
   php -S localhost:8000
   ```
3. Open `http://localhost:8000` in your web browser.

---

## 🚀 Production Hosting on InfinityFree

1. **Sign Up:** Register at [InfinityFree](https://www.infinityfree.com/) and create a free account with a custom subdomain (e.g., `studysync.site.je`).
2. **Create MySQL Database:** In the InfinityFree Control Panel (vPanel), navigate to **MySQL Databases** and create a database (e.g., `if0_43143714_studysync`).
3. **Import Database Schema:** 
   - Open **phpMyAdmin** from the vPanel.
   - Select your database, click **Import**, choose `database/schema.sql` (ensure `CREATE DATABASE` and `USE` lines are commented out), and click **Go**.
4. **Update Credentials:** Edit `includes/config.php` with your InfinityFree MySQL details:
   ```php
   define('DB_HOST', 'sqlXXX.infinityfree.com'); // Your MySQL Hostname
   define('DB_PORT', '3306');
   define('DB_NAME', 'if0_43143714_studysync');  // Your Database Name
   define('DB_USER', 'if0_43143714');           // Your Account Username
   define('DB_PASS', 'YOUR_VPANEL_PASSWORD');
   ```
5. **Upload Files:** Upload the contents of `studysync/` directly into the remote **`htdocs/`** directory using the Monsta File Manager or FTP (FileZilla).

---

## 🧠 How the Scheduling Algorithm Works

The scheduler in `includes/scheduler.php` implements an anti-cramming round-robin allocation:
1. **Capacity Check:** Computes `total_capacity = count(study_days) * (day_end_hour - day_start_hour)`.
2. **Prioritization:** Sorts courses by weekly required hours in descending order so that the heaviest courses get scheduled first.
3. **Least-Loaded Day Selection:** Iterates through each subject one hour at a time and places it on the **open day with the minimum current hours that doesn't already contain that subject**.
4. **Graceful Overflow:** If a subject cannot avoid sharing a day, it falls back to the day with the lowest load. If total hours exceed window capacity, unplaced hours are reported to the user with a prompt to adjust settings.

---

## 📄 License & Attribution

This project is open-source and created for educational purposes. Feel free to use, modify, and extend it for academic submissions or personal study planning.
