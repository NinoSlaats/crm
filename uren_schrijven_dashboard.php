<?php
session_start();
require_once 'db.php';

// Check of gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Verwerk het formulier als er op opslaan is geklikt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $opdracht_id = $_POST['opdracht_id'];
    $aantal_uren = $_POST['aantal_uren'];
    $omschrijving = $_POST['omschrijving'];
    $datum = $_POST['datum'];
    $medewerker_id = $_SESSION['user_id']; // De ingelogde gebruiker

    // Voeg de werkzaamheid toe
    $stmt = $conn->prepare("INSERT INTO werkzaamheden_personeel (medewerker_id, datum, aantal_uren, omschrijving) VALUES (?, ?, ?, ?)");
$stmt->execute([$_SESSION['user_id'], $_POST['datum'], $_POST['aantal_uren'], $_POST['omschrijving']]);

    // Terug naar dashboard met succesmelding
    header("Location: index.php?succes=werkzaamheid_toegevoegd");
    exit;
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Werkzaamheid toevoegen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="card shadow p-4 mx-auto" style="max-width: 500px;">
        <h3>Nieuwe werkzaamheid</h3>
        <form method="POST">
            <div class="mb-3">
                <label>Opdracht</label>
                <select name="opdracht_id" class="form-control" required>
                    <?php
                    // Haal alle actieve opdrachten op om uit te kiezen
                    $opdrachten = $conn->query("SELECT id, naam FROM opdrachten WHERE status = 'Actief'");
                    foreach($opdrachten as $o) {
                        echo "<option value='".$o['id']."'>".$o['naam']."</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3"><label>Datum</label><input type="date" name="datum" class="form-control" value="<?= date('Y-m-d'); ?>" required></div>
            <div class="mb-3"><label>Aantal uren</label><input type="number" step="0.5" name="aantal_uren" class="form-control" required></div>
            <div class="mb-3"><label>Omschrijving</label><textarea name="omschrijving" class="form-control" required></textarea></div>
            <button type="submit" class="btn btn-primary w-100">Opslaan</button>
            <a href="index.php" class="btn btn-secondary w-100 mt-2">Annuleren</a>
        </form>
    </div>
</body>
</html>