<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("UPDATE werkzaamheden_personeel SET aantal_uren = ?, omschrijving = ? WHERE id = ? AND medewerker_id = ?");
    $stmt->execute([$_POST['aantal_uren'], $_POST['omschrijving'], $id, $_SESSION['user_id']]);
    header("Location: index.php?succes=aangepast");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM werkzaamheden_personeel WHERE id = ? AND medewerker_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$w = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$w) { die("Werkzaamheid niet gevonden."); }
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Werkzaamheid bewerken</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<?php include 'sidebar.php'; ?>

<main class="px-3 px-md-4 py-4">
    <div class="card shadow-sm p-4 bg-white border" style="max-width: 500px;">
        <h3>Werkzaamheid bewerken</h3>
        <hr>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Aantal uren</label>
                <input type="number" step="0.5" name="aantal_uren" class="form-control" value="<?= htmlspecialchars($w['aantal_uren']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Omschrijving</label>
                <textarea name="omschrijving" class="form-control" required><?= htmlspecialchars($w['omschrijving']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Wijzigingen opslaan</button>
            <a href="index.php" class="btn btn-secondary w-100 mt-2">Annuleren</a>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>