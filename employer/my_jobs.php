<?php
// employer/my_jobs.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('employer');

$user = currentUser();
$uid  = $user['id'];
$nav_active = 'jobs';

// Handle delete
if ($_GET['delete'] ?? '') {
    $jid = intval($_GET['delete']);
    $conn->query("UPDATE jobs SET is_deleted=1 WHERE id=$jid AND employer_id=$uid");
    header("Location: my_jobs.php?msg=deleted"); exit();
}

$jobs = $conn->query("
    SELECT j.*, (SELECT COUNT(*) FROM applications a WHERE a.job_id=j.id) AS app_count
    FROM jobs j WHERE j.employer_id=$uid AND j.is_deleted=0 ORDER BY j.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Jobs – Job Portal</title>
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
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">My Job Listings</h1>
        <a href="post_job.php" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Post New</a>
      </div>

      <?php if (isset($_GET['msg'])): ?>
        <?php $msgs = ['posted'=>['alert-success','Job posted successfully!'],'deleted'=>['alert-danger','Job listing removed.'],'updated'=>['alert-success','Application status updated.']]; ?>
        <?php if (isset($msgs[$_GET['msg']])): [$cls,$txt]=$msgs[$_GET['msg']]; ?>
          <div class="alert <?= $cls ?>"><i class="fas fa-circle-check"></i> <?= $txt ?></div>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($jobs->num_rows === 0): ?>
        <div class="card text-center p-5">
          <i class="fas fa-briefcase" style="font-size:2.5rem; color:var(--border); margin-bottom:1rem;"></i>
          <p class="text-muted">You haven't posted any jobs yet.</p>
          <a href="post_job.php" class="btn btn-primary mt-3">Post Your First Job</a>
        </div>
      <?php else: ?>
        <?php while ($job = $jobs->fetch_assoc()):
          $expired = strtotime($job['deadline']) < time();
        ?>
        <div class="card mb-3">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
              <div class="flex-grow-1">
                <h5 class="card-title"><?= htmlspecialchars($job['title']) ?></h5>
                <div class="d-flex flex-wrap gap-2">
                  <span class="badge <?= $job['type']==='internship'?'bg-primary':'bg-warning text-dark' ?>"><?= ucfirst($job['type']) ?></span>
                  <span class="badge bg-secondary"><i class="fas fa-location-dot"></i> <?= htmlspecialchars($job['location']) ?></span>
                  <span class="badge <?= $expired?'bg-danger':'bg-success' ?>">
                    <i class="fas fa-calendar"></i> <?= $expired ? 'Expired' : 'Active' ?> · <?= date('d M Y', strtotime($job['deadline'])) ?>
                  </span>
                  <span class="badge bg-primary"><i class="fas fa-users"></i> <?= $job['app_count'] ?> applicant<?= $job['app_count']!=1?'s':'' ?></span>
                </div>
              </div>
              <div class="d-flex gap-2 flex-wrap flex-shrink-0">
                <a href="applications.php?job_id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-primary">
                  <i class="fas fa-eye"></i> View Apps
                </a>
                <a href="?delete=<?= $job['id'] ?>" class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Delete this job listing?')">
                  <i class="fas fa-trash"></i> Delete
                </a>
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
