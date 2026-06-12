<?php
require_once 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$klant_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Haal klantgegevens op
$stmt = $conn->prepare("SELECT * FROM klanten WHERE id = :id");
$stmt->execute(['id' => $klant_id]);
$klant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$klant) {
    die("Klant niet gevonden.");
}

// --- OPDRACHT TOEVOEGEN (Create) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actie_opdracht_toevoegen'])) {
    if ($_SESSION['user_rol'] !== 'Medewerker') {
        $naam = $_POST['naam'];
        $startdatum = $_POST['startdatum'];
        $einddatum = !empty($_POST['einddatum']) ? $_POST['einddatum'] : null;
        $uurprijs = $_POST['uurprijs'];
        $status = $_POST['status'];

        $stmt = $conn->prepare("INSERT INTO opdrachten (klant_id, naam, startdatum, einddatum, status, uurprijs) VALUES (:klant_id, :naam, :startdatum, :einddatum, :status, :uurprijs)");
        $stmt->execute([
            'klant_id' => $klant_id,
            'naam' => $naam,
            'startdatum' => $startdatum,
            'einddatum' => $einddatum,
            'status' => $status,
            'uurprijs' => $uurprijs
        ]);
        header("Location: klant_details.php?id=" . $klant_id . "&succes=1");
        exit;
    }
}

// --- OPDRACHTEN OPHALEN (Read) ---
// Actieve opdrachten
$stmt = $conn->prepare("SELECT * FROM opdrachten WHERE klant_id = :id AND status = 'Actief'");
$stmt->execute(['id' => $klant_id]);
$actieve_opdrachten = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Voldane opdrachten
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
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <a href="klanten.php" class="btn btn-sm btn-outline-secondary mb-3">⬅️ Terug naar klanten</a>
            
            <h1 class="h2"><?= htmlspecialchars($klant['bedrijfsnaam']); ?></h1>
            <p class="text-muted">Contactpersoon: <?= htmlspecialchars($klant['contactpersoon']); ?> | Adres: <?= htmlspecialchars($klant['adres']); ?></p>

            <?php if(isset($_GET['succes'])): ?>
                <div class="alert alert-success">Opdracht succesvol toegevoegd!</div>
            <?php endif; ?>

            <div class="row mt-4">
                <div class="col-md-8">
                    
                    <div class="card shadow-sm p-3 mb-4 bg-white border">
                        <h4 class="text-primary mb-3">⚡ Actieve Opdrachten</h4>
                        <table class="table table-striped">
                            <thead>
                                <tr><th>Opdrachtnaam</th><th>Startdatum</th><th>Uurprijs</th></tr>
                            </thead>
                            <tbody>
                                <?php if(count($actieve_opdrachten) > 0): ?>
                                    <?php foreach($actieve_opdrachten as $opdracht): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($opdracht['naam']); ?></strong></td>
                                            <td><?= date('d-m-Y', strtotime($opdracht['startdatum'])); ?></td>
                                            <td>€ <?= number_format($opdracht['uurprijs'], 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-muted">Geen actieve opdrachten voor deze klant.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="card shadow-sm p-3 bg-white border">
                        <h4 class="text-success mb-3">✅ Voldane Opdrachten</h4>
                        <table class="table table-striped">
                            <thead>
                                <tr><th>Opdrachtnaam</th><th>Einddatum</th><th>Uurprijs</th></tr>
                            </thead>
                            <tbody>
                                <?php if(count($voldane_opdrachten) > 0): ?>
                                    <?php foreach($voldane_opdrachten as $opdracht): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($opdracht['naam']); ?></strong></td>
                                            <td><?= date('d-m-Y', strtotime($opdracht['einddatum'])); ?></td>
                                            <td>€ <?= number_format($opdracht['uurprijs'], 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-muted">Geen afgeronde opdrachten voor deze klant.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>

                <div class="col-md-4">
                    <?php if ($_SESSION['user_rol'] !== 'Medewerker'): ?>
                        <div class="card shadow-sm p-4 bg-white border">
                            <h4>➕ Nieuwe opdracht</h4>
                            <hr>
                            <form method="POST" action="klant_details.php?id=<?= $klant_id; ?>">
                                <input type="hidden" name="actie_opdracht_toevoegen" value="1">
                                <div class="mb-3">
                                    <label class="form-label">Naam van de opdracht</label>
                                    <input type="text" name="naam" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Startdatum</label>
                                    <input type="date" name="startdatum" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Einddatum (optioneel)</label>
                                    <input type="date" name="einddatum" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Uurprijs (€)</label>
                                    <input type="number" step="0.01" name="uurprijs" class="form-control" placeholder="0.00" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="Actief">Actief</option>
                                        <option value="Voldaan">Voldaan</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Opdracht Opslaan</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info shadow-sm">Als medewerker kun je opdrachten inzien. Alleen Verkoopmedewerkers en Afdelingshoofden mogen nieuwe opdrachten aanmaken en de uurprijs bepalen.</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html>