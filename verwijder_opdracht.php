<?php
require_once 'db.php';

// Beveiliging: alleen voor niet-medewerkers
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] === 'Medewerker') {
    die("Geen toegang.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: klanten.php");
    exit;
}

csrf_check();

$klant_id = 0;
if (isset($_POST['id'])) {
    // klant_id ophalen zodat we netjes terug kunnen naar de juiste klantpagina
    $stmt = $conn->prepare("SELECT klant_id FROM opdrachten WHERE id = ?");
    $stmt->execute([intval($_POST['id'])]);
    $opdracht = $stmt->fetch(PDO::FETCH_ASSOC);
    $klant_id = $opdracht['klant_id'] ?? 0;

    $stmt = $conn->prepare("DELETE FROM opdrachten WHERE id = ?");
    $stmt->execute([intval($_POST['id'])]);
}

// Terug naar de klantpagina (vaste, voorspelbare redirect i.p.v. HTTP_REFERER)
if ($klant_id > 0) {
    header("Location: klant_details.php?id=" . $klant_id);
} else {
    header("Location: klanten.php");
}
exit;
?>