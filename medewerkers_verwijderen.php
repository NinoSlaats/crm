<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: klanten.php");
    exit;
}

csrf_check();

// Alleen voor hogere rollen
if ($_SESSION['user_rol'] !== 'Medewerker' && isset($_POST['id'])) {
    $stmt = $conn->prepare("DELETE FROM klanten WHERE id = ?");
    $stmt->execute([intval($_POST['id'])]);
}
header("Location: klanten.php");
exit;