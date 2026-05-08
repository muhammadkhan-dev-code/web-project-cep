<?php
// admin/dashboard.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');

$user = currentUser();
$nav_active = 'dashboard';

// Stats
$total_students  = $conn->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetch_row()[0];
$total_employers = $conn->query("SELECT COUNT(*) FROM users WHERE role='employer'")->fetch_row()[0];
$total_jobs      = $conn->query("SELECT COUNT(*) FROM jobs WHERE is_deleted=0")->fetch_row()[0];
$total_apps      = $conn->query("SELECT COUNT(*) FROM applications")->fetch_row()[0];
$banned_users    = $conn->query("SELECT COUNT(*) FROM users WHERE is_banned=1")->fetch_row()[0];

// Recent users
$recent_users = $conn->query("SELECT full_name, email, role, is_banned, created_at FROM users ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard – Job Portal</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container-fluid">
  <div class="row">
    <aside class="col-lg-3 bg-light p-3 border-end" style="min-height:100vh;">
      <div class="mb-4">
        <h6 class="text-uppercase text-muted small fw-bold">Admin Panel</h6>
        <nav class="nav flex-column">
          <a href="dashboard.php" class="nav-link active"><i class="fas fa-gauge"></i> Overview</a>
          <a href="users.php" class="nav-link"><i class="fas fa-users"></i> Manage Users</a>
          <a href="jobs.php" class="nav-link"><i class="fas fa-briefcase"></i> Manage Jobs</a>
        </nav>
      </div>
      <a href="../logout.php" class="nav-link text-danger">
        <i class="fas fa-arrow-right-from-bracket"></i> Logout
      </a>
    </aside>

    <main class="col-lg-9 p-4">
    <div style="margin-bottom:1.5rem;">
      <h1 style="font-size:1.5rem; margin-bottom:.25rem;">Admin Overview</h1>
      <p class="text-muted" style="font-size:.9rem;">Monitor platform activity and manage users/jobs.</p>
    </div>

      <!-- Stats -->
      <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-4">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="text-primary me-3" style="font-size:2rem;"><i class="fas fa-user-graduate"></i></div>
                <div>
                  <div class="display-6"><?= $total_students ?></div>
                  <div class="text-muted small">Students</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="text-warning me-3" style="font-size:2rem;"><i class="fas fa-building"></i></div>
                <div>
                  <div class="display-6"><?= $total_employers ?></div>
                  <div class="text-muted small">Employers</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="text-success me-3" style="font-size:2rem;"><i class="fas fa-briefcase"></i></div>
                <div>
                  <div class="display-6"><?= $total_jobs ?></div>
                  <div class="text-muted small">Active Jobs</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="text-info me-3" style="font-size:2rem;"><i class="fas fa-file-lines"></i></div>
                <div>
                  <div class="display-6"><?= $total_apps ?></div>
                  <div class="text-muted small">Applications</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="text-danger me-3" style="font-size:2rem;"><i class="fas fa-ban"></i></div>
                <div>
                  <div class="display-6"><?= $banned_users ?></div>
                  <div class="text-muted small">Banned Users</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="d-flex gap-2 mb-4 flex-wrap">
        <a href="users.php" class="btn btn-primary"><i class="fas fa-users"></i> Manage Users</a>
        <a href="jobs.php" class="btn btn-outline-primary"><i class="fas fa-briefcase"></i> Manage Jobs</a>
      </div>

      <!-- Recent Users -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Recent Registrations</h5>
        <a href="users.php" class="text-decoration-none">View all →</a>
      </div>

      <div class="card">
        <table class="table table-striped mb-0">
          <thead>
            <tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Status</th></tr>
          </thead>
          <tbody>
            <?php while ($u = $recent_users->fetch_assoc()): ?>
            <tr>
              <td class="fw-bold"><?= htmlspecialchars($u['full_name']) ?></td>
              <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
              <td>
                <span class="badge <?= $u['role']==='student'?'bg-primary':($u['role']==='employer'?'bg-warning text-dark':'bg-secondary') ?>">
                  <?= ucfirst($u['role']) ?>
                </span>
              </td>
              <td class="text-muted"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
              <td>
                <?php if ($u['is_banned']): ?>
                  <span class="badge bg-danger">Banned</span>
                <?php else: ?>
                  <span class="badge bg-success">Active</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</div>

</body>
</html>
