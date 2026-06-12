<?php
require_once 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] === 'Medewerker') {
    header("Location: index.php");
    exit;
}

// Haal alle klanten op die uren hebben staan die we kunnen factureren
$stmt = $conn->query("
    SELECT k.id AS klant_id, k.bedrijfsnaam, k.email, o.naam AS opdracht_naam, SUM(w.aantal_uren) AS openstaande_uren, o.uurprijs
    FROM werkzaamheden w
    JOIN opdrachten o ON w.opdracht_id = o.id
    JOIN klanten k ON o.klant_id = k.id
    GROUP BY o.id
");
$factuur_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$verzonden_melding = '';
if (isset($_GET['verzend_id'])) {
    $verzonden_melding = "Factuur succesvol digitaal gegenereerd en verzonden naar de klant!";
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM - Facturatie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <h1 class="h2 mb-4">🧾 Uren Factureren</h1>

            <?php if (!empty($verzonden_melding)): ?>
                <div class="alert alert-success shadow-sm"><?= $verzonden_melding; ?></div>
            <?php endif; ?>

            <div class="card shadow-sm p-4 bg-white border">
                <h4 class="mb-3">Te factureren opdrachten</h4>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Klant</th>
                                <th>Opdracht</th>
                                <th>Uren Openstaand</th>
                                <th>Tarief</th>
                                <th>Totaalbedrag</th>
                                <th>Actie</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($factuur_data) > 0): ?>
                                <?php foreach ($factuur_data as $f): 
                                    $totaal = $f['openstaande_uren'] * $f['uurprijs'];
                                ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($f['bedrijfsnaam']); ?></strong><br><small class="text-muted"><?= $f['email']; ?></small></td>
                                        <td><?= htmlspecialchars($f['opdracht_name'] ?? $f['opdracht_naam']); ?></td>
                                        <td><?= number_format($f['openstaande_uren'], 2, ',', '.'); ?> uur</td>
                                        <td>€ <?= number_format($f['uurprijs'], 2, ',', '.'); ?></td>
                                        <td><strong>€ <?= number_format($totaal, 2, ',', '.'); ?></strong></td>
                                        <td>
                                            <a href="facturen.php?verzend_id=<?= $f['klant_id']; ?>" class="btn btn-sm btn-success">⚙️ Genereer & Mail Factuur</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted">Geen openstaande uren gevonden om te factureren.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html>