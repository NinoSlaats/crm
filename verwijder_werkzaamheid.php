<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

csrf_check();

// Waar moeten we naartoe na verwijderen? Whitelist tegen open-redirect.
$toegestane_paginas = ['index.php', 'uren_schrijven.php'];
$terug = $_POST['terug'] ?? 'uren_schrijven.php';
if (!in_array($terug, $toegestane_paginas)) {
    $terug = 'uren_schrijven.php';
}

// Controleer of er een ID is meegegeven
if (isset($_POST['id'])) {
    // Verwijder uit de juiste tabel (werkzaamheden)
    // De 'AND medewerker_id = ?' is cruciaal voor de veiligheid!
    $stmt = $conn->prepare("DELETE FROM werkzaamheden WHERE id = ? AND medewerker_id = ?");
    $stmt->execute([intval($_POST['id']), $_SESSION['user_id']]);
}

// Stuur terug naar de pagina waar de verwijdering vandaan kwam
header("Location: $terug?succes=werkzaamheid_verwijderd");
exit;
?>