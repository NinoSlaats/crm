<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$medewerker_id = $_SESSION['user_id'];
$succes_melding = '';

// --- UREN OPSLAAN (Create) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actie_uren_schrijven'])) {
    $opdracht_id = intval($_POST['opdracht_id']);
    $datum = $_POST['datum'];
    $aantal_uren = floatval($_POST['aantal_uren']);
    $omschrijving = htmlspecialchars($_POST['omschrijving']);

    if ($opdracht_id > 0 && $aantal_uren > 0 && !empty($datum)) {
        $stmt = $conn->prepare("INSERT INTO werkzaamheden (medewerker_id, opdracht_id, datum, aantal_uren, omschrijving) VALUES (:medewerker_id, :opdracht_id, :datum, :aantal_uren, :omschrijving)");
        $stmt->execute([
            'medewerker_id' => $medewerker_id,
            'opdracht_id' => $opdracht_id,
            'datum' => $datum,
            'aantal_uren' => $aantal_uren,
            'omschrijving' => $omschrijving
        ]);
        $succes_melding = "Je uren zijn succesvol geregistreerd!";
    }
}

// --- ACTIEVE OPDRACHTEN OPHALEN ---
$stmt = $conn->query("SELECT id, naam FROM opdrachten WHERE status = 'Actief'");
$actieve_opdrachten = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- RECENT GESCHREVEN UREN OPHALEN ---
$stmt = $conn->prepare("
    SELECT w.*, o.naam AS opdracht_naam 
    FROM werkzaamheden w 
    JOIN opdrachten o ON w.opdracht_id = o.id 
    WHERE w.medewerker_id = :medewerker_id 
    ORDER BY w.datum DESC 
    LIMIT 10
");
$stmt->execute(['medewerker_id' => $medewerker_id]);
$recente_uren = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM - Uren Schrijven</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<?php include 'sidebar.php'; ?>

<main class="px-3 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Uren Registreren</h1>
    </div>

    <?php if (!empty($succes_melding)): ?>
        <div class="alert alert-success shadow-sm"><?= $succes_melding; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12 col-md-5 mb-4">
            <div class="card shadow-sm p-4 bg-white border">
                <h4 class="card-title mb-3">Urenformulier</h4>
                <hr>
                <form method="POST" action="uren_schrijven.php">
                    <input type="hidden" name="actie_uren_schrijven" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label">Kies de Opdracht</label>
                        <select name="opdracht_id" class="form-select" required>
                            <option value="">-- Selecteer een opdracht --</option>
                            <?php foreach ($actieve_opdrachten as $opdracht): ?>
                                <option value="<?= $opdracht['id']; ?>"><?= htmlspecialchars($opdracht['naam']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Datum</label>
                        <input type="date" name="datum" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Aantal uren (bijv. 3.5)</label>
                        <input type="number" step="0.25" min="0.25" max="24" name="aantal_uren" class="form-control" placeholder="0.00" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Wat heb je gedaan? (Omschrijving)</label>
                        <textarea name="omschrijving" class="form-control" rows="3" placeholder="Korte toelichting van de werkzaamheden..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Uren Opslaan</button>
                </form>
            </div>
        </div>

        <div class="col-12 col-md-7">
            <div class="card shadow-sm p-3 bg-white border">
                <h4 class="card-title mb-3">Je recent geschreven uren</h4>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Datum</th>
                                <th>Opdracht</th>
                                <th>Uren</th>
                                <th>Omschrijving</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recente_uren) > 0): ?>
                                <?php foreach ($recente_uren as $uur): ?>
                                    <tr>
                                        <td class="text-nowrap"><?= date('d-m-Y', strtotime($uur['datum'])); ?></td>
                                        <td><strong><?= htmlspecialchars($uur['opdracht_naam']); ?></strong></td>
                                        <td><span class="badge bg-primary"><?= number_format($uur['aantal_uren'], 2, ',', '.'); ?> uur</span></td>
                                        <td class="small text-muted"><?= htmlspecialchars($uur['omschrijving']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">Je hebt nog geen uren geschreven deze periode.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>