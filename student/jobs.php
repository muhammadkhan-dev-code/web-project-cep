<?php
// student/jobs.php - Browse & Search Jobs
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('student');

$user = currentUser();
$uid  = $user['id'];
$nav_active = 'jobs';

$search   = trim($_GET['search'] ?? '');
$type_f   = $_GET['type'] ?? '';
$success  = $_GET['applied'] ?? '';

// Build query
$where = "j.is_deleted=0 AND j.deadline >= CURDATE()";
$params = [];
$types  = '';

if ($search) {
    $where .= " AND (j.title LIKE ? OR j.location LIKE ? OR j.description LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s, $s, $s]);
    $types .= 'sss';
}
if ($type_f && in_array($type_f, ['part-time','internship'])) {
    $where .= " AND j.type = ?";
    $params[] = $type_f;
    $types .= 's';
}

$sql = "SELECT j.*, u.full_name AS employer_name,
        (SELECT COUNT(*) FROM applications a WHERE a.job_id=j.id AND a.student_id=$uid) AS already_applied
        FROM jobs j
        JOIN users u ON j.employer_id = u.id
        WHERE $where ORDER BY j.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$jobs = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Browse Jobs – Job Portal</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="d-flex" style="min-height: calc(100vh - 64px);">
  <aside class="sidebar d-none d-lg-block">
    <nav class="sidebar-nav">
      <h6 class="text-muted fw-bold text-uppercase small">Menu</h6>
      <a href="dashboard.php" class="nav-link"><i class="fas fa-house"></i> Dashboard</a>
      <a href="jobs.php" class="nav-link active"><i class="fas fa-briefcase"></i> Browse Jobs</a>
      <a href="my_applications.php" class="nav-link"><i class="fas fa-file-lines"></i> My Applications</a>
      <a href="../logout.php" class="nav-link text-danger mt-3"><i class="fas fa-arrow-right-from-bracket"></i> Logout</a>
    </nav>
  </aside>

  <main class="flex-grow-1 p-4">
    <h1 class="h3 mb-4">Browse Jobs</h1>

    <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-circle-check"></i> Application submitted successfully!
      </div>
    <?php endif; ?>

    <!-- Search & Filter -->
    <form method="GET" class="d-flex gap-3 mb-4 flex-wrap">
      <div class="search-input-wrapper flex-grow-1" style="min-width: 250px;">
        <i class="fas fa-magnifying-glass"></i>
        <input type="text" name="search" class="form-control"
               placeholder="Search by title, location..."
               value="<?= htmlspecialchars($search) ?>">
      </div>
      <select name="type" class="form-select" style="width:auto; min-width:150px;">
        <option value="">All Types</option>
        <option value="part-time"  <?= $type_f==='part-time'  ? 'selected':'' ?>>Part-time</option>
        <option value="internship" <?= $type_f==='internship' ? 'selected':'' ?>>Internship</option>
      </select>
      <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
      <?php if ($search || $type_f): ?>
        <a href="jobs.php" class="btn btn-outline-primary">Clear</a>
      <?php endif; ?>
    </form>

    <!-- Job Cards -->
    <?php if ($jobs->num_rows === 0): ?>
      <div class="card text-center p-5">
        <i class="fas fa-briefcase fs-1 text-muted mb-3"></i>
        <p class="text-muted">No jobs found. Try a different search.</p>
      </div>
    <?php else: ?>
      <?php while ($job = $jobs->fetch_assoc()): ?>
      <div class="job-card card p-4 mb-3">
        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
          <div class="flex-grow-1">
            <h5 class="card-title"><?= htmlspecialchars($job['title']) ?></h5>
            <div class="mb-2">
              <span class="badge badge-secondary"><i class="fas fa-building"></i> <?= htmlspecialchars($job['employer_name']) ?></span>
              <span class="badge <?= $job['type']==='internship' ? 'badge-primary':'badge-warning' ?>">
                <?= ucfirst($job['type']) ?>
              </span>
              <span class="badge badge-secondary"><i class="fas fa-location-dot"></i> <?= htmlspecialchars($job['location']) ?></span>
              <span class="badge badge-secondary"><i class="fas fa-calendar"></i> <?= date('d M Y', strtotime($job['deadline'])) ?></span>
            </div>
            <p class="card-text small text-muted text-truncate-2">
              <?= htmlspecialchars($job['description']) ?>
            </p>
          </div>
          <div class="flex-shrink-0">
            <?php if ($job['already_applied']): ?>
              <span class="badge badge-success">
                <i class="fas fa-circle-check"></i> Applied
              </span>
            <?php else: ?>
              <a href="apply.php?job_id=<?= $job['id'] ?>" class="btn btn-sm btn-primary">
                Apply Now <i class="fas fa-paper-plane"></i>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    <?php endif; ?>

  </main>
</div>

</body>
</html>
