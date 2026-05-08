<?php
// register.php - User Registration (Student & Employer)
session_start();
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php"); exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';
    $role      = $_POST['role'] ?? '';

    // Server-side validation
    if (empty($full_name) || empty($email) || empty($password) || empty($role)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($role, ['student', 'employer'])) {
        $error = 'Invalid role selected.';
    } else {
        // Check duplicate email
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'This email is already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt->close();

            $ins = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
            $ins->bind_param("ssss", $full_name, $email, $hashed, $role);

            if ($ins->execute()) {
                header("Location: index.php?registered=1");
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $ins->close();
        }
        if ($stmt->errno == 0) $stmt->close();
    }
}

$selected_role = $_POST['role'] ?? 'student';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account – Student Job Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<style>
.auth-container {
    display: flex;
    align-items: center;

    justify-content: center;

    min-height: 100vh;
}
</style>

<body>

    <div class="auth-container  ">
        <div class="auth-card">

            <div class="auth-logo">Job<span>Portal</span></div>
            <p class="auth-subtitle">Create your account to get started</p>

            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <!-- Role Toggle -->
            <div class="role-tabs" role="tablist">
                <button type="button" class="role-tab <?= $selected_role === 'student'  ? 'active' : '' ?>"
                    onclick="setRole('student')" id="tab-student">
                    <i class="fas fa-user-graduate"></i> Student
                </button>
                <button type="button" class="role-tab <?= $selected_role === 'employer' ? 'active' : '' ?>"
                    onclick="setRole('employer')" id="tab-employer">
                    <i class="fas fa-building"></i> Employer
                </button>
            </div>

            <form method="POST" action="" id="registerForm" novalidate>
                <input type="hidden" name="role" id="roleInput" value="<?= htmlspecialchars($selected_role) ?>">

                <div class="mb-3">
                    <label class="form-label" for="full_name" id="nameLabel">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" placeholder="Your full name"
                        value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                    <div class="invalid-feedback">Full name is required.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="reg_email">Email Address</label>
                    <input type="email" id="reg_email" name="email" class="form-control" placeholder="you@example.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    <div class="invalid-feedback">Please enter a valid email.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="reg_password">Password</label>
                    <input type="password" id="reg_password" name="password" class="form-control"
                        placeholder="Min. 6 characters" required>
                    <div class="invalid-feedback">Password must be at least 6 characters.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                        placeholder="Repeat your password" required>
                    <div class="invalid-feedback">Passwords do not match.</div>
                </div>

                <button type="submit" name="register" class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <p class="text-center text-muted small">
                Already have an account? <a href="index.php" class="fw-bold">Sign in</a>
            </p>

        </div>
    </div>

    <script>
    function setRole(role) {
        document.getElementById('roleInput').value = role;
        document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
        document.getElementById('tab-' + role).classList.add('active');
        document.getElementById('nameLabel').textContent =
            role === 'employer' ? 'Company / Organization Name' : 'Full Name';
        document.getElementById('full_name').placeholder =
            role === 'employer' ? 'e.g. ABC Technologies' : 'Your full name';
    }

    // Client-side validation
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        let valid = true;

        const name = document.getElementById('full_name');
        const email = document.getElementById('reg_email');
        const pass = document.getElementById('reg_password');
        const conf = document.getElementById('confirm_password');

        if (!name.value.trim()) {
            name.classList.add('is-invalid');
            valid = false;
        } else {
            name.classList.remove('is-invalid');
        }

        if (!email.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            email.classList.add('is-invalid');
            valid = false;
        } else {
            email.classList.remove('is-invalid');
        }

        if (pass.value.length < 6) {
            pass.classList.add('is-invalid');
            valid = false;
        } else {
            pass.classList.remove('is-invalid');
        }

        if (conf.value !== pass.value) {
            conf.classList.add('is-invalid');
            valid = false;
        } else {
            conf.classList.remove('is-invalid');
        }

        if (!valid) e.preventDefault();
    });
    </script>
</body>

</html>