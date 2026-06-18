<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// AANGEPAST: We halen gegevens nu uit 'werkzaamheden_personeel'
// Er is geen 'JOIN opdrachten' meer nodig, dus we selecteren gewoon alles
$stmt = $conn->prepare("
    SELECT * FROM werkzaamheden_personeel 
    WHERE medewerker_id = :user_id
    ORDER BY datum DESC
    LIMIT 5
");
$stmt->execute(['user_id' => $_SESSION['user_id']]); 
$mijn_werkzaamheden = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gilde CRM - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">🏠 Dashboard</h1>
            </div>

            <div class="p-5 mb-4 bg-white rounded shadow-sm border">
                <h1 class="display-6 fw-bold text-dark">Welkom terug, <?= htmlspecialchars($_SESSION['user_naam']); ?>!</h1>
                <p class="fs-5 text-muted mt-3">
                    Je rol: <span class="badge bg-primary"><?= $_SESSION['user_rol']; ?></span>.
                </p>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow-sm p-4 bg-white border">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">⚡ Mijn meest recente werkzaamheden</h4>
                            <a href="uren_schrijven_dashboard.php" class="btn btn-success btn-sm">+ Nieuwe werkzaamheid</a>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Datum</th> <th>Uren</th>
                                        <th>Omschrijving</th>
                                        <th>Actie</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($mijn_werkzaamheden) > 0): ?>
                                        <?php foreach($mijn_werkzaamheden as $w): ?>
                                            <tr>
                                                <td><?= date('d-m-Y', strtotime($w['datum'])); ?></td>
                                                <td><span class="badge bg-info text-dark"><?= $w['aantal_uren']; ?> uur</span></td>
                                                <td><?= htmlspecialchars($w['omschrijving']); ?></td>
                                                <td>
                                                    <a href="bewerk_werkzaamheid.php?id=<?= $w['id']; ?>" class="btn btn-sm btn-warning">✎</a>
                                                    <a href="verwijder_werkzaamheid.php?id=<?= $w['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Verwijderen?');">🗑</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-muted">Nog geen werkzaamheden geregistreerd.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>