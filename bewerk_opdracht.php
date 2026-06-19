<?php
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] === 'Medewerker') {
    header("Location: login.php"); exit;
}

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("UPDATE opdrachten SET naam = ?, uurprijs = ?, status = ? WHERE id = ?");
    $stmt->execute([$_POST['naam'], $_POST['uurprijs'], $_POST['status'], $id]);
    header("Location: klanten.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM opdrachten WHERE id = ?");
$stmt->execute([$id]);
$o = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opdracht bewerken</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<?php include 'sidebar.php'; ?>

<main class="px-3 px-md-4 py-4">
    <div class="card shadow-sm p-4 bg-white border" style="max-width: 500px;">
        <h3>Opdracht bewerken</h3>
        <hr>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Naam</label>
                <input type="text" name="naam" class="form-control" value="<?= htmlspecialchars($o['naam']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Uurprijs (€)</label>
                <input type="number" step="0.01" name="uurprijs" class="form-control" value="<?= $o['uurprijs'] ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="Actief" <?= $o['status'] == 'Actief' ? 'selected' : '' ?>>Actief</option>
                    <option value="Voldaan" <?= $o['status'] == 'Voldaan' ? 'selected' : '' ?>>Voldaan</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Wijzigingen opslaan</button>
            <a href="javascript:history.back()" class="btn btn-secondary w-100 mt-2">Annuleren</a>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>