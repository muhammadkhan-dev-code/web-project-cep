# 🎓 Student Job Portal
**Course:** Web Engineering — Complex Engineering Problem (CEP)  
**Institution:** Mehran University of Engineering and Technology, Jamshoro  
**Department:** Software Engineering  
**Semester:** 7th | Year: 4th (Final)  
**Instructor:** Dr. Din Muhammad  
**Total Marks:** 15

---

## 📌 Project Overview

The **Student Job Portal** is a full-stack web application that connects students with employers. Students can browse and apply for part-time jobs and internships, while employers can post listings and manage applications. An admin panel provides platform-wide moderation.

---

## ✅ Completed Tasks

### Task 1 — Responsive Front-End Design *(CEP A1, A3, A9)*

**What was done:**
- Designed a clean, modern UI using **HTML5**, **CSS3**, and **Bootstrap 5**
- Built **3 separate role-based views**: Student, Employer, and Admin
- Applied **client-side form validation** using vanilla JavaScript on:
  - Registration form (name, email, password match, role selection)
  - Login form (email format, required fields)
  - Job application form (cover note minimum 20 characters)
  - Post job form (all fields required, future deadline enforced)
- Used a consistent **color system** via CSS custom properties (variables)
- Built reusable UI components: stat cards, job cards, badges, alerts, sidebar, navbar
- Fully **responsive layout** — works on mobile, tablet, and desktop

**Color Scheme:**

| Token | Hex | Purpose |
|---|---|---|
| Primary (Blue) | `#1B4FD8` | Buttons, links, active states |
| Accent (Orange) | `#F97316` | Employer CTAs, highlights |
| Success (Green) | `#16A34A` | Accepted status, active badges |
| Danger (Red) | `#DC2626` | Rejected, errors, ban actions |
| Background | `#F8FAFC` | Page background |

**Typography:**
- Headings: `Plus Jakarta Sans` (Google Fonts) — bold, modern
- Body: `DM Sans` (Google Fonts) — clean and readable

---

### Task 2 — User Registration & Login *(CEP A1, A3)*

**What was done:**
- Implemented **registration** for two roles: Student and Employer
- Role selection via interactive toggle tabs (no page reload)
- Server-side validation: required fields, valid email, min 6-char password, password match, no duplicate emails
- Passwords hashed using PHP's `password_hash()` (bcrypt) — never stored as plaintext
- Login verified with `password_verify()` against hashed password
- **Session management** using PHP `$_SESSION`:
  - Stores `user_id`, `user_name`, and `role` on login
  - Role-based redirect after login (Student → `/student/`, Employer → `/employer/`, Admin → `/admin/`)
- `requireLogin()` and `requireRole($role)` guards on every protected page
- **Banned user check** — banned accounts are blocked at login
- Logout clears session and redirects to homepage

---

### Task 3 — Job Listing System *(CEP A1, A7)*

**What was done:**
- Employers can **post jobs** with: title, description, type (part-time / internship), location, and deadline
- All listings stored in MySQL `jobs` table
- Students can **browse all active jobs** (deadline not expired, not deleted)
- **Search** by title or location (live query with `LIKE`)
- **Filter** by job type (part-time / internship)
- Expired listings (past deadline) are automatically hidden from students
- Each job card shows employer name, type badge, location, deadline, and application count

---

### Task 4 — Application System *(CEP A2, A8)*

**What was done:**
- Students can apply to jobs with a **cover note** (min 20 characters)
- **Duplicate application prevention** using `UNIQUE KEY (job_id, student_id)` in the database — enforced both in PHP and at DB level
- Employers can **view all received applications** per job listing
- Employers can **update application status** to: Pending, Accepted, or Rejected
- Applied jobs show an "Applied" badge instead of the Apply button for students

---

### Task 5 — Application Status Tracking *(CEP A3, A9)*

**What was done:**
- Students have a **My Applications** dashboard listing all submitted applications
- Each entry shows: job title, employer, type, location, applied date, and **current status**
- Status updates from the employer side are reflected immediately on the student side (shared DB)
- Status displayed with color-coded badges: gray (Pending), green (Accepted), red (Rejected)
- Student dashboard summary cards show total, pending, accepted, and rejected counts

---

### Task 6 — Admin Panel *(CEP A3, A9)*

**What was done:**
- Admin can **view all registered users** (students and employers) with search and role filter
- Admin can **ban** or **unban** user accounts (banned users cannot log in)
- Admin can **permanently delete** user accounts
- Admin can **remove spam/fake job listings** (soft delete via `is_deleted` flag)
- Admin dashboard shows platform-wide stats: total students, employers, active jobs, applications, banned users
- Admin role is protected — no registration route; seeded directly in the database

---

## 🛠️ Tools & Technologies Used

| Category | Tool / Technology |
|---|---|
| **Frontend** | HTML5, CSS3, Bootstrap 5.3 |
| **Icons** | Font Awesome 6.4 |
| **Fonts** | Google Fonts (Plus Jakarta Sans, DM Sans) |
| **Backend** | PHP 8.x (procedural with MySQLi) |
| **Database** | MySQL 8.x |
| **Local Server** | XAMPP (Apache + MySQL) |
| **Session Auth** | PHP `$_SESSION` with role-based guards |
| **Password Security** | PHP `password_hash()` / `password_verify()` (bcrypt) |
| **Form Validation** | Client-side (JavaScript) + Server-side (PHP) |
| **SQL Injection Prevention** | MySQLi Prepared Statements (`bind_param`) |
| **Version Structure** | Manual file-based MVC-style structure |

---

## 📁 Project Structure

```
student_job_portal/
├── index.php                   # Login page
├── register.php                # Registration (Student & Employer)
├── logout.php                  # Session destroy & redirect
├── database.sql                # Database schema + default admin seed
│
├── css/
│   └── style.css               # All custom styles & CSS variables
│
├── includes/
│   ├── db.php                  # MySQL connection (configure credentials here)
│   ├── auth.php                # Session helpers, requireLogin(), requireRole()
│   └── navbar.php              # Shared responsive navbar (all roles)
│
├── student/
│   ├── dashboard.php           # Stats overview + recent applications
│   ├── jobs.php                # Browse & search/filter job listings
│   ├── apply.php               # Apply to a job with cover note
│   └── my_applications.php     # Track all application statuses
│
├── employer/
│   ├── dashboard.php           # Stats + recent applicant activity
│   ├── post_job.php            # Post new job listing form
│   ├── my_jobs.php             # View, manage & delete own listings
│   └── applications.php        # View applicants + update status
│
└── admin/
    ├── dashboard.php           # Platform-wide stats overview
    ├── users.php               # Manage users (ban/unban/delete)
    └── jobs.php                # Manage all job listings (remove spam)
```

---

## 🗄️ Database Schema

### `users`
| Column | Type | Description |
|---|---|---|
| id | INT PK AUTO_INCREMENT | User ID |
| full_name | VARCHAR(100) | Full name or company name |
| email | VARCHAR(150) UNIQUE | Login email |
| password | VARCHAR(255) | Bcrypt hashed password |
| role | ENUM(student, employer, admin) | User role |
| is_banned | TINYINT(1) | 0 = active, 1 = banned |
| created_at | TIMESTAMP | Registration time |

### `jobs`
| Column | Type | Description |
|---|---|---|
| id | INT PK AUTO_INCREMENT | Job ID |
| employer_id | INT FK → users.id | Posting employer |
| title | VARCHAR(200) | Job title |
| description | TEXT | Full job description |
| type | ENUM(part-time, internship) | Job type |
| location | VARCHAR(100) | City or Remote |
| deadline | DATE | Application deadline |
| is_deleted | TINYINT(1) | Soft delete flag |
| created_at | TIMESTAMP | Post time |

### `applications`
| Column | Type | Description |
|---|---|---|
| id | INT PK AUTO_INCREMENT | Application ID |
| job_id | INT FK → jobs.id | Applied job |
| student_id | INT FK → users.id | Applicant |
| cover_note | TEXT | Student's cover note |
| status | ENUM(pending, accepted, rejected) | Current status |
| applied_at | TIMESTAMP | Application time |
| UNIQUE | (job_id, student_id) | Prevents duplicate applications |

---

## ⚙️ Setup Instructions

### Prerequisites
- XAMPP (or WAMP) with Apache and MySQL running
- PHP 7.4 or higher

### Steps

**1. Import the database**
- Open `http://localhost/phpmyadmin`
- Create a new database named `student_job_portal`
- Click **Import** → select `database.sql` → click Go

**2. Configure database credentials**

Open `includes/db.php` and update:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // Leave blank for default XAMPP
define('DB_NAME', 'student_job_portal');
```

**3. Place files in htdocs**

Copy the `student_job_portal/` folder into:
```
C:\xampp\htdocs\student_job_portal\
```

**4. Run the project**

Open your browser and go to:
```
http://localhost/student_job_portal/
```

### Default Admin Account
| Field | Value |
|---|---|
| Email | admin@portal.com |
| Password | password |

> ⚠️ Change the admin password after first login for security.

---

## 🔒 Security Measures Implemented

- **Prepared Statements** on all database queries — prevents SQL Injection
- **`password_hash()` / `password_verify()`** — passwords never stored as plaintext
- **`htmlspecialchars()`** on all user output — prevents XSS (Cross-Site Scripting)
- **Role-based access control (RBAC)** — every page checks session role
- **Session-based authentication** — no sensitive data in URLs or cookies
- **Duplicate application constraint** — enforced at both PHP and database level

---

## 👥 CEP Characteristics Addressed

| Code | Characteristic | How Addressed |
|---|---|---|
| CEP-A1 | Depth of Knowledge | PHP, MySQL, sessions, RBAC, responsive design |
| CEP-A2 | Conflicting Requirements | Balanced usability vs. security; simplicity vs. features |
| CEP-A3 | Depth of Analysis | Multi-role data flow, filtering logic, status tracking |
| CEP-A4 | Infrequently Encountered | Duplicate application prevention, concurrent submissions |
| CEP-A5 | Beyond Standard Practice | Role-based dashboards + application lifecycle tracking |
| CEP-A6 | Diverse Stakeholders | Student, Employer, Admin — each with unique access |
| CEP-A7 | Interdependence | Auth, job listing, applications, admin all interconnected |
| CEP-A8 | Significant Consequences | Secure data handling prevents missed opportunities & leaks |
| CEP-A9 | Judgement in Decision Making | Schema design, access control logic, UI layout decisions |

---

## 📋 Rubric Coverage

| Rubric | Feature | Status |
|---|---|---|
| R1 — UI/UX & Responsiveness | Bootstrap 5, custom CSS, mobile-first layout | ✅ Complete |
| R2 — Registration & Role Management | PHP sessions, bcrypt, role guards, ban system | ✅ Complete |
| R3 — Job Listing & Search | Post jobs, search, filter, deadline enforcement | ✅ Complete |
| R4 — Application, Status Tracking, Admin | Apply, track status, admin ban/remove | ✅ Complete |
| R5 — Viva Voce | Prepare to explain all code logic and decisions | 🔜 Presentation |

---

*Developed for Web Engineering CEP — Mehran University of Engineering and Technology, Jamshoro*