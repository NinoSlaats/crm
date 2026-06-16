<?php
require_once 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] === 'Medewerker') {
    header("Location: index.php");
    exit;
}

// 1. OVERZICHT: JAAROPBRENGST BEREKENEN
$jaar_stmt = $conn->query("
    SELECT SUM(w.aantal_uren * o.uurprijs) AS totale_opbrengst
    FROM werkzaamheden w
    JOIN opdrachten o ON w.opdracht_id = o.id
    WHERE YEAR(w.datum) = YEAR(CURDATE())
");
$jaaropbrengst = $jaar_stmt->fetch(PDO::FETCH_ASSOC)['totale_opbrengst'] ?? 0;

// 2. OVERZICHT: GEWERKTE UREN EN OMZET PER TERMIJN (Maanden)
$termijn_stmt = $conn->query("
    SELECT MONTHNAME(w.datum) AS maand, SUM(w.aantal_uren) AS totale_uren, SUM(w.aantal_uren * o.uurprijs) AS omzet
    FROM werkzaamheden w
    JOIN opdrachten o ON w.opdracht_id = o.id
    WHERE YEAR(w.datum) = YEAR(CURDATE())
    GROUP BY MONTH(w.datum)
    ORDER BY MONTH(w.datum)
");
$maand_rapporten = $termijn_stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. OVERZICHT: TOP OPDRACHTEN OP BASIS VAN OMZET (Rendabel)
$top_stmt = $conn->query("
    SELECT o.naam AS opdracht_naam, k.bedrijfsnaam, SUM(w.aantal_uren) AS uren, SUM(w.aantal_uren * o.uurprijs) AS omzet
    FROM werkzaamheden w
    JOIN opdrachten o ON w.opdracht_id = o.id
    JOIN klanten k ON o.klant_id = k.id
    GROUP BY o.id
    ORDER BY omzet DESC
    LIMIT 5
");
$top_opdrachten = $top_stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. OVERZICHT: GEDETAILLEERDE URENSPECIFICATIE (Wie, wat, wanneer)
$details_stmt = $conn->query("
    SELECT w.datum, w.aantal_uren, w.omschrijving, m.naam AS medewerker_naam, o.naam AS opdracht_naam, k.bedrijfsnaam
    FROM werkzaamheden w
    JOIN medewerkers m ON w.medewerker_id = m.id
    JOIN opdrachten o ON w.opdracht_id = o.id
    JOIN klanten k ON o.klant_id = k.id
    ORDER BY w.datum DESC
");
$gedetailleerde_uren = $details_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM - Rapportages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="d-md-none p-3 bg-dark text-white">
    <button class="btn btn-outline-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
        ☰ Menu
    </button>
</div>
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <h1 class="h2 mb-4">📊 Management Rapportages</h1>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white shadow-sm p-4">
                        <h5 class="card-title text-uppercase small">Jaaropbrengst (<?= date('Y'); ?>)</h5>
                        <h2 class="fw-bold">€ <?= number_format($jaaropbrengst, 2, ',', '.'); ?></h2>
                        <p class="mb-0 text-white-50">Gefactureerde en openstaande uren</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm p-4 bg-white border mb-4">
                <h4 class="mb-3">📅 Gewerkte uren en omzet per termijn (Maanden)</h4>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Termijn (Maand)</th>
                                <th>Totaal Gewerkte Uren</th>
                                <th>Gegenereerde Omzet</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($maand_rapporten) > 0): ?>
                                <?php foreach($maand_rapporten as $rapport): ?>
                                    <tr>
                                        <td><strong><?= $rapport['maand']; ?></strong></td>
                                        <td><?= number_format($rapport['totale_uren'], 2, ',', '.'); ?> uur</td>
                                        <td>€ <?= number_format($rapport['omzet'], 2, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted py-3">Nog geen uren geschreven in dit jaar.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm p-4 bg-white border mb-4">
                <h4 class="mb-3">🏆 Meest Rendabele Opdrachten (Top 5)</h4>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Opdracht</th>
                                <th>Klant</th>
                                <th>Totaal Uren</th>
                                <th>Totale Omzet</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($top_opdrachten) > 0): ?>
                                <?php foreach($top_opdrachten as $top): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($top['opdracht_naam']); ?></strong></td>
                                        <td><?= htmlspecialchars($top['bedrijfsnaam']); ?></td>
                                        <td><?= number_format($top['uren'], 2, ',', '.'); ?> uur</td>
                                        <td><strong>€ <?= number_format($top['omzet'], 2, ',', '.'); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">Nog geen opdrachten met uren gevonden.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm p-4 bg-white border">
                <h4 class="mb-3">📋 Gedetailleerde Urenspecificatie</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Datum</th>
                                <th>Medewerker</th>
                                <th>Klant & Opdracht</th>
                                <th>Aantal uren</th>
                                <th>Omschrijving werkzaamheden</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($gedetailleerde_uren) > 0): ?>
                                <?php foreach($gedetailleerde_uren as $uur): ?>
                                    <tr>
                                        <td class="text-nowrap"><?= date('d-m-Y', strtotime($uur['datum'])); ?></td>
                                        <td><strong><?= htmlspecialchars($uur['medewerker_naam']); ?></strong></td>
                                        <td>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($uur['bedrijfsnaam']); ?></span><br>
                                            <small class="text-muted"><?= htmlspecialchars($uur['opdracht_naam']); ?></small>
                                        </td>
                                        <td><span class="badge bg-success"><?= number_format($uur['aantal_uren'], 2, ',', '.'); ?> uur</span></td>
                                        <td><?= htmlspecialchars($uur['omschrijving']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted py-3">Er zijn nog geen specifieke urenregistraties gevonden.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>