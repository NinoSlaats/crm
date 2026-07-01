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
    csrf_check();

    $naam = trim($_POST['naam']);
    $email = trim($_POST['email']);
    $wachtwoord = $_POST['wachtwoord'];

    // Alleen toegestane rollen accepteren (whitelist tegen manipulatie van het formulier)
    $toegestane_rollen = ['Medewerker', 'Verkoopmedewerker', 'Afdelingshoofd', 'Admin'];
    $rol = in_array($_POST['rol'], $toegestane_rollen) ? $_POST['rol'] : 'Medewerker';

    // Basisvalidatie
    if ($naam === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: medewerkers.php?fout=ongeldige_invoer");
        exit;
    }
    if (strlen($wachtwoord) < 8) {
        header("Location: medewerkers.php?fout=wachtwoord_te_kort");
        exit;
    }

    // Check op bestaand e-mailadres
    $stmt = $conn->prepare("SELECT id FROM medewerkers WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: medewerkers.php?fout=email_bestaat");
        exit;
    }

    $wachtwoord_hash = password_hash($wachtwoord, PASSWORD_DEFAULT);

    try {
        $stmt = $conn->prepare("INSERT INTO medewerkers (naam, email, wachtwoord, rol) VALUES (?, ?, ?, ?)");
        $stmt->execute([$naam, $email, $wachtwoord_hash, $rol]);
        header("Location: medewerkers.php?succes=toegevoegd");
    } catch (PDOException $e) {
        header("Location: medewerkers.php?fout=opslaan_mislukt");
    }
    exit;
}

header("Location: medewerkers.php");
exit;