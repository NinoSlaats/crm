<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$mag_beheren = isset($_SESSION['user_rol']) && in_array($_SESSION['user_rol'], ['Afdelingshoofd', 'Verkoopmedewerker']);
if (!$mag_beheren) {
    header("Location: medewerkers.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM medewerkers WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: medewerkers.php?succes=verwijderd");
exit;