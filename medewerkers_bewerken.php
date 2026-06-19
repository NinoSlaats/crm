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

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: medewerkers.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['wachtwoord'])) {
        $wachtwoord_hash = password_hash($_POST['wachtwoord'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE medewerkers SET naam = ?, email = ?, rol = ?, wachtwoord = ? WHERE id = ?");
        $stmt->execute([$_POST['naam'], $_POST['email'], $_POST['rol'], $wachtwoord_hash, $id]);
    } else {
        $stmt = $conn->prepare("UPDATE medewerkers SET naam = ?, email = ?, rol = ? WHERE id = ?");
        $stmt->execute([$_POST['naam'], $_POST['email'], $_POST['rol'], $id]);
    }
    header("Location: medewerkers.php?succes=bewerkt");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM medewerkers WHERE id = ?");
$stmt->execute([$id]);
$m = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$m) {
    header("Location: medewerkers.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM - Medewerker bewerken</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<?php include 'sidebar.php'; ?>

<main class="px-3 px-md-4 py-4">
    <h1 class="h2 mb-4">Medewerker bewerken</h1>

    <div class="card shadow-sm p-4 bg-white border" style="max-width: 500px;">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Naam</label>
                <input type="text" name="naam" class="form-control" value="<?= htmlspecialchars($m['naam']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">E-mailadres</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($m['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Rol</label>
                <select name="rol" class="form-select" required>
                    <?php foreach (['Medewerker', 'Verkoopmedewerker', 'Afdelingshoofd', 'Admin'] as $rol_optie): ?>
                        <option value="<?= $rol_optie; ?>" <?= $m['rol'] === $rol_optie ? 'selected' : ''; ?>><?= $rol_optie; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Nieuw wachtwoord</label>
                <input type="password" name="wachtwoord" class="form-control" placeholder="Laat leeg om ongewijzigd te laten">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Opslaan</button>
                <a href="medewerkers.php" class="btn btn-outline-secondary">Annuleren</a>
            </div>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>