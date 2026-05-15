

CREATE DATABASE IF NOT EXISTS student_job_portal;
USE student_job_portal;

-- Users Table (Students & Employers)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'employer', 'admin') NOT NULL,
    is_banned TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Jobs Table
CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    type ENUM('part-time', 'internship') NOT NULL,
    location VARCHAR(100) NOT NULL,
    deadline DATE NOT NULL,
    is_deleted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Applications Table
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    student_id INT NOT NULL,
    cover_note TEXT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_application (job_id, student_id)
);

-- Default Admin Account (password: admin123)
INSERT INTO users (full_name, email, password, role)
VALUES ('Admin', 'admin@portal.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Note: Default password is 'password' via Laravel hash above.
-- For production use: run this PHP to generate proper hash:
-- echo password_hash('admin123', PASSWORD_DEFAULT);
-- Then replace the hash above.



-- ============================================================
--  Student Job Portal — Seed / Sample Data
--  Compatible with the schema provided in the CEP assignment
--  All passwords are hashed with PHP password_hash() DEFAULT
--  Plain-text passwords are shown in comments for testing.
-- ============================================================

USE student_job_portal;

-- ============================================================
-- 1. USERS  (1 admin already inserted by schema)
--    Passwords:
--      Students  → student123
--      Employers → employer123
--      Admin     → admin123  (already in schema, hash kept as-is)
-- ============================================================

INSERT INTO users (full_name, email, password, role, is_banned) VALUES

-- ── Students ──────────────────────────────────────────────
('Ali Hassan',       'ali.hassan@student.com',
 '$2y$10$KIBjMsz5Oe3R6HFZl3Rn8.mHuQ3EVqV7nU0i4EQMXmYhCBvKq3e6K',   -- student123
 'student', 0),

('Sara Khan',        'sara.khan@student.com',
 '$2y$10$KIBjMsz5Oe3R6HFZl3Rn8.mHuQ3EVqV7nU0i4EQMXmYhCBvKq3e6K',
 'student', 0),

('Usman Raza',       'usman.raza@student.com',
 '$2y$10$KIBjMsz5Oe3R6HFZl3Rn8.mHuQ3EVqV7nU0i4EQMXmYhCBvKq3e6K',
 'student', 0),

('Hina Malik',       'hina.malik@student.com',
 '$2y$10$KIBjMsz5Oe3R6HFZl3Rn8.mHuQ3EVqV7nU0i4EQMXmYhCBvKq3e6K',
 'student', 0),

('Bilal Ahmed',      'bilal.ahmed@student.com',
 '$2y$10$KIBjMsz5Oe3R6HFZl3Rn8.mHuQ3EVqV7nU0i4EQMXmYhCBvKq3e6K',
 'student', 0),

-- Banned student (to demo admin ban feature)
('Spammer Student',  'spam.student@fake.com',
 '$2y$10$KIBjMsz5Oe3R6HFZl3Rn8.mHuQ3EVqV7nU0i4EQMXmYhCBvKq3e6K',
 'student', 1),

-- ── Employers ─────────────────────────────────────────────
('TechSoft Pvt Ltd',      'hr@techsoft.com',
 '$2y$10$w3ZaGHs5tqY9BmkJ1L7XR.3Lv2P4kpDqNjRoaW5U0cHYFbMeGp9i.',   -- employer123
 'employer', 0),

('DigitalHub Agency',     'jobs@digitalhub.pk',
 '$2y$10$w3ZaGHs5tqY9BmkJ1L7XR.3Lv2P4kpDqNjRoaW5U0cHYFbMeGp9i.',
 'employer', 0),

('Rozee Solutions',       'careers@rozeesol.com',
 '$2y$10$w3ZaGHs5tqY9BmkJ1L7XR.3Lv2P4kpDqNjRoaW5U0cHYFbMeGp9i.',
 'employer', 0),

-- Banned employer (to demo admin ban / spam removal)
('Fake Jobs Co',          'fake@spamjobs.com',
 '$2y$10$w3ZaGHs5tqY9BmkJ1L7XR.3Lv2P4kpDqNjRoaW5U0cHYFbMeGp9i.',
 'employer', 1);

-- ── Quick reference of IDs (assuming admin was id=1) ──────
--  id  2  → Ali Hassan        (student)
--  id  3  → Sara Khan         (student)
--  id  4  → Usman Raza        (student)
--  id  5  → Hina Malik        (student)
--  id  6  → Bilal Ahmed       (student)
--  id  7  → Spammer Student   (student, BANNED)
--  id  8  → TechSoft          (employer)
--  id  9  → DigitalHub        (employer)
--  id 10  → Rozee Solutions   (employer)
--  id 11  → Fake Jobs Co      (employer, BANNED)


-- ============================================================
-- 2. JOBS
--    Mix of: active, expired, and soft-deleted (spam) listings
--    employer_id references the IDs above
-- ============================================================

INSERT INTO jobs (employer_id, title, description, type, location, deadline, is_deleted) VALUES

-- TechSoft (id=8) ────────────────────────────────────────
(8, 'Junior PHP Developer',
 'We are looking for a motivated junior PHP developer to join our backend team. '
 'You will work on REST APIs, MySQL databases, and Laravel-based projects. '
 'Fresh graduates are welcome to apply.',
 'internship', 'Karachi', '2026-07-31', 0),

(8, 'Frontend Intern – React',
 'Join our product team as a frontend intern. You will build UI components using '
 'React and Tailwind CSS, collaborate with designers, and contribute to live products.',
 'internship', 'Karachi', '2026-06-30', 0),

(8, 'Part-Time Data Entry Operator',
 'Responsible for entering and maintaining records in our internal CRM. '
 'Requires good typing speed and attention to detail. Flexible hours (4 hrs/day).',
 'part-time', 'Remote', '2026-05-25', 0),

-- DigitalHub (id=9) ──────────────────────────────────────
(9, 'Social Media Marketing Intern',
 'Create and schedule posts across Instagram, LinkedIn, and Facebook for our clients. '
 'Knowledge of Canva and basic analytics is a plus.',
 'internship', 'Hyderabad', '2026-08-15', 0),

(9, 'Graphic Design – Part Time',
 'Design banners, brochures, and digital ads. Must know Adobe Illustrator or Figma. '
 'Work 3 days a week from our Hyderabad office.',
 'part-time', 'Hyderabad', '2026-06-01', 0),

-- Rozee Solutions (id=10) ────────────────────────────────
(10, 'Python / ML Intern',
 'Work alongside our data science team on machine learning pipelines. '
 'You will help clean datasets, train models, and visualise results using '
 'Matplotlib and Seaborn. Knowledge of scikit-learn preferred.',
 'internship', 'Lahore', '2026-09-01', 0),

(10, 'Content Writer – Part Time',
 'Write SEO-friendly blog posts, product descriptions, and social copy. '
 '2–3 articles per week. Strong English writing skills required.',
 'part-time', 'Remote', '2026-07-15', 0),

-- EXPIRED listing (deadline in the past) – should be hidden from students
(9, 'WordPress Developer Intern',
 'Build and customise WordPress themes for client websites. '
 'This position has already closed.',
 'internship', 'Hyderabad', '2025-12-31', 0),

-- SPAM / DELETED listing posted by the banned employer
(11, 'Earn 50,000/month from home – No skills needed!',
 'Just click this link and register to start earning. Guaranteed income!!!',
 'part-time', 'Anywhere', '2026-12-31', 1);   -- is_deleted = 1 (admin removed it)

INSERT INTO applications (job_id, student_id, cover_note, status) VALUES

-- Ali Hassan 
(1, 2,
 'I am a final-year Software Engineering student with hands-on PHP and MySQL experience '
 'from university projects. I am eager to learn and contribute to your backend team.',
 'accepted'),

(4, 2,
 'I manage my university department''s Instagram page and understand social media strategy. '
 'Would love to bring those skills to DigitalHub.',
 'pending'),

-- Sara Khan 
(2, 3,
 'I have built several React projects including a task management app and a weather '
 'dashboard. I am comfortable with hooks, component design, and Tailwind CSS.',
 'accepted'),

(6, 3,
 'I completed an online course on Machine Learning with Python and have worked on '
 'two mini projects using scikit-learn. Very excited about this opportunity.',
 'rejected'),

(7, 3,
 'I write tech articles on Medium and have experience with SEO tools like Ubersuggest. '
 'I can deliver consistent, quality content every week.',
 'pending'),

-- Usman Raza 
(1, 4,
 'As a PHP developer who has built a complete e-commerce site as a semester project, '
 'I believe I can add immediate value to your team.',
 'rejected'),

(3, 4,
 'I type 65 WPM and am experienced with MS Excel and Google Sheets. '
 'I am available for a 4-hour daily shift.',
 'pending'),

(6, 4,
 'I am familiar with pandas, numpy, and basic regression models. '
 'Looking forward to applying these skills in a professional setting.',
 'accepted'),

-- Hina Malik 
(5, 5,
 'I use Figma daily for my UI/UX coursework and have created brand kits for two '
 'student startups. I am available three days a week.',
 'accepted'),

(4, 5,
 'I run a personal blog with 2 000+ followers and know how to grow engagement '
 'organically. I would love to do the same for your clients.',
 'pending'),

-- Bilal Ahmed 
(7, 6,
 'I have written for my university newsletter for two years. I understand how to '
 'balance technical accuracy with readability.',
 'pending'),

(2, 6,
 'I am proficient in React, Redux, and have deployed two projects on Vercel. '
 'I would be a strong addition to your frontend team.',
 'rejected');