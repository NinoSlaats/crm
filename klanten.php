<?php
require_once 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

// --- Create ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actie_klant_toevoegen'])) {
    if ($_SESSION['user_rol'] !== 'Medewerker') {
        $bedrijfsnaam = $_POST['bedrijfsnaam'];
        $contactpersoon = $_POST['contactpersoon'];
        $adres = $_POST['adres'];
        $email = $_POST['email'];

        $stmt = $conn->prepare("INSERT INTO klanten (bedrijfsnaam, contactpersoon, adres, email) VALUES (:bedrijfsnaam, :contactpersoon, :adres, :email)");
        $stmt->execute([
            'bedrijfsnaam' => $bedrijfsnaam,
            'contactpersoon' => $contactpersoon,
            'adres' => $adres,
            'email' => $email
        ]);
        header("Location: klanten.php?succes=1");
        exit;
    }
}

// --- Read ---
$zoekterm = isset($_GET['zoeken']) ? $_GET['zoeken'] : '';
if (!empty($zoekterm)) {
    $stmt = $conn->prepare("SELECT * FROM klanten WHERE bedrijfsnaam LIKE :zoek OR contactpersoon LIKE :zoek");
    $stmt->execute(['zoek' => "%$zoekterm%"]);
} else {
    $stmt = $conn->query("SELECT * FROM klanten");
}
$klanten = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM - Klanten</title>
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
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">🏢 Klantenbeheer</h1>
            </div>

            <?php if(isset($_GET['succes'])): ?>
                <div class="alert alert-success shadow-sm">Klant succesvol toegevoegd!</div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm p-3 mb-4 bg-white border">
                        <form method="GET" action="klanten.php" class="d-flex mb-3">
                            <input type="text" name="zoeken" class="form-control me-2" placeholder="Zoek op bedrijfsnaam..." value="<?= htmlspecialchars($zoekterm); ?>">
                            <button type="submit" class="btn btn-primary">Zoeken</button>
                            <?php if(!empty($zoekterm)): ?>
                                <a href="klanten.php" class="btn btn-secondary ms-2">Reset</a>
                            <?php endif; ?>
                        </form>

                        <table class="table table-striped table-hover mt-2">
                            <thead>
                                <tr>
                                    <th>Bedrijfsnaam</th>
                                    <th>Contactpersoon</th>
                                    <th>E-mail</th>
                                    <th>Actie</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($klanten) > 0): ?>
                                    <?php foreach($klanten as $klant): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($klant['bedrijfsnaam']); ?></strong></td>
                                            <td><?= htmlspecialchars($klant['contactpersoon']); ?></td>
                                            <td><?= htmlspecialchars($klant['email']); ?></td>
                                           <td>
    <a href="klant_details.php?id=<?= $klant['id']; ?>" class="btn btn-sm btn-primary">👁️ Bekijk Opdrachten</a>
    
    <?php if ($_SESSION['user_rol'] !== 'Medewerker'): ?>
        <a href="bewerk_klant.php?id=<?= $klant['id']; ?>" class="btn btn-sm btn-warning">✏️</a>
        <a href="verwijder_klant.php?id=<?= $klant['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Weet je zeker dat je deze klant wilt verwijderen?');">🗑️</a>
    <?php endif; ?>
</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted">Geen klanten gevonden.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-md-4">
                    <?php if ($_SESSION['user_rol'] !== 'Medewerker'): ?>
                        <div class="card shadow-sm p-4 bg-white border">
                            <h4>➕ Nieuwe klant</h4>
                            <hr>
                            <form method="POST" action="klanten.php">
                                <input type="hidden" name="actie_klant_toevoegen" value="1">
                                <div class="mb-3">
                                    <label class="form-label">Bedrijfsnaam</label>
                                    <input type="text" name="bedrijfsnaam" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contactpersoon</label>
                                    <input type="text" name="contactpersoon" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Adres</label>
                                    <input type="text" name="adres" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">E-mailadres</label>
                                    <input type="email" name="email" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-success w-100">Klant Opslaan</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info shadow-sm">Als medewerker kun je klanten inzien. Het aanmaken van klanten is voorbehouden aan Verkoop en Management.</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>