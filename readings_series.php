<?php
// readings_series.php
session_start();
header('Content-Type: application/json');
require 'db.php';

$sub_id = (int)($_GET['subscription_id'] ?? 0);
if ($sub_id <= 0) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'subscription_id required']);
  exit;
}

// Authorise tech or client
$authorised = false;
if (isset($_SESSION['tech_id'])) {
  $tid = (int)$_SESSION['tech_id'];
  $q = $conn->prepare("SELECT 1 FROM subscriptions WHERE id=:id AND technician_id=:tid");
  $q->execute([':id'=>$sub_id, ':tid'=>$tid]);
  $authorised = $q->fetchColumn();
} elseif (isset($_SESSION['user_id'])) {
  $uid = (int)$_SESSION['user_id'];
  $q = $conn->prepare("SELECT 1 FROM subscriptions WHERE id=:id AND user_id=:uid");
  $q->execute([':id'=>$sub_id, ':uid'=>$uid]);
  $authorised = $q->fetchColumn();
}

if (!$authorised) {
  http_response_code(403);
  echo json_encode(['ok'=>false,'error'=>'Not authorised']);
  exit;
}

// ✅ Only fetch the two latest readings (previous + latest)
$stmt = $conn->prepare("
  SELECT moisture, gmax, compaction, height, temperature, rainfall, updated_at
  FROM field_readings
  WHERE subscription_id = :sid
  ORDER BY updated_at DESC
  LIMIT 2
");
$stmt->execute([':sid'=>$sub_id]);
$rows = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC)); // oldest → latest

if (!$rows) {
  echo json_encode(['ok'=>true,'labels'=>[]]);
  exit;
}

$labels=[]; $moist=[]; $gx=[]; $comp=[]; $h=[]; $temp=[]; $rain=[];

foreach ($rows as $r) {
  $labels[] = date('d/m H:i', strtotime($r['updated_at']));
  $moist[]  = $r['moisture']    !== null ? (float)$r['moisture']    : null;
  $gx[]     = $r['gmax']        !== null ? (float)$r['gmax']        : null;
  $comp[]   = $r['compaction']  !== null ? (float)$r['compaction']  : null;
  $h[]      = $r['height']      !== null ? (float)$r['height']      : null;
  $temp[]   = $r['temperature'] !== null ? (float)$r['temperature'] : null;
  $rain[]   = $r['rainfall']    !== null ? (float)$r['rainfall']    : null;
}

echo json_encode([
  'ok'=>true,
  'labels'=>$labels,
  'moisture'=>$moist,
  'gmax'=>$gx,
  'compaction'=>$comp,
  'height'=>$h,
  'temperature'=>$temp,
  'rainfall'=>$rain
]);
