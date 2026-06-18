<?php
require_once 'db.php';
// session_start(); // <--- Verwijder deze regel of zet er // voor, omdat db.php dit al doet!

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] === 'Medewerker') {
    die("Geen toegang.");
}

$id = $_GET['id'];

// Opslaan van wijzigingen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("UPDATE opdrachten SET naam = ?, uurprijs = ?, status = ? WHERE id = ?");
    $stmt->execute([$_POST['naam'], $_POST['uurprijs'], $_POST['status'], $id]);
    
    // Terug naar klanten overzicht of details
    header("Location: klanten.php");
    exit;
}

// Gegevens ophalen voor het formulier
$stmt = $conn->prepare("SELECT * FROM opdrachten WHERE id = ?");
$stmt->execute([$id]);
$o = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="card shadow p-4" style="max-width: 500px; margin: auto;">
        <h3>Opdracht bewerken</h3>
        <form method="POST">
            <div class="mb-3"><label>Naam</label><input type="text" name="naam" class="form-control" value="<?= htmlspecialchars($o['naam']) ?>" required></div>
            <div class="mb-3"><label>Uurprijs (€)</label><input type="number" step="0.01" name="uurprijs" class="form-control" value="<?= $o['uurprijs'] ?>" required></div>
            <div class="mb-3"><label>Status</label>
                <select name="status" class="form-select">
                    <option value="Actief" <?= $o['status'] == 'Actief' ? 'selected' : '' ?>>Actief</option>
                    <option value="Voldaan" <?= $o['status'] == 'Voldaan' ? 'selected' : '' ?>>Voldaan</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Wijzigingen opslaan</button>
            <a href="javascript:history.back()" class="btn btn-secondary w-100 mt-2">Annuleren</a>
        </form>
    </div>
</body>
</html>