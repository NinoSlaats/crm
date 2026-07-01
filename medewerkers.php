<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Lijst met rollen die de medewerkerslijst mogen bekijken
$toegestane_rollen = ['Admin', 'Afdelingshoofd', 'Verkoopmedewerker'];

if (!isset($_SESSION['user_rol']) || !in_array($_SESSION['user_rol'], $toegestane_rollen)) {
    header("Location: index.php");
    exit;
}

// Rollen die mogen toevoegen/bewerken/verwijderen
$mag_beheren = in_array($_SESSION['user_rol'], ['Afdelingshoofd', 'Verkoopmedewerker']);

$succes_melding = '';
if (isset($_GET['succes'])) {
    switch ($_GET['succes']) {
        case 'toegevoegd':
            $succes_melding = 'Medewerker is succesvol toegevoegd.';
            break;
        case 'bewerkt':
            $succes_melding = 'Medewerker is succesvol bijgewerkt.';
            break;
        case 'verwijderd':
            $succes_melding = 'Medewerker is verwijderd.';
            break;
    }
}

$stmt = $conn->query("SELECT id, naam, email, rol FROM medewerkers ORDER BY naam ASC");
$medewerkers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM - Medewerkers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<?php include 'sidebar.php'; ?>

<main class="px-3 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Medewerkersbeheer</h1>
        <?php if ($mag_beheren): ?>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#toevoegenModal">
                + Nieuwe medewerker
            </button>
        <?php endif; ?>
    </div>

    <?php if (!empty($succes_melding)): ?>
        <div id="succesMelding" class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <?= htmlspecialchars($succes_melding); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Sluiten"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm p-4 bg-white border">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Naam</th>
                        <th>E-mailadres</th>
                        <th>Rol</th>
                        <?php if ($mag_beheren): ?><th style="width: 200px;">Actie</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($medewerkers) > 0): ?>
                        <?php foreach ($medewerkers as $m): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($m['naam']); ?></strong></td>
                                <td><?= htmlspecialchars($m['email']); ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($m['rol']); ?></span></td>
                                <?php if ($mag_beheren): ?>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="medewerkers_bewerken.php?id=<?= $m['id']; ?>" class="btn btn-sm btn-warning">Bewerk</a>
                                        <a href="medewerkers_verwijderen.php?id=<?= $m['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Weet je zeker dat je deze medewerker wilt verwijderen?');">Verwijder</a>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="<?= $mag_beheren ? 4 : 3; ?>" class="text-center text-muted py-3">Geen medewerkers gevonden.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php if ($mag_beheren): ?>
<div class="modal fade" id="toevoegenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="medewerkers_toevoegen.php">
                <div class="modal-header">
                    <h5 class="modal-title">Nieuwe medewerker toevoegen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Naam</label>
                        <input type="text" name="naam" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-mailadres</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Wachtwoord</label>
                        <input type="password" name="wachtwoord" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select name="rol" class="form-select" required>
                            <option value="Medewerker">Medewerker</option>
                            <option value="Verkoopmedewerker">Verkoopmedewerker</option>
                            <option value="Afdelingshoofd">Afdelingshoofd</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuleren</button>
                    <button type="submit" class="btn btn-success">Opslaan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Melding automatisch laten verdwijnen na 4 seconden
    const succesMelding = document.getElementById('succesMelding');
    if (succesMelding) {
        setTimeout(() => {
            const alertInstance = bootstrap.Alert.getOrCreateInstance(succesMelding);
            alertInstance.close();
        }, 4000);
    }
</script>
</body>
</html>