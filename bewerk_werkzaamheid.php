<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Waar moeten we naartoe na opslaan/annuleren? Whitelist tegen open-redirect.
$toegestane_paginas = ['index.php', 'uren_schrijven.php'];
$terug = $_REQUEST['terug'] ?? 'uren_schrijven.php';
if (!in_array($terug, $toegestane_paginas)) {
    $terug = 'uren_schrijven.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $opdracht_id = intval($_POST['opdracht_id']);
    $datum = $_POST['datum'];
    $aantal_uren = $_POST['aantal_uren'];
    $omschrijving = trim($_POST['omschrijving']);

    if ($opdracht_id <= 0 || empty($datum) || !is_numeric($aantal_uren) || $aantal_uren <= 0 || $omschrijving === '') {
        header("Location: bewerk_werkzaamheid.php?id=$id&terug=$terug&fout=ongeldige_invoer");
        exit;
    }

    $stmt = $conn->prepare("UPDATE werkzaamheden SET opdracht_id = ?, datum = ?, aantal_uren = ?, omschrijving = ? WHERE id = ? AND medewerker_id = ?");
    $stmt->execute([$opdracht_id, $datum, $aantal_uren, $omschrijving, $id, $_SESSION['user_id']]);

    header("Location: $terug?succes=aangepast");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM werkzaamheden WHERE id = ? AND medewerker_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$w = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$w) { die("Werkzaamheid niet gevonden."); }

// Actieve opdrachten ophalen (+ de huidige opdracht, ook als die inmiddels 'Voldaan' is)
$stmt = $conn->prepare("SELECT id, naam FROM opdrachten WHERE status = 'Actief' OR id = ? ORDER BY naam ASC");
$stmt->execute([$w['opdracht_id']]);
$opdrachten = $stmt->fetchAll(PDO::FETCH_ASSOC);

$fout_tekst = '';
if (isset($_GET['fout']) && $_GET['fout'] === 'ongeldige_invoer') {
    $fout_tekst = 'Vul een opdracht, datum, aantal uren en omschrijving in.';
}
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

        <?php if (!empty($fout_tekst)): ?>
            <div class="alert alert-danger shadow-sm"><?= htmlspecialchars($fout_tekst); ?></div>
        <?php endif; ?>

        <form method="POST" action="bewerk_werkzaamheid.php?id=<?= $id; ?>">
            <?php csrf_veld(); ?>
            <input type="hidden" name="terug" value="<?= htmlspecialchars($terug); ?>">

            <div class="mb-3">
                <label class="form-label">Opdracht</label>
                <select name="opdracht_id" class="form-select" required>
                    <?php foreach ($opdrachten as $o): ?>
                        <option value="<?= $o['id']; ?>" <?= $o['id'] == $w['opdracht_id'] ? 'selected' : ''; ?>><?= htmlspecialchars($o['naam']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Datum</label>
                <input type="date" name="datum" class="form-control" value="<?= htmlspecialchars($w['datum']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Aantal uren</label>
                <input type="number" step="0.25" min="0.25" max="24" name="aantal_uren" class="form-control" value="<?= htmlspecialchars($w['aantal_uren']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Omschrijving</label>
                <textarea name="omschrijving" class="form-control" rows="3" required><?= htmlspecialchars($w['omschrijving']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Wijzigingen opslaan</button>
            <a href="<?= htmlspecialchars($terug); ?>" class="btn btn-secondary w-100 mt-2">Annuleren</a>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>