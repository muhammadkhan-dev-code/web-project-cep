<?php
// student/dashboard.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('student');

$user = currentUser();
$uid  = $user['id'];
$nav_active = 'dashboard';

// Stats
$total_apps = $conn->query("SELECT COUNT(*) FROM applications WHERE student_id=$uid")->fetch_row()[0];
$pending    = $conn->query("SELECT COUNT(*) FROM applications WHERE student_id=$uid AND status='pending'")->fetch_row()[0];
$accepted   = $conn->query("SELECT COUNT(*) FROM applications WHERE student_id=$uid AND status='accepted'")->fetch_row()[0];
$rejected   = $conn->query("SELECT COUNT(*) FROM applications WHERE student_id=$uid AND status='rejected'")->fetch_row()[0];

// Available jobs count
$open_jobs  = $conn->query("SELECT COUNT(*) FROM jobs WHERE is_deleted=0 AND deadline >= CURDATE()")->fetch_row()[0];

// Recent applications
$recent_stmt = $conn->prepare("
    SELECT a.status, a.applied_at, j.title, j.type, j.location, u.full_name AS employer_name
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN users u ON j.employer_id = u.id
    WHERE a.student_id = ?
    ORDER BY a.applied_at DESC LIMIT 5
");
$recent_stmt->bind_param("i", $uid);
$recent_stmt->execute();
$recent_apps = $recent_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard – Job Portal</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="d-flex" style="min-height: calc(100vh - 64px);">
  <!-- Sidebar -->
  <aside class="sidebar d-none d-lg-block">
    <nav class="sidebar-nav">
      <div class="mb-4">
        <h6 class="text-muted fw-bold text-uppercase small">Menu</h6>
        <a href="dashboard.php" class="nav-link active"><i class="fas fa-house"></i> Dashboard</a>
        <a href="jobs.php" class="nav-link"><i class="fas fa-briefcase"></i> Browse Jobs</a>
        <a href="my_applications.php" class="nav-link"><i class="fas fa-file-lines"></i> My Applications</a>
      </div>
      <div class="mt-auto">
        <a href="../logout.php" class="nav-link text-danger"><i class="fas fa-arrow-right-from-bracket"></i> Logout</a>
      </div>
    </nav>
  </aside>

  <!-- Main -->
  <main class="flex-grow-1 p-4">
    <div class="mb-4">
      <h1 class="h3 mb-1">Welcome back, <?= htmlspecialchars($user['name']) ?> 👋</h1>
      <p class="text-muted small">Here's an overview of your job search activity.</p>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
      <div class="col-md-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon primary"><i class="fas fa-briefcase"></i></div>
          <div>
            <div class="stat-value"><?= $total_apps ?></div>
            <div class="stat-label">Total Applied</div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon accent"><i class="fas fa-clock"></i></div>
          <div>
            <div class="stat-value"><?= $pending ?></div>
            <div class="stat-label">Pending</div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon success"><i class="fas fa-circle-check"></i></div>
          <div>
            <div class="stat-value"><?= $accepted ?></div>
            <div class="stat-label">Accepted</div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon danger"><i class="fas fa-circle-xmark"></i></div>
          <div>
            <div class="stat-value"><?= $rejected ?></div>
            <div class="stat-label">Rejected</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Open jobs CTA -->
    <div class="bg-primary text-white rounded-3 p-4 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div>
        <div class="h4 fw-bold mb-1"><?= $open_jobs ?> Open Jobs</div>
        <div class="small opacity-75">Available internships & part-time roles</div>
      </div>
      <a href="jobs.php" class="btn btn-warning">Browse All Jobs <i class="fas fa-arrow-right"></i></a>
    </div>

    <!-- Recent Applications -->
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h5 class="mb-0">Recent Applications</h5>
      <a href="my_applications.php" class="small fw-bold">View all →</a>
    </div>

    <?php if ($recent_apps->num_rows === 0): ?>
      <div class="card text-center p-5">
        <i class="fas fa-folder-open fs-1 text-muted mb-3"></i>
        <p class="text-muted">You haven't applied to any jobs yet.</p>
        <a href="jobs.php" class="btn btn-primary mt-2">Find Jobs</a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead class="table-light">
            <tr>
              <th>Job Title</th>
              <th>Employer</th>
              <th>Type</th>
              <th>Applied</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($app = $recent_apps->fetch_assoc()): ?>
            <tr>
              <td class="fw-bold"><?= htmlspecialchars($app['title']) ?></td>
              <td class="text-muted"><?= htmlspecialchars($app['employer_name']) ?></td>
              <td>
                <span class="badge <?= $app['type']==='internship' ? 'badge-primary' : 'badge-warning' ?>">
                  <?= ucfirst($app['type']) ?>
                </span>
              </td>
              <td class="text-muted"><?= date('d M Y', strtotime($app['applied_at'])) ?></td>
              <td>
                <?php
                $badge = match($app['status']) {
                    'accepted' => 'badge-success',
                    'rejected' => 'badge-danger',
                    default    => 'badge-secondary',
                };
                ?>
                <span class="badge <?= $badge ?>"><?= ucfirst($app['status']) ?></span>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  </main>
</div>

</body>
</html>
