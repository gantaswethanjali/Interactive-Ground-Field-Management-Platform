<?php
$host = "localhost";
$db = "fieldcraft_db";
$user = "root"; // default XAMPP user
$pass = "";     // default XAMPP password is empty

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
