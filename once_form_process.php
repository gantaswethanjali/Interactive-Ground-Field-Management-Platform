<?php
session_start();

/**
 * once_form_process.php
 * Securely processes selections from once.php without changing your app flow.
 */

// --- Require POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Please submit the form to continue.';
    header('Location: once.php');
    exit;
}

// --- Whitelist of valid services (must match once.php tables) ---
$valid_services = [
    // Routine
    "Mowing (Regular Cutting)",
    "Edging & Trimming",
    "Aeration",
    "Fertilising",
    "Overseeding",
    "Top Dressing",
    "Scarification",
    "Watering / Irrigation",
    "Weed & Pest Control",
    "Line Marking",
    "Goal Mouth / Wear Zone Repair",
    // Specialised
    "Seasonal Renovation",
    "Compaction Relief",
    "Drainage Management",
    "Match Preparation",
    "Seasonal Grass Management",
    "Pest, Disease & Weed Monitoring",
];

// --- Read inputs ---
$selected_services = isset($_POST['services']) && is_array($_POST['services']) ? $_POST['services'] : [];
$dates_raw         = isset($_POST['service_dates']) && is_array($_POST['service_dates']) ? $_POST['service_dates'] : [];

// --- Clean service list: trim, dedupe, whitelist ---
$selected_services = array_values(array_unique(array_map('trim', $selected_services)));
$selected_services = array_values(array_intersect($selected_services, $valid_services));

// --- Clean dates: trim, dedupe, max 3, validate format + not past ---
$today = new DateTime('today');
$selected_dates = [];
foreach ($dates_raw as $d) {
    $d = trim((string)$d);
    if ($d === '') continue;

    $dt = DateTime::createFromFormat('Y-m-d', $d);
    $errors = DateTime::getLastErrors();

    if ($dt && $errors['warning_count'] === 0 && $errors['error_count'] === 0) {
        // ensure date is today or future
        if ($dt >= $today) {
            $selected_dates[] = $dt->format('Y-m-d');
        }
    }
}
$selected_dates = array_values(array_unique($selected_dates));
// cap at 3 dates
if (count($selected_dates) > 3) {
    $selected_dates = array_slice($selected_dates, 0, 3);
}

// --- Validate required selections ---
if (empty($selected_services) || empty($selected_dates)) {
    $_SESSION['error'] = "⚠️ Please select at least one service and one valid future date before continuing.";
    header("Location: once.php");
    exit;
}

// --- Save into session for the next steps ---
$_SESSION['one_time_selected_services'] = $selected_services;
$_SESSION['one_time_selected_dates']    = $selected_dates;

// --- Redirect after login target (no payment data ever collected) ---
$_SESSION['redirect_after_login'] = 'payment.php';

// --- Redirect based on auth status ---
if (!empty($_SESSION['user_id'])) {
    header('Location: payment.php');
} else {
    header('Location: signin.php');
}
exit;
