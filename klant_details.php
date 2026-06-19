<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$klant_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM klanten WHERE id = :id");
$stmt->execute(['id' => $klant_id]);
$klant = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$klant) { die("Klant niet gevonden."); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actie_opdracht_toevoegen'])) {
    if ($_SESSION['user_rol'] !== 'Medewerker') {
        $stmt = $conn->prepare("INSERT INTO opdrachten (klant_id, naam, startdatum, einddatum, status, uurprijs) VALUES (:klant_id, :naam, :startdatum, :einddatum, :status, :uurprijs)");
        $stmt->execute([
            'klant_id' => $klant_id, 'naam' => $_POST['naam'], 'startdatum' => $_POST['startdatum'],
            'einddatum' => !empty($_POST['einddatum']) ? $_POST['einddatum'] : null,
            'status' => $_POST['status'], 'uurprijs' => $_POST['uurprijs']
        ]);
        header("Location: klant_details.php?id=" . $klant_id . "&succes=1");
        exit;
    }
}

$stmt = $conn->prepare("SELECT * FROM opdrachten WHERE klant_id = :id AND status = 'Actief'");
$stmt->execute(['id' => $klant_id]);
$actieve_opdrachten = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM opdrachten WHERE klant_id = :id AND status = 'Voldaan'");
$stmt->execute(['id' => $klant_id]);
$voldane_opdrachten = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM - Opdrachten van <?= htmlspecialchars($klant['bedrijfsnaam']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<?php include 'sidebar.php'; ?>

<main class="px-3 px-md-4 py-4">
    <a href="klanten.php" class="btn btn-sm btn-outline-secondary mb-3">← Terug naar klanten</a>
    <h1 class="h2 mb-4"><?= htmlspecialchars($klant['bedrijfsnaam']); ?></h1>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm p-3 mb-4 bg-white border">
                <h4 class="text-primary mb-3">Actieve Opdrachten</h4>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Naam</th><th>Datum</th><th>Prijs</th><th>Actie</th></tr></thead>
                        <tbody>
                            <?php foreach($actieve_opdrachten as $o): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($o['naam']); ?></strong></td>
                                    <td><?= date('d-m-Y', strtotime($o['startdatum'])); ?></td>
                                    <td>€ <?= number_format($o['uurprijs'], 2, ',', '.'); ?></td>
                                    <td>
                                        <a href="bewerk_opdracht.php?id=<?= $o['id']; ?>" class="btn btn-sm btn-warning">Bewerk</a>
                                        <a href="verwijder_opdracht.php?id=<?= $o['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Verwijderen?');">Verwijder</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if(empty($actieve_opdrachten)): ?>
                                <tr><td colspan="4" class="text-center text-muted">Geen actieve opdrachten.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm p-3 bg-white border">
                <h4 class="text-success mb-3">Voldane Opdrachten</h4>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Naam</th><th>Datum</th><th>Prijs</th><th>Actie</th></tr></thead>
                        <tbody>
                            <?php foreach($voldane_opdrachten as $o): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($o['naam']); ?></strong></td>
                                    <td><?= $o['einddatum'] ? date('d-m-Y', strtotime($o['einddatum'])) : '-'; ?></td>
                                    <td>€ <?= number_format($o['uurprijs'], 2, ',', '.'); ?></td>
                                    <td>
                                        <a href="bewerk_opdracht.php?id=<?= $o['id']; ?>" class="btn btn-sm btn-warning">Bewerk</a>
                                        <a href="verwijder_opdracht.php?id=<?= $o['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Verwijderen?');">Verwijder</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if(empty($voldane_opdrachten)): ?>
                                <tr><td colspan="4" class="text-center text-muted">Geen voldane opdrachten.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($_SESSION['user_rol'] !== 'Medewerker'): ?>
        <div class="col-md-4 mt-4 mt-md-0">
            <div class="card shadow-sm p-4 bg-white border">
                <h4>Nieuwe opdracht</h4>
                <hr>
                <form method="POST">
                    <input type="hidden" name="actie_opdracht_toevoegen" value="1">
                    <div class="mb-3"><label class="form-label">Naam</label><input type="text" name="naam" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Startdatum</label><input type="date" name="startdatum" class="form-control" value="<?= date('Y-m-d'); ?>" required></div>
                    <div class="mb-3"><label class="form-label">Uurprijs (€)</label><input type="number" step="0.01" name="uurprijs" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Actief">Actief</option>
                            <option value="Voldaan">Voldaan</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Opslaan</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>