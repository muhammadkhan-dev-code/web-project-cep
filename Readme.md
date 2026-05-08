# 🎓 Student Job Portal

A simple and easy way for students to find jobs and internships!

---

## What is this project?

The **Student Job Portal** is a website where:

- **Students** can search for jobs and apply to them
- **Employers** can post job openings and manage applications
- **Admins** can manage users and job listings

---

## Main Features

### 1️⃣ Sign Up & Login

- Easy registration for students and employers
- Choose your role when signing up
- Secure password protection
- Each user type sees their own dashboard

### 2️⃣ Post Jobs (For Employers)

- Employers can post new jobs with:
  - Job title
  - Job description
  - Type (Part-time or Internship)
  - Location
  - Application deadline

### 3️⃣ Browse & Apply (For Students)

- View all available jobs
- Search jobs by title or location
- Filter by job type
- Apply with a cover note
- Can't apply to the same job twice

### 4️⃣ Track Applications (For Students)

- See all jobs you applied to
- Check if your application is Pending, Accepted, or Rejected
- View detailed information about each application

### 5️⃣ Manage Applications (For Employers)

- See who applied to your jobs
- Accept or reject applications
- Update application status anytime

### 6️⃣ Admin Panel

- View all users and jobs on the platform
- Delete fake or spam job listings
- Ban or unban user accounts

---

## Design & Colors

The website uses:

- **Blue** (#1B4FD8) — Main color for buttons and links
- **Orange** (#F97316) — Special highlights and employer features
- **Green** (#16A34A) — Accepted status
- **Red** (#DC2626) — Rejected status

Works on all devices:

- 📱 Mobile phones
- 💻 Tablets
- 🖥️ Desktop computers

---

## How to Use

### For Students:

1. Sign up as a Student
2. Go to "Browse Jobs"
3. Search or filter jobs
4. Click "Apply Now"
5. Write a cover note
6. Check "My Applications" to track status

### For Employers:

1. Sign up as an Employer
2. Click "Post Job"
3. Fill in job details
4. Submit
5. View applications in "My Jobs"
6. Accept or reject applicants

### For Admins:

1. Login with admin account
2. View all users and jobs
3. Delete spam listings
4. Ban users if needed

---

## Safety & Security

- Passwords are encrypted (never stored as plain text)
- You can't apply to the same job twice
- Only approved users can post jobs
- Admin can remove fake accounts

---

### Task 6 — Admin Panel _(CEP A3, A9)_

**What was done:**

- Admin can **view all registered users** (students and employers) with search and role filter
- Admin can **ban** or **unban** user accounts (banned users cannot log in)
- Admin can **permanently delete** user accounts
- Admin can **remove spam/fake job listings** (soft delete via `is_deleted` flag)
- Admin dashboard shows platform-wide stats: total students, employers, active jobs, applications, banned users
- Admin role is protected — no registration route; seeded directly in the database

---

## 🛠️ Tools & Technologies Used

| Category                     | Tool / Technology                                    |
| ---------------------------- | ---------------------------------------------------- |
| **Frontend**                 | HTML5, CSS3, Bootstrap 5.3                           |
| **Icons**                    | Font Awesome 6.4                                     |
| **Fonts**                    | Google Fonts (Plus Jakarta Sans, DM Sans)            |
| **Backend**                  | PHP 8.x (procedural with MySQLi)                     |
| **Database**                 | MySQL 8.x                                            |
| **Local Server**             | XAMPP (Apache + MySQL)                               |
| **Session Auth**             | PHP `$_SESSION` with role-based guards               |
| **Password Security**        | PHP `password_hash()` / `password_verify()` (bcrypt) |
| **Form Validation**          | Client-side (JavaScript) + Server-side (PHP)         |
| **SQL Injection Prevention** | MySQLi Prepared Statements (`bind_param`)            |
| **Version Structure**        | Manual file-based MVC-style structure                |

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

| Column     | Type                           | Description               |
| ---------- | ------------------------------ | ------------------------- |
| id         | INT PK AUTO_INCREMENT          | User ID                   |
| full_name  | VARCHAR(100)                   | Full name or company name |
| email      | VARCHAR(150) UNIQUE            | Login email               |
| password   | VARCHAR(255)                   | Bcrypt hashed password    |
| role       | ENUM(student, employer, admin) | User role                 |
| is_banned  | TINYINT(1)                     | 0 = active, 1 = banned    |
| created_at | TIMESTAMP                      | Registration time         |

### `jobs`

| Column      | Type                        | Description          |
| ----------- | --------------------------- | -------------------- |
| id          | INT PK AUTO_INCREMENT       | Job ID               |
| employer_id | INT FK → users.id           | Posting employer     |
| title       | VARCHAR(200)                | Job title            |
| description | TEXT                        | Full job description |
| type        | ENUM(part-time, internship) | Job type             |
| location    | VARCHAR(100)                | City or Remote       |
| deadline    | DATE                        | Application deadline |
| is_deleted  | TINYINT(1)                  | Soft delete flag     |
| created_at  | TIMESTAMP                   | Post time            |

### `applications`

| Column     | Type                              | Description                     |
| ---------- | --------------------------------- | ------------------------------- |
| id         | INT PK AUTO_INCREMENT             | Application ID                  |
| job_id     | INT FK → jobs.id                  | Applied job                     |
| student_id | INT FK → users.id                 | Applicant                       |
| cover_note | TEXT                              | Student's cover note            |
| status     | ENUM(pending, accepted, rejected) | Current status                  |
| applied_at | TIMESTAMP                         | Application time                |
| UNIQUE     | (job_id, student_id)              | Prevents duplicate applications |

---

## ⚙️ Setup Instructions

### Prerequisites

- XAMPP (or WAMP) with Apache and MySQL running
- PHP 7.4 or higher

### Steps

**1. Import the database**

- Open `http://localhost/phpmyadmin`
- Create a new database named `std_job_portal`
- Click **Import** → select `database.sql` → click Go

**2. Configure database credentials**

Open `includes/db.php` and update:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'dbname');
```

**3. Place files in htdocs**

Copy the `student_job_portal/` folder into:

```
C:\xampp\htdocs\std_job_portal\
```

**4. Run the project**

Open your browser and go to:

```
http://localhost/std_job_portal/
```

---

## 🔒 Security Measures Implemented

- **Prepared Statements** on all database queries — prevents SQL Injection
- **`password_hash()` / `password_verify()`** — passwords never stored as plaintext
- **`htmlspecialchars()`** on all user output — prevents XSS (Cross-Site Scripting)
- **Role-based access control (RBAC)** — every page checks session role
- **Session-based authentication** — no sensitive data in URLs or cookies
- **Duplicate application constraint** — enforced at both PHP and database level

---
