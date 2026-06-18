<?php
session_start();
require_once 'db.php';
// Alleen voor hogere rollen
if ($_SESSION['user_rol'] !== 'Medewerker' && isset($_GET['id'])) {
    $stmt = $conn->prepare("DELETE FROM klanten WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}
header("Location: klanten.php");
exit;