<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "crm";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
} catch(PDOException $e) {
    die("Databaseverbinding mislukt: " . $e->getMessage());
}
?>