<?php
session_start();
require_once 'includes/db.php';


if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'student')  header("Location: student/dashboard.php");
    if ($_SESSION['role'] === 'employer') header("Location: employer/dashboard.php");
    if ($_SESSION['role'] === 'admin')    header("Location: admin/dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, password, role, is_banned FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_banned']) {
                $error = 'Your account has been banned. Contact admin.';
            } else {
                // sesssion_start()
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['role']      = $user['role'];

                if ($user['role'] === 'student')  header("Location: student/dashboard.php");
                if ($user['role'] === 'employer') header("Location: employer/dashboard.php");
                if ($user['role'] === 'admin')    header("Location: admin/dashboard.php");
                exit();
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Job Portal – Login</title>
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

    <div class="auth-container">
        <div class="auth-card">

            <div class="auth-logo">Job<span>Portal</span></div>
            <p class="auth-subtitle">Connect students with opportunities</p>

            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-circle-check"></i> Account created! You can now log in.
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-lock"></i> Access denied. Please log in.
            </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm" novalidate>
                <div class="mb-3">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    <div class="invalid-feedback">Please enter a valid email.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="Enter your password" required>
                    <div class="invalid-feedback">Password is required.</div>
                </div>

                <button type="submit" name="login" class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-arrow-right-to-bracket"></i> Sign In
                </button>
            </form>

            <p class="text-center text-muted small">
                Don't have an account?
                <a href="register.php" class="fw-bold">Create one</a>
            </p>
        </div>
    </div>

    <script>
    // Client-side validation
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        let valid = true;
        const email = document.getElementById('email');
        const pass = document.getElementById('password');

        if (!email.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            email.classList.add('is-invalid');
            valid = false;
        } else {
            email.classList.remove('is-invalid');
        }

        if (!pass.value) {
            pass.classList.add('is-invalid');
            valid = false;
        } else {
            pass.classList.remove('is-invalid');
        }

        if (!valid) e.preventDefault();
    });
    </script>
</body>

</html>