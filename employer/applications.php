<?php
// employer/applications.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('employer');

$user   = currentUser();
$uid    = $user['id'];
$nav_active = 'jobs';
$job_id = intval($_GET['job_id'] ?? 0);

// Verify this job belongs to the employer
$job_stmt = $conn->prepare("SELECT * FROM jobs WHERE id=? AND employer_id=? AND is_deleted=0");
$job_stmt->bind_param("ii", $job_id, $uid);
$job_stmt->execute();
$job = $job_stmt->get_result()->fetch_assoc();
if (!$job) { header("Location: my_jobs.php"); exit(); }

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_id'], $_POST['status'])) {
    $app_id = intval($_POST['app_id']);
    $status = $_POST['status'];
    if (in_array($status, ['pending','accepted','rejected'])) {
        $upd = $conn->prepare("UPDATE applications SET status=? WHERE id=? AND job_id=?");
        $upd->bind_param("sii", $status, $app_id, $job_id);
        $upd->execute();
    }
    header("Location: applications.php?job_id=$job_id&updated=1"); exit();
}

$apps_stmt = $conn->prepare("
    SELECT a.id, a.status, a.applied_at, a.cover_note, u.full_name, u.email
    FROM applications a
    JOIN users u ON a.student_id = u.id
    WHERE a.job_id = ?
    ORDER BY a.applied_at DESC
");
$apps_stmt->bind_param("i", $job_id);
$apps_stmt->execute();
$apps = $apps_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Applications – <?= htmlspecialchars($job['title']) ?></title>
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
          <a href="post_job.php" class="nav-link"><i class="fas fa-plus-circle"></i> Post a Job</a>
          <a href="my_jobs.php" class="nav-link active"><i class="fas fa-list-check"></i> My Jobs</a>
        </nav>
      </div>
      <a href="../logout.php" class="nav-link text-danger"><i class="fas fa-arrow-right-from-bracket"></i> Logout</a>
    </aside>

    <main class="col-lg-9 p-4">
      <div class="mb-3">
        <a href="my_jobs.php" class="text-decoration-none">← Back to Jobs</a>
      </div>

      <div class="mb-4">
        <h1 class="h3 mb-2"><?= htmlspecialchars($job['title']) ?></h1>
        <span class="badge bg-primary"><?= $apps->num_rows ?> Applicant<?= $apps->num_rows!=1?'s':'' ?></span>
      </div>

      <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success"><i class="fas fa-circle-check"></i> Status updated.</div>
      <?php endif; ?>

      <?php if ($apps->num_rows === 0): ?>
        <div class="card text-center p-5">
          <i class="fas fa-inbox" style="font-size:2.5rem; color:var(--border); margin-bottom:1rem;"></i>
          <p class="text-muted">No applications received yet.</p>
        </div>
      <?php else: ?>
        <?php while ($a = $apps->fetch_assoc()):
          $badge = match($a['status']) { 'accepted'=>'bg-success','rejected'=>'bg-danger',default=>'bg-secondary' };
        ?>
        <div class="card mb-3">
          <div class="card-body">
            <div class="d-flex justify-content-between flex-wrap gap-3">
              <div class="flex-grow-1">
                <h6 class="fw-bold mb-2"><?= htmlspecialchars($a['full_name']) ?></h6>
                <p class="small text-muted mb-2"><?= htmlspecialchars($a['email']) ?> · <?= date('d M Y', strtotime($a['applied_at'])) ?></p>
                <div class="p-3 mb-3 bg-light border rounded">
                  <?= nl2br(htmlspecialchars($a['cover_note'])) ?>
                </div>
              </div>
              <div class="flex-shrink-0 text-end">
                <span class="badge <?= $badge ?> d-block mb-2" style="width:fit-content; margin-left:auto;"><?= ucfirst($a['status']) ?></span>
                <form method="POST" class="d-flex flex-column gap-2 align-items-end">
                  <input type="hidden" name="app_id" value="<?= $a['id'] ?>">
                  <select name="status" class="form-select form-select-sm" style="width:140px;">
                    <option value="pending"  <?= $a['status']==='pending'  ?'selected':'' ?>>Pending</option>
                    <option value="accepted" <?= $a['status']==='accepted' ?'selected':'' ?>>Accepted</option>
                    <option value="rejected" <?= $a['status']==='rejected' ?'selected':'' ?>>Rejected</option>
                  </select>
                  <button type="submit" class="btn btn-sm btn-primary">Update</button>
                </form>
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
