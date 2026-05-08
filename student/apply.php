<?php
// student/apply.php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('student');

$user   = currentUser();
$uid    = $user['id'];
$job_id = intval($_GET['job_id'] ?? 0);

if (!$job_id) { header("Location: jobs.php"); exit(); }

// Fetch job
$stmt = $conn->prepare("SELECT j.*, u.full_name AS employer_name FROM jobs j JOIN users u ON j.employer_id=u.id WHERE j.id=? AND j.is_deleted=0 AND j.deadline>=CURDATE()");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

if (!$job) { header("Location: jobs.php"); exit(); }

// Already applied?
$check = $conn->prepare("SELECT id FROM applications WHERE job_id=? AND student_id=?");
$check->bind_param("ii", $job_id, $uid);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) { header("Location: jobs.php?applied=dup"); exit(); }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cover = trim($_POST['cover_note'] ?? '');
    if (strlen($cover) < 20) {
        $error = 'Cover note must be at least 20 characters.';
    } else {
        $ins = $conn->prepare("INSERT INTO applications (job_id, student_id, cover_note) VALUES (?,?,?)");
        $ins->bind_param("iis", $job_id, $uid, $cover);
        if ($ins->execute()) {
            header("Location: jobs.php?applied=1"); exit();
        } else {
            $error = 'Submission failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apply – <?= htmlspecialchars($job['title']) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php $nav_active='jobs'; include '../includes/navbar.php'; ?>

<div class="auth-wrapper" style="background:var(--bg);">
  <div style="width:100%; max-width:580px;">

    <!-- Job Summary -->
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title"><?= htmlspecialchars($job['title']) ?></h5>
        <div class="d-flex flex-wrap gap-2 mt-2">
          <span class="badge bg-secondary"><i class="fas fa-building"></i> <?= htmlspecialchars($job['employer_name']) ?></span>
          <span class="badge <?= $job['type']==='internship'?'bg-primary':'bg-warning text-dark' ?>"><?= ucfirst($job['type']) ?></span>
          <span class="badge bg-secondary"><i class="fas fa-location-dot"></i> <?= htmlspecialchars($job['location']) ?></span>
          <span class="badge bg-secondary"><i class="fas fa-calendar"></i> Deadline: <?= date('d M Y', strtotime($job['deadline'])) ?></span>
        </div>
        <p class="card-text text-muted small mt-3">
          <?= nl2br(htmlspecialchars($job['description'])) ?>
        </p>
      </div>
    </div>

    <!-- Apply Form -->
    <div class="card">
      <div class="card-body">
        <h2 class="card-title h5 mb-4"><i class="fas fa-paper-plane"></i> Submit Your Application</h2>

        <?php if ($error): ?>
          <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" id="applyForm" novalidate>
          <div class="mb-3">
            <label class="form-label" for="cover_note">Cover Note <span class="text-muted fw-normal">(min. 20 characters)</span></label>
            <textarea id="cover_note" name="cover_note" class="form-control"
                      rows="6" placeholder="Briefly explain why you are a good fit for this role..."
                      required><?= htmlspecialchars($_POST['cover_note'] ?? '') ?></textarea>
            <div class="invalid-feedback">Please write a cover note (at least 20 characters).</div>
            <div class="text-end small text-muted mt-2" id="charCount">0 characters</div>
          </div>

          <div class="d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Application</button>
            <a href="jobs.php" class="btn btn-outline-primary">Cancel</a>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>

<script>
const ta = document.getElementById('cover_note');
const cc = document.getElementById('charCount');
ta.addEventListener('input', () => { cc.textContent = ta.value.length + ' characters'; });

document.getElementById('applyForm').addEventListener('submit', function(e) {
  if (ta.value.trim().length < 20) {
    ta.classList.add('is-invalid'); e.preventDefault();
  } else { ta.classList.remove('is-invalid'); }
});
</script>
</body>
</html>
