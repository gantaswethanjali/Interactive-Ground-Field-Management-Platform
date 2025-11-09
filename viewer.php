<?php
require_once 'db.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT file_path FROM technician_files WHERE id = :id");
$stmt->execute([':id' => $id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    http_response_code(404);
    exit('File not found.');
}

$file = $row['file_path'];
$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$mime_types = [
  'pdf' => 'application/pdf',
  'jpg' => 'image/jpeg',
  'jpeg' => 'image/jpeg',
  'png' => 'image/png',
  'doc' => 'application/msword',
  'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'xls' => 'application/vnd.ms-excel',
  'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];

if (isset($mime_types[$ext])) {
    header("Content-Type: " . $mime_types[$ext]);
    header("Content-Disposition: inline; filename=\"" . basename($file) . "\"");
    readfile($file);
} else {
    echo "Unsupported file type.";
}
?>
