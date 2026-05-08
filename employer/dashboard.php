<?php
// employer/dashboard.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('employer');

$user = currentUser();
$uid  = $user['id'];
$nav_active = 'dashboard';

// Stats
$total_jobs   = $conn->query("SELECT COUNT(*) FROM jobs WHERE employer_id=$uid AND is_deleted=0")->fetch_row()[0];
$active_jobs  = $conn->query("SELECT COUNT(*) FROM jobs WHERE employer_id=$uid AND is_deleted=0 AND deadline>=CURDATE()")->fetch_row()[0];
$total_apps   = $conn->query("SELECT COUNT(*) FROM applications a JOIN jobs j ON a.job_id=j.id WHERE j.employer_id=$uid")->fetch_row()[0];
$pending_apps = $conn->query("SELECT COUNT(*) FROM applications a JOIN jobs j ON a.job_id=j.id WHERE j.employer_id=$uid AND a.status='pending'")->fetch_row()[0];

// Recent applications
$stmt = $conn->prepare("
    SELECT a.id, a.status, a.applied_at, j.title, u.full_name AS student_name
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN users u ON a.student_id = u.id
    WHERE j.employer_id = ?
    ORDER BY a.applied_at DESC LIMIT 6
");
$stmt->bind_param("i", $uid);
$stmt->execute();
$apps = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employer Dashboard – Job Portal</title>
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
        <h6 class="text-uppercase text-muted small fw-bold">Menu</h6>
        <nav class="nav flex-column">
          <a href="dashboard.php" class="nav-link active"><i class="fas fa-house"></i> Dashboard</a>
          <a href="post_job.php" class="nav-link"><i class="fas fa-plus-circle"></i> Post a Job</a>
          <a href="my_jobs.php" class="nav-link"><i class="fas fa-list-check"></i> My Jobs</a>
        </nav>
      </div>
      <a href="../logout.php" class="nav-link text-danger">
        <i class="fas fa-arrow-right-from-bracket"></i> Logout
      </a>
    </aside>

    <main class="col-lg-9 p-4">
    <div style="margin-bottom:1.5rem;">
      <h1 style="font-size:1.5rem; margin-bottom:.25rem;">
        Welcome, <?= htmlspecialchars($user['name']) ?> 👋
      </h1>
      <p class="text-muted" style="font-size:.9rem;">Manage your job listings and review applicants.</p>
    </div>

      <!-- Stats -->
      <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="text-primary me-3" style="font-size:2rem;"><i class="fas fa-briefcase"></i></div>
                <div>
                  <div class="display-6"><?= $total_jobs ?></div>
                  <div class="text-muted small">Total Jobs</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="text-success me-3" style="font-size:2rem;"><i class="fas fa-circle-check"></i></div>
                <div>
                  <div class="display-6"><?= $active_jobs ?></div>
                  <div class="text-muted small">Active Jobs</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="text-warning me-3" style="font-size:2rem;"><i class="fas fa-users"></i></div>
                <div>
                  <div class="display-6"><?= $total_apps ?></div>
                  <div class="text-muted small">Total Applicants</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="card">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="text-danger me-3" style="font-size:2rem;"><i class="fas fa-clock"></i></div>
                <div>
                  <div class="display-6"><?= $pending_apps ?></div>
                  <div class="text-muted small">Pending Review</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- CTA -->
      <div class="card border-0 mb-4" style="background:linear-gradient(135deg,var(--accent) 0%,#FB923C 100%); color:#fff;">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div>
            <div class="h5 mb-1">Ready to hire?</div>
            <div class="small" style="opacity:.85;">Post a new job or internship listing now</div>
          </div>
          <a href="post_job.php" class="btn btn-light">Post a Job <i class="fas fa-plus"></i></a>
        </div>
      </div>

      <!-- Recent Applications -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Recent Applications</h5>
        <a href="my_jobs.php" class="text-decoration-none">View all jobs →</a>
      </div>

      <?php if ($apps->num_rows === 0): ?>
        <div class="card text-center p-5">
          <i class="fas fa-inbox" style="font-size:2.5rem; color:var(--border); margin-bottom:1rem;"></i>
          <p class="text-muted">No applications yet. Post a job to get started.</p>
          <a href="post_job.php" class="btn btn-primary mt-3">Post a Job</a>
        </div>
      <?php else: ?>
        <div class="card">
          <table class="table table-striped mb-0">
            <thead>
              <tr><th>Applicant</th><th>Job Title</th><th>Applied</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php while ($a = $apps->fetch_assoc()): ?>
              <tr>
                <td class="fw-bold"><?= htmlspecialchars($a['student_name']) ?></td>
                <td class="text-muted"><?= htmlspecialchars($a['title']) ?></td>
                <td class="text-muted"><?= date('d M Y', strtotime($a['applied_at'])) ?></td>
                <td>
                  <?php $badge = match($a['status']) { 'accepted'=>'bg-success','rejected'=>'bg-danger',default=>'bg-secondary' }; ?>
                  <span class="badge <?= $badge ?>"><?= ucfirst($a['status']) ?></span>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </main>
  </div>
</div>

</body>
</html>
