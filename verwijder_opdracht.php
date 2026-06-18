<?php
require_once 'db.php';
session_start();

// Beveiliging: alleen voor niet-medewerkers
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] === 'Medewerker') {
    die("Geen toegang.");
}

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("DELETE FROM opdrachten WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}

// Stuur terug naar de vorige pagina
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>