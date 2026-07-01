<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT * FROM werkzaamheden 
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

<?php include 'sidebar.php'; ?>

<main class="px-3 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dashboard</h1>
    </div>

    <?php if (isset($_GET['succes'])):
        $succes_teksten = [
            'werkzaamheid_toegevoegd' => 'Werkzaamheid succesvol toegevoegd.',
            'aangepast' => 'Werkzaamheid succesvol aangepast.',
            'werkzaamheid_verwijderd' => 'Werkzaamheid succesvol verwijderd.',
        ];
        $succes_tekst = $succes_teksten[$_GET['succes']] ?? null;
    ?>
        <?php if ($succes_tekst): ?>
        <div id="succesMelding" class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <?= htmlspecialchars($succes_tekst); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Sluiten"></button>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="p-4 mb-4 bg-white rounded shadow-sm border">
        <h1 class="display-6 fw-bold text-dark">Welkom terug, <?= htmlspecialchars($_SESSION['user_naam']); ?>!</h1>
        <p class="fs-5 text-muted mt-3">
            Je rol: <span class="badge bg-primary"><?= $_SESSION['user_rol']; ?></span>
        </p>
    </div>

    <div class="card shadow-sm p-4 bg-white border">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h4 class="mb-0">Mijn meest recente werkzaamheden</h4>
            <a href="uren_schrijven_dashboard.php" class="btn btn-success btn-sm">+ Nieuwe werkzaamheid</a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Uren</th>
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
                                    <a href="bewerk_werkzaamheid.php?id=<?= $w['id']; ?>&terug=index.php" class="btn btn-sm btn-warning">Bewerk</a>
                                    <form method="POST" action="verwijder_werkzaamheid.php" class="d-inline" onsubmit="return confirm('Verwijderen?');">
                                        <input type="hidden" name="id" value="<?= $w['id']; ?>">
                                        <input type="hidden" name="terug" value="index.php">
                                        <?php csrf_veld(); ?>
                                        <button type="submit" class="btn btn-sm btn-danger">Verwijder</button>
                                    </form>
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
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const succesMelding = document.getElementById('succesMelding');
    if (succesMelding) {
        setTimeout(() => {
            bootstrap.Alert.getOrCreateInstance(succesMelding).close();
        }, 4000);
    }
</script>
</body>
</html>