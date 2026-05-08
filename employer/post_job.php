<?php
// employer/post_job.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('employer');

$user = currentUser();
$uid  = $user['id'];
$nav_active = 'post';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type        = $_POST['type'] ?? '';
    $location    = trim($_POST['location'] ?? '');
    $deadline    = $_POST['deadline'] ?? '';

    if (empty($title) || empty($description) || empty($type) || empty($location) || empty($deadline)) {
        $error = 'All fields are required.';
    } elseif (!in_array($type, ['part-time','internship'])) {
        $error = 'Invalid job type.';
    } elseif (strtotime($deadline) <= time()) {
        $error = 'Deadline must be a future date.';
    } else {
        $stmt = $conn->prepare("INSERT INTO jobs (employer_id, title, description, type, location, deadline) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("isssss", $uid, $title, $description, $type, $location, $deadline);
        if ($stmt->execute()) {
            header("Location: my_jobs.php?posted=1"); exit();
        } else {
            $error = 'Failed to post job. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Post a Job – Job Portal</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container-fluid d-flex align-items-center justify-content-center" style="min-height:calc(100vh - 80px); background:var(--bg);">
  <div style="width:100%; max-width:600px;">
    <div class="card">
      <div class="card-body">
        <h2 class="card-title mb-4"><i class="fas fa-plus-circle text-primary"></i> Post a New Job</h2>

        <?php if ($error): ?>
          <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" id="jobForm" novalidate>
          <div class="mb-3">
            <label class="form-label">Job Title</label>
            <input type="text" name="title" class="form-control" placeholder="e.g. Frontend Developer Intern"
                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
            <div class="invalid-feedback">Job title is required.</div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Job Type</label>
                <select name="type" class="form-select" required>
                  <option value="">Select type</option>
                  <option value="part-time"  <?= ($_POST['type']??'')==='part-time'  ?'selected':'' ?>>Part-time</option>
                  <option value="internship" <?= ($_POST['type']??'')==='internship' ?'selected':'' ?>>Internship</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-control" placeholder="e.g. Karachi / Remote"
                       value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" required>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Application Deadline</label>
            <input type="date" name="deadline" class="form-control"
                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                   value="<?= htmlspecialchars($_POST['deadline'] ?? '') ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Job Description</label>
            <textarea name="description" class="form-control" rows="6"
                      placeholder="Describe the role, responsibilities, and requirements..."
                      required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            <div class="invalid-feedback">Description is required.</div>
          </div>

          <div class="d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Post Job</button>
            <a href="my_jobs.php" class="btn btn-outline-primary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('jobForm').addEventListener('submit', function(e) {
  let valid = true;
  document.querySelectorAll('[required]').forEach(el => {
    if (!el.value.trim()) { el.classList.add('is-invalid'); valid = false; }
    else el.classList.remove('is-invalid');
  });
  if (!valid) e.preventDefault();
});
</script>
</body>
</html>
