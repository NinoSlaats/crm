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
    csrf_check();

    $naam = trim($_POST['naam']);
    $email = trim($_POST['email']);

    // Alleen toegestane rollen accepteren (whitelist tegen manipulatie van het formulier)
    $toegestane_rollen = ['Medewerker', 'Verkoopmedewerker', 'Afdelingshoofd', 'Admin'];
    $rol = in_array($_POST['rol'], $toegestane_rollen) ? $_POST['rol'] : 'Medewerker';

    // Basisvalidatie
    if ($naam === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: medewerkers_bewerken.php?id=$id&fout=ongeldige_invoer");
        exit;
    }

    // Voorkom dat je jezelf degradeert naar een rol zonder beheerrechten (buitensluiten)
    if ($id === (int) $_SESSION['user_id'] && !in_array($rol, ['Afdelingshoofd', 'Verkoopmedewerker'])) {
        header("Location: medewerkers_bewerken.php?id=$id&fout=zelf_degraderen");
        exit;
    }

    // Check op e-mailadres dat al bij een ándere medewerker in gebruik is
    $stmt = $conn->prepare("SELECT id FROM medewerkers WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->fetch()) {
        header("Location: medewerkers_bewerken.php?id=$id&fout=email_bestaat");
        exit;
    }

    if (!empty($_POST['wachtwoord'])) {
        if (strlen($_POST['wachtwoord']) < 8) {
            header("Location: medewerkers_bewerken.php?id=$id&fout=wachtwoord_te_kort");
            exit;
        }
        $wachtwoord_hash = password_hash($_POST['wachtwoord'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE medewerkers SET naam = ?, email = ?, rol = ?, wachtwoord = ? WHERE id = ?");
        $stmt->execute([$naam, $email, $rol, $wachtwoord_hash, $id]);
    } else {
        $stmt = $conn->prepare("UPDATE medewerkers SET naam = ?, email = ?, rol = ? WHERE id = ?");
        $stmt->execute([$naam, $email, $rol, $id]);
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
            <?php csrf_veld(); ?>
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