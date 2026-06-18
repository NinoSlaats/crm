<?php
session_start();
require_once 'db.php';

// Rol- en loginbeveiliging
if (!isset($_SESSION['user_id']) && !isset($_SESSION['medewerker_id'])) {
    header("Location: login.php");
    exit;
}

$rol = $_SESSION['user_rol'] ?? $_SESSION['rol'] ?? 'Medewerker';
if ($rol === 'Medewerker') {
    header("Location: index.php");
    exit;
}

// 1. Bepaal sorteervolgorde (standaard: hoogste bedrag eerst = desc)
$sort = $_GET['sort'] ?? 'desc'; 
$order_sql = ($sort === 'asc') ? 'ASC' : 'DESC';

// 2. Haal data op met directe berekening in SQL
$stmt = $conn->query("
    SELECT k.id AS klant_id, o.id AS opdracht_id, k.bedrijfsnaam, k.email, o.naam AS opdracht_naam, 
           SUM(w.aantal_uren) AS openstaande_uren, o.uurprijs,
           (SUM(w.aantal_uren) * o.uurprijs) AS totaalbedrag
    FROM werkzaamheden w
    JOIN opdrachten o ON w.opdracht_id = o.id
    JOIN klanten k ON o.klant_id = k.id
    GROUP BY o.id
    ORDER BY totaalbedrag $order_sql
");
$factuur_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// (Hieronder de rest van je logica voor PHPMailer blijft gelijk...)
// [PHPMailer logica staat hier...]
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>CRM - Facturatie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <h1 class="h2 mb-4">Uren Factureren</h1>
            
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
                                <th>
                                    Totaalbedrag 
                                    <a href="?sort=desc" class="text-decoration-none text-dark">▲</a>
                                    <a href="?sort=asc" class="text-decoration-none text-dark">▼</a>
                                </th>
                                <th style="width: 250px;">Actie</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($factuur_data) > 0): ?>
                                <?php foreach ($factuur_data as $f): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($f['bedrijfsnaam']); ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($f['email']); ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($f['opdracht_naam']); ?></td>
                                        <td><?= number_format($f['openstaande_uren'], 2, ',', '.'); ?> uur</td>
                                        <td>€ <?= number_format($f['uurprijs'], 2, ',', '.'); ?></td>
                                        <td><strong>€ <?= number_format($f['totaalbedrag'], 2, ',', '.'); ?></strong></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="genereer_factuur.php?opdracht_id=<?= $f['opdracht_id']; ?>" target="_blank" class="btn btn-sm btn-primary">PDF</a>
                                                <a href="facturen.php?verzend_id=<?= $f['klant_id']; ?>&opdracht_id=<?= $f['opdracht_id']; ?>" class="btn btn-sm btn-success flex-grow-1">Mail Factuur</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted">Geen openstaande uren gevonden.</td></tr>
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