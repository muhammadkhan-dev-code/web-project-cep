<?php
// includes/navbar.php
// Usage: include after session_start() & requireLogin()
// $nav_active = 'dashboard' | 'jobs' | 'applications' | 'users' etc.
$user = currentUser();
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <span style="color: var(--primary);">Job</span><span style="color: var(--accent);">Portal</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav ms-auto">
                <?php if ($user['role'] === 'student'): ?>
                <a href="dashboard.php" class="nav-link <?= ($nav_active??'')==='dashboard' ? 'active':'' ?>">
                    <i class="fas fa-house"></i> Dashboard
                </a>
                <a href="jobs.php" class="nav-link <?= ($nav_active??'')==='jobs' ? 'active':'' ?>">
                    <i class="fas fa-briefcase"></i> Browse Jobs
                </a>
                <a href="my_applications.php" class="nav-link <?= ($nav_active??'')==='applications' ? 'active':'' ?>">
                    <i class="fas fa-file-lines"></i> My Apps
                </a>

                <?php elseif ($user['role'] === 'employer'): ?>
                <a href="dashboard.php" class="nav-link <?= ($nav_active??'')==='dashboard' ? 'active':'' ?>">
                    <i class="fas fa-house"></i> Dashboard
                </a>
                <a href="post_job.php" class="nav-link <?= ($nav_active??'')==='post' ? 'active':'' ?>">
                    <i class="fas fa-plus"></i> Post Job
                </a>
                <a href="my_jobs.php" class="nav-link <?= ($nav_active??'')==='jobs' ? 'active':'' ?>">
                    <i class="fas fa-list"></i> My Jobs
                </a>

                <?php elseif ($user['role'] === 'admin'): ?>
                <a href="dashboard.php" class="nav-link <?= ($nav_active??'')==='dashboard' ? 'active':'' ?>">
                    <i class="fas fa-gauge"></i> Overview
                </a>
                <a href="users.php" class="nav-link <?= ($nav_active??'')==='users' ? 'active':'' ?>">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="jobs.php" class="nav-link <?= ($nav_active??'')==='jobs' ? 'active':'' ?>">
                    <i class="fas fa-briefcase"></i> Jobs
                </a>
                <?php endif; ?>

                <div class="nav-link ps-3">
                    <span class="text-muted small fw-bold">
                        <i class="fas fa-circle-user" style="color: var(--primary);"></i>
                        <?= htmlspecialchars($user['name']) ?>
                    </span>
                </div>
                <a href="../logout.php" class="btn btn-outline-primary btn-sm ms-2">
                    <i class="fas fa-arrow-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>