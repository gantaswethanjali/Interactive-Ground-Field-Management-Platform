<?php
// readings_save.php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['tech_id'])) {
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'Technician not logged in']);
  exit;
}
$tech_id = (int)$_SESSION['tech_id'];

$sub_id = (int)($_POST['subscription_id'] ?? 0);
$client_email = trim($_POST['client_email'] ?? '');

if ($sub_id <= 0 || $client_email === '') {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'subscription_id and client_email are required']);
  exit;
}

// Ensure this subscription belongs to this tech
$q = $conn->prepare("SELECT s.id, s.user_id, u.email AS user_email
                     FROM subscriptions s
                     JOIN users u ON s.user_id = u.id
                     WHERE s.id=:sid AND s.technician_id=:tid");
$q->execute([':sid'=>$sub_id, ':tid'=>$tech_id]);
$row = $q->fetch(PDO::FETCH_ASSOC);
if (!$row) {
  http_response_code(403);
  echo json_encode(['ok'=>false,'error'=>'Not authorised for this subscription']);
  exit;
}

// Check client email matches the subscription user
if (strcasecmp($client_email, $row['user_email']) !== 0) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Client email does not match this subscription']);
  exit;
}

// Normalise numeric inputs — allow nulls
$fields = ['moisture','gmax','compaction','height','temperature','rainfall'];
$data = [];
foreach ($fields as $f) {
  $v = $_POST[$f] ?? '';
  $data[$f] = ($v === '' || $v === null) ? null : (float)$v;
}
$notes = trim($_POST['notes'] ?? '');

$stmt = $conn->prepare("
  INSERT INTO field_readings
    (subscription_id, technician_id, moisture, gmax, compaction, height, temperature, rainfall, notes)
  VALUES
    (:sid, :tid, :moisture, :gmax, :compaction, :height, :temperature, :rainfall, :notes)
");
$stmt->execute([
  ':sid' => $sub_id,
  ':tid' => $tech_id,
  ':moisture' => $data['moisture'],
  ':gmax' => $data['gmax'],
  ':compaction' => $data['compaction'],
  ':height' => $data['height'],
  ':temperature' => $data['temperature'],
  ':rainfall' => $data['rainfall'],
  ':notes' => $notes !== '' ? $notes : null
]);

echo json_encode(['ok'=>true]);
