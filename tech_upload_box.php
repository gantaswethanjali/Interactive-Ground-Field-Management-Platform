<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db.php';

$tech_id = $_SESSION['tech_id'] ?? null;
$upload_msg = '';

// ------------------------------
// Handle DELETE
// ------------------------------
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $file_id = (int)$_GET['delete'];
    $check = $conn->prepare("SELECT file_path FROM technician_files WHERE id = :id AND technician_id = :tid");
    $check->execute([':id' => $file_id, ':tid' => $tech_id]);
    $f = $check->fetch(PDO::FETCH_ASSOC);
    if ($f) {
        @unlink(__DIR__ . '/' . $f['file_path']); // remove from disk
        $del = $conn->prepare("DELETE FROM technician_files WHERE id = :id");
        $del->execute([':id' => $file_id]);
        $upload_msg = "🗑️ File deleted.";
    } else {
        $upload_msg = "❌ File not found or not yours.";
    }
}

// ------------------------------
// Handle UPLOAD
// ------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_upload'])) {
    $client_email = trim($_POST['client_email']);

    // Find the client by email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $client_email]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        $upload_msg = "❌ No client found with that email.";
    } else {
        $client_id = $client['id'];
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_name = basename($_FILES['file_upload']['name']);
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Only allow PDFs
        if ($file_type !== 'pdf') {
            $upload_msg = "❌ Only PDF files are allowed. Please export your document as PDF.";
        } elseif ($_FILES['file_upload']['error'] !== UPLOAD_ERR_OK) {
            $upload_msg = "❌ Upload error.";
        } else {
            $target_file = $upload_dir . time() . '_' . preg_replace('/[^\w.\- ]+/', '_', $file_name);
            if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $target_file)) {
                $insert = $conn->prepare("
                    INSERT INTO technician_files (technician_id, client_id, file_name, file_path)
                    VALUES (:tid, :cid, :fname, :fpath)
                ");
                $insert->execute([
                    ':tid' => $tech_id,
                    ':cid' => $client_id,
                    ':fname' => $file_name,
                    ':fpath' => $target_file
                ]);

                // Flash message + redirect (no header warning)
                $_SESSION['upload_msg'] = "✅ PDF uploaded successfully for client: " . htmlspecialchars($client_email);

                $dest = $_SERVER['PHP_SELF'];
                if (!headers_sent()) {
                    header("Location: " . $dest);
                    exit;
                } else {
                    echo '<script>location.href=' . json_encode($dest) . ';</script>';
                    exit;
                }
            } else {
                $upload_msg = "❌ Failed to upload file.";
            }
        }
    }
}

// ------------------------------
// Fetch technician's own files
// ------------------------------
$stmt = $conn->prepare("
    SELECT f.id, f.file_name, f.uploaded_at, u.email AS client_email, f.client_visible
    FROM technician_files f
    JOIN users u ON f.client_id = u.id
    WHERE f.technician_id = :tid
    ORDER BY f.uploaded_at DESC
");
$stmt->execute([':tid' => $tech_id]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card p-3 shadow">
  <h5 class="mb-3">Upload Client PDF</h5>

  <?php if (isset($_SESSION['upload_msg'])): ?>
    <div class="alert alert-info"><?= $_SESSION['upload_msg'] ?></div>
    <?php unset($_SESSION['upload_msg']); ?>
  <?php endif; ?>

  <?php if (!empty($upload_msg)): ?>
    <div class="alert alert-info"><?= htmlspecialchars($upload_msg) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="mb-3">
    <div class="mb-2">
      <label class="form-label">Client Email</label>
      <input type="email" name="client_email" class="form-control" placeholder="client@example.com" required>
    </div>

    <div class="mb-2">
      <label class="form-label">Select PDF</label>
      <input type="file" name="file_upload" class="form-control" accept=".pdf" required>
      <div class="form-text">Only PDF files allowed</div>
    </div>

    <button type="submit" class="btn btn-primary w-100">Upload PDF</button>
  </form>

  <h6 class="mt-4">Your Uploaded Files</h6>
  <?php if ($files): ?>
    <div class="list-group" style="max-height:400px; overflow-y:auto;">
      <?php foreach ($files as $file): ?>
        <div class="list-group-item d-flex justify-content-between align-items-start">
          <div>
            <a href="viewer.php?id=<?= (int)$file['id'] ?>" target="_blank" class="text-decoration-none">
              <?= htmlspecialchars($file['file_name']) ?>
            </a>
            <br>
            <small class="text-muted">
              Client: <?= htmlspecialchars($file['client_email']) ?> | <?= htmlspecialchars($file['uploaded_at']) ?>
              <?php if (isset($file['client_visible']) && !$file['client_visible']): ?>
                <span class="badge bg-warning text-dark ms-2">Hidden by client</span>
              <?php endif; ?>
            </small>
          </div>
          <a href="?delete=<?= (int)$file['id'] ?>" class="btn btn-sm btn-danger"
             onclick="return confirm('Delete this file permanently?')">
            Delete
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-secondary">No files uploaded yet.</div>
  <?php endif; ?>
</div>
