<?php
// student/my_applications.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('student');

$user = currentUser();
$uid  = $user['id'];
$nav_active = 'applications';

$stmt = $conn->prepare("
    SELECT a.id, a.status, a.applied_at, a.cover_note,
           j.title, j.type, j.location, j.deadline,
           u.full_name AS employer_name
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN users u ON j.employer_id = u.id
    WHERE a.student_id = ?
    ORDER BY a.applied_at DESC
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
<title>My Applications – Job Portal</title>
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
          <a href="dashboard.php" class="nav-link"><i class="fas fa-house"></i> Dashboard</a>
          <a href="jobs.php" class="nav-link"><i class="fas fa-briefcase"></i> Browse Jobs</a>
          <a href="my_applications.php" class="nav-link active"><i class="fas fa-file-lines"></i> My Applications</a>
        </nav>
      </div>
      <a href="../logout.php" class="nav-link text-danger"><i class="fas fa-arrow-right-from-bracket"></i> Logout</a>
    </aside>

    <main class="col-lg-9 p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">My Applications</h1>
        <a href="jobs.php" class="btn btn-sm btn-primary">Browse More Jobs</a>
      </div>

      <?php if ($apps->num_rows === 0): ?>
        <div class="card text-center p-5">
          <i class="fas fa-folder-open" style="font-size:2.5rem; color:var(--border); margin-bottom:1rem;"></i>
          <p class="text-muted">You haven't applied to any jobs yet.</p>
          <a href="jobs.php" class="btn btn-primary mt-3">Find Jobs</a>
        </div>
      <?php else: ?>
        <?php while ($a = $apps->fetch_assoc()):
          $badge = match($a['status']) { 'accepted'=>'bg-success','rejected'=>'bg-danger',default=>'bg-secondary' };
          $icon  = match($a['status']) { 'accepted'=>'fa-circle-check','rejected'=>'fa-circle-xmark',default=>'fa-clock' };
        ?>
        <div class="card mb-3">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
              <div class="flex-grow-1">
                <h5 class="card-title"><?= htmlspecialchars($a['title']) ?></h5>
                <div class="d-flex flex-wrap gap-2 mb-2">
                  <span class="badge bg-secondary"><i class="fas fa-building"></i> <?= htmlspecialchars($a['employer_name']) ?></span>
                  <span class="badge <?= $a['type']==='internship'?'bg-primary':'bg-warning text-dark' ?>"><?= ucfirst($a['type']) ?></span>
                  <span class="badge bg-secondary"><i class="fas fa-location-dot"></i> <?= htmlspecialchars($a['location']) ?></span>
                </div>
                <p class="small text-muted mb-1">
                  <strong>Cover Note:</strong> <?= htmlspecialchars(substr($a['cover_note'],0,120)) ?>…
                </p>
                <p class="small text-muted mb-0">
                  Applied on <?= date('d M Y, h:i A', strtotime($a['applied_at'])) ?>
                </p>
              </div>
              <div class="flex-shrink-0 text-end">
                <span class="badge <?= $badge ?>" style="font-size:.85rem; padding:.4rem 1rem;">
                  <i class="fas <?= $icon ?>"></i> <?= ucfirst($a['status']) ?>
                </span>
              </div>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      <?php endif; ?>
    </main>
  </div>
</div>

</body>
</html>
