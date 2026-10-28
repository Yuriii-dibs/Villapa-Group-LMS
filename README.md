# Villapa Group Learning Management System (LMS)

A modern, responsive, and lightweight Learning Management System built with **Laravel 12**, **Tailwind CSS**, **Alpine.js**, and **MySQL/MariaDB**. Designed to provide a streamlined educational experience without heavyweight external SaaS dependencies or paid services.

---

## Key Features & Roles

### 1. Student
- **Catalog & Enrollment**: Browse published courses with search and category filtering; enroll in courses with instant access.
- **Learning Interface**: Module-based syllabus, lesson reader with markdown/HTML content, "Previous", "Next", and "Mark as Complete" navigation, and automated progress percentage tracking.
- **Interactive Chat**: Course-specific discussion rooms for enrolled peers and instructors; direct 1-on-1 messaging with course instructors.
- **Calendar & Deadlines**: Unified monthly calendar with colored tags for personal, course, and general school events, plus upcoming 7-day agendas.
- **Online Classes**: Join live video classrooms embedded directly via Jitsi Meet (or open external conference links like Zoom/Google Meet) within a 15-minute start window.
- **Account Management**: Profile editing, email updates, and secure password changes.

### 2. Instructor
- **Course Authoring**: Create courses with titles, categories, difficulty levels, descriptions, and thumbnail uploads.
- **Curriculum Builder**: Organize modules and lessons with intuitive up/down ordering and rich text content.
- **Approval Workflow**: Save drafts privately and submit completed courses for Admin review.
- **Virtual Classrooms**: Schedule live sessions (Jitsi Meet or external link), launch sessions, and view real-time student attendance logs.
- **Communication & Calendar**: Broadcast course announcements/events to enrolled students, join course chat rooms, and direct-message students.
- **Student Analytics**: Track enrolled student counts and individual lesson progress.

### 3. Administrator (and Super Admin)
- **User Governance**: Searchable user directory with role filtering, account activation, suspension toggles, instructor approvals, and role assignments (with built-in self-protection preventing admins from modifying or suspending themselves).
- **Course Moderation**: Review pending courses, approve for immediate catalog publication, or reject with required feedback reasons. Unpublish active courses when needed.
- **Category Management**: Create, edit, and safely delete categories (preventing deletion if courses are assigned).
- **Institution Insights**: Aggregate metrics including total students, active instructors, published courses, pending submissions, and recent signups.
- **School Announcements**: Post general calendar events visible to all users across the institution.

---

## Prerequisites

- **PHP 8.2 or higher** with `pdo_mysql`, `mbstring`, `openssl`, and `gd` extensions enabled.
- **Composer** (PHP dependency manager).
- **Node.js (v18+) & NPM** (frontend asset compilation).
- **XAMPP** (or any local Apache + MySQL / MariaDB stack).

---

## XAMPP & phpMyAdmin Setup

1. Open the **XAMPP Control Panel**.
2. Click **Start** for both **Apache** and **MySQL**.
3. Open your browser and navigate to **[http://localhost/phpmyadmin](http://localhost/phpmyadmin)**.
4. Click on **Databases** (or "New" in the left sidebar).
5. In the **Database name** field, enter exactly:
   ```text
   lms_app
   ```
6. Select collation `utf8mb4_unicode_ci` and click **Create**.

---

## Installation & Configuration

Clone or navigate into the project directory in your terminal (e.g. `c:\xampp\htdocs\Villapa_Group_LMS-app`):

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Configure Environment (`.env`)
Copy `.env.example` to create your local `.env` file:
```bash
# Windows PowerShell / CMD
copy .env.example .env
```

Ensure your `.env` database parameters point to your XAMPP MySQL database:
```env
APP_NAME="Villapa Group LMS"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lms_app
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=public
```

### 3. Generate App Key & Link Storage
```bash
php artisan key:generate
php artisan storage:link
```

### 4. Run Migrations and Seed Sample Data
```bash
php artisan migrate
php artisan db:seed
```

### 5. Build Frontend Assets
For production-optimized assets:
```bash
npm run build
```
*(Or use `npm run dev` if you are actively editing CSS/JS files).*

### 6. Start the Development Server
```bash
php artisan serve
```
Open **[http://localhost:8000](http://localhost:8000)** in your browser.

---

## Sample Login Credentials

All seeded accounts use the password: `Password123!`

| Role | Name | Email Address | Password | Purpose / Notes |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | Super Administrator | `superadmin@school.test` | `Password123!` | Full unrestricted platform administration |
| **Super Admin** | System Administrator | `admin@villapa-lms.test` | `Password123!` | Alternate super-admin credential |
| **Admin** | School Administrator | `admin@school.test` | `Password123!` | User management, course approvals, categories |
| **Instructor** | Prof. Alex Instructor | `instructor@school.test` | `Password123!` | Active instructor with published courses & classes |
| **Instructor** | Dr. Maria Santos | `instructor2@school.test` | `Password123!` | Second instructor (Design Systems course) |
| **Pending Instructor** | Mark Pending | `pending.instructor@school.test` | `Password123!` | Tests Admin instructor approval workflow |
| **Student** | Sam Student | `student@school.test` | `Password123!` | Enrolled in Course 1 with partial progress |
| **Student** | Jane Student | `student2@school.test` | `Password123!` | Second enrolled student for testing multi-user chat |
| **Suspended Student** | Suspended Student | `suspended@school.test` | `Password123!` | Tests authentication block for suspended accounts |

---

## Running the Automated Test Suite

The application includes an extensive end-to-end feature and unit test suite covering authentication, authorization, access control, course workflows, learning progression, chat isolation, calendar aggregation, and online classes.

To run the entire test suite:
```bash
php artisan test
```

To run a specific test file:
```bash
php artisan test tests/Feature/ChatCalendarAndOnlineClassesTest.php
```

---

## Architecture & Known Limitations

- **Lightweight Polling Chat**: Chat and direct messaging utilize clean RESTful JSON endpoints with lightweight client-side polling (`fetch` every 3 seconds) rather than persistent WebSocket daemons (Pusher, Reverb, or Socket.io). This ensures effortless deployment on standard Apache/XAMPP environments without background service orchestrators.
- **Free Video Conferencing**: The virtual classroom embeds the free public Jitsi Meet service (`meet.jit.si`) or supports external meeting URLs (Zoom, Microsoft Teams, Google Meet), eliminating the need for paid SDK licenses or external API credentials.
- **No Assignments/Quizzes**: Assignments, quizzes, and grading have been cleanly decommissioned in favor of focused self-paced modular lessons and syllabus completion tracking.
- **Local File Storage**: Course thumbnails and attachments are stored locally via Laravel's `public` disk (`storage/app/public` symlinked to `public/storage`). For multi-server production deployments, configure an S3-compatible cloud driver in `.env`.
