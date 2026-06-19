<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("INSERT INTO werkzaamheden_personeel (medewerker_id, datum, aantal_uren, omschrijving) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_POST['datum'], $_POST['aantal_uren'], $_POST['omschrijving']]);
    header("Location: index.php?succes=werkzaamheid_toegevoegd");
    exit;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Werkzaamheid toevoegen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<?php include 'sidebar.php'; ?>

<main class="px-3 px-md-4 py-4">
    <div class="card shadow-sm p-4 bg-white border" style="max-width: 500px;">
        <h3>Nieuwe werkzaamheid</h3>
        <hr>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Opdracht</label>
                <select name="opdracht_id" class="form-select" required>
                    <option value="">-- Selecteer een opdracht --</option>
                    <?php
                    $opdrachten = $conn->query("SELECT id, naam FROM opdrachten WHERE status = 'Actief'");
                    foreach($opdrachten as $o) {
                        echo "<option value='" . $o['id'] . "'>" . htmlspecialchars($o['naam']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Datum</label>
                <input type="date" name="datum" class="form-control" value="<?= date('Y-m-d'); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Aantal uren</label>
                <input type="number" step="0.5" name="aantal_uren" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Omschrijving</label>
                <textarea name="omschrijving" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Opslaan</button>
            <a href="index.php" class="btn btn-secondary w-100 mt-2">Annuleren</a>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>