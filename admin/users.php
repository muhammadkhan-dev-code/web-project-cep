<?php
// admin/users.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');

$user = currentUser();
$nav_active = 'users';

// Ban/unban
if (isset($_GET['ban'])) {
    $id = intval($_GET['ban']);
    $conn->query("UPDATE users SET is_banned=1 WHERE id=$id AND role!='admin'");
    header("Location: users.php?msg=banned"); exit();
}
if (isset($_GET['unban'])) {
    $id = intval($_GET['unban']);
    $conn->query("UPDATE users SET is_banned=0 WHERE id=$id");
    header("Location: users.php?msg=unbanned"); exit();
}
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id=$id AND role!='admin'");
    header("Location: users.php?msg=deleted"); exit();
}

$search  = trim($_GET['search'] ?? '');
$role_f  = $_GET['role'] ?? '';
$where   = "role != 'admin'";
if ($search)  $where .= " AND (full_name LIKE '%".addslashes($search)."%' OR email LIKE '%".addslashes($search)."%')";
if ($role_f && in_array($role_f,['student','employer'])) $where .= " AND role='$role_f'";

$users = $conn->query("SELECT * FROM users WHERE $where ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users – Admin</title>
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
          <a href="users.php" class="nav-link active"><i class="fas fa-users"></i> Users</a>
          <a href="jobs.php" class="nav-link"><i class="fas fa-briefcase"></i> Jobs</a>
        </nav>
      </div>
      <a href="../logout.php" class="nav-link text-danger"><i class="fas fa-arrow-right-from-bracket"></i> Logout</a>
    </aside>

    <main class="col-lg-9 p-4">
      <h1 class="h3 mb-4">Manage Users</h1>

      <?php
      $msgs = ['banned'=>['alert-danger','User banned.'],'unbanned'=>['alert-success','User unbanned.'],'deleted'=>['alert-danger','User deleted.']];
      if (isset($_GET['msg']) && isset($msgs[$_GET['msg']])):
        [$cls,$txt] = $msgs[$_GET['msg']];
      ?>
        <div class="alert <?= $cls ?>"><i class="fas fa-circle-check"></i> <?= $txt ?></div>
      <?php endif; ?>

      <!-- Filter -->
      <form method="GET" class="row g-2 mb-4">
        <div class="col-md-5">
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
            <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>">
          </div>
        </div>
        <div class="col-md-3">
          <select name="role" class="form-select">
            <option value="">All Roles</option>
            <option value="student"  <?= $role_f==='student'  ?'selected':'' ?>>Students</option>
            <option value="employer" <?= $role_f==='employer' ?'selected':'' ?>>Employers</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <?php if ($search||$role_f): ?>
          <div class="col-md-2">
            <a href="users.php" class="btn btn-outline-primary w-100">Clear</a>
          </div>
        <?php endif; ?>
      </form>

      <div class="card">
        <table class="table table-striped mb-0">
          <thead>
            <tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Status</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php while ($u = $users->fetch_assoc()): ?>
            <tr>
              <td class="fw-bold"><?= htmlspecialchars($u['full_name']) ?></td>
              <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
              <td>
                <span class="badge <?= $u['role']==='student'?'bg-primary':'bg-warning text-dark' ?>">
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
              <td>
                <div class="d-flex gap-2 flex-wrap">
                  <?php if ($u['is_banned']): ?>
                    <a href="?unban=<?= $u['id'] ?>" class="btn btn-sm btn-outline-success">
                      <i class="fas fa-unlock"></i> Unban
                    </a>
                  <?php else: ?>
                    <a href="?ban=<?= $u['id'] ?>" class="btn btn-sm btn-outline-warning"
                       onclick="return confirm('Ban this user?')">
                      <i class="fas fa-ban"></i> Ban
                    </a>
                  <?php endif; ?>
                  <a href="?delete=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('Permanently delete this user?')">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
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
