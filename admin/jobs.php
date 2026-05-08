<?php
// admin/jobs.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');

$user = currentUser();
$nav_active = 'jobs';

// Delete job
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("UPDATE jobs SET is_deleted=1 WHERE id=$id");
    header("Location: jobs.php?msg=deleted"); exit();
}

$search = trim($_GET['search'] ?? '');
$where  = "j.is_deleted=0";
if ($search) $where .= " AND (j.title LIKE '%".addslashes($search)."%' OR u.full_name LIKE '%".addslashes($search)."%')";

$jobs = $conn->query("
    SELECT j.*, u.full_name AS employer_name,
    (SELECT COUNT(*) FROM applications a WHERE a.job_id=j.id) AS app_count
    FROM jobs j JOIN users u ON j.employer_id=u.id
    WHERE $where ORDER BY j.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Jobs – Admin</title>
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
          <a href="dashboard.php" class="nav-link"><i class="fas fa-gauge"></i> Overview</a>
          <a href="users.php" class="nav-link"><i class="fas fa-users"></i> Users</a>
          <a href="jobs.php" class="nav-link active"><i class="fas fa-briefcase"></i> Jobs</a>
        </nav>
      </div>
      <a href="../logout.php" class="nav-link text-danger"><i class="fas fa-arrow-right-from-bracket"></i> Logout</a>
    </aside>

    <main class="col-lg-9 p-4">
      <h1 class="h3 mb-4">Manage Job Listings</h1>

      <?php if (isset($_GET['msg']) && $_GET['msg']==='deleted'): ?>
        <div class="alert alert-danger"><i class="fas fa-trash"></i> Job listing removed.</div>
      <?php endif; ?>

      <form method="GET" class="row g-2 mb-4">
        <div class="col-md-8">
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
            <input type="text" name="search" class="form-control" placeholder="Search jobs or employers..."
                   value="<?= htmlspecialchars($search) ?>">
          </div>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
        <?php if ($search): ?>
          <div class="col-md-2">
            <a href="jobs.php" class="btn btn-outline-primary w-100">Clear</a>
          </div>
        <?php endif; ?>
      </form>

      <div class="card">
        <table class="table table-striped mb-0">
          <thead>
            <tr><th>Title</th><th>Employer</th><th>Type</th><th>Deadline</th><th>Apps</th><th>Action</th></tr>
          </thead>
          <tbody>
            <?php while ($j = $jobs->fetch_assoc()):
              $expired = strtotime($j['deadline']) < time();
            ?>
            <tr>
              <td class="fw-bold"><?= htmlspecialchars($j['title']) ?></td>
              <td class="text-muted"><?= htmlspecialchars($j['employer_name']) ?></td>
              <td>
                <span class="badge <?= $j['type']==='internship'?'bg-primary':'bg-warning text-dark' ?>">
                  <?= ucfirst($j['type']) ?>
                </span>
              </td>
              <td>
                <span class="badge <?= $expired?'bg-danger':'bg-success' ?>">
                  <?= date('d M Y', strtotime($j['deadline'])) ?>
                </span>
              </td>
              <td><?= $j['app_count'] ?></td>
              <td>
                <a href="?delete=<?= $j['id'] ?>" class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Remove this job listing?')">
                  <i class="fas fa-trash"></i> Remove
                </a>
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
