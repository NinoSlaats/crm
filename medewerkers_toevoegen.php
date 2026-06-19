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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam = trim($_POST['naam']);
    $email = trim($_POST['email']);
    $rol = $_POST['rol'];
    $wachtwoord_hash = password_hash($_POST['wachtwoord'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO medewerkers (naam, email, wachtwoord, rol) VALUES (?, ?, ?, ?)");
    $stmt->execute([$naam, $email, $wachtwoord_hash, $rol]);

    header("Location: medewerkers.php?succes=toegevoegd");
    exit;
}

header("Location: medewerkers.php");
exit;