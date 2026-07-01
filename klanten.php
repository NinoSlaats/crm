<?php
require_once 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actie_klant_toevoegen'])) {
    csrf_check();
    if ($_SESSION['user_rol'] !== 'Medewerker') {
        $bedrijfsnaam = trim($_POST['bedrijfsnaam']);
        $email = trim($_POST['email']);

        if ($bedrijfsnaam === '') {
            header("Location: klanten.php?fout=ongeldige_invoer");
            exit;
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: klanten.php?fout=ongeldig_email");
            exit;
        }

        try {
            $stmt = $conn->prepare("INSERT INTO klanten (bedrijfsnaam, contactpersoon, adres, email) VALUES (:bedrijfsnaam, :contactpersoon, :adres, :email)");
            $stmt->execute([
                'bedrijfsnaam' => $bedrijfsnaam,
                'contactpersoon' => trim($_POST['contactpersoon']),
                'adres' => trim($_POST['adres']),
                'email' => $email
            ]);
            header("Location: klanten.php?succes=1");
        } catch (PDOException $e) {
            header("Location: klanten.php?fout=opslaan_mislukt");
        }
        exit;
    }
}

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
    <link rel="stylesheet" href="style.css">
    <style>
        /* Kolommen vaste breedte zodat knoppen altijd passen op desktop */
        @media (min-width: 768px) {
            .col-bedrijf    { width: 20%; }
            .col-contact    { width: 15%; }
            .col-email      { width: 25%; }
            .col-actie      { width: 40%; white-space: nowrap; }
            td.col-bedrijf, td.col-contact, td.col-email {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                max-width: 0;
            }
        }
    </style>
</head>
<body class="bg-light">

<?php include 'sidebar.php'; ?>

<main class="px-3 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Klantenbeheer</h1>
    </div>

    <?php if(isset($_GET['succes'])): ?>
        <div id="succesMelding" class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            Klant succesvol toegevoegd!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Sluiten"></button>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['fout'])):
        $fout_teksten = [
            'ongeldige_invoer' => 'Vul minimaal een bedrijfsnaam in.',
            'ongeldig_email' => 'Vul een geldig e-mailadres in (of laat het veld leeg).',
            'opslaan_mislukt' => 'Opslaan is helaas mislukt. Probeer het opnieuw.',
        ];
        $fout_tekst = $fout_teksten[$_GET['fout']] ?? 'Er is iets misgegaan.';
    ?>
        <div id="foutMelding" class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <?= htmlspecialchars($fout_tekst); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Sluiten"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm p-3 mb-4 bg-white border">
                <div class="d-flex mb-3 gap-2">
                    <input type="text" id="zoekInput" class="form-control" placeholder="Zoek op bedrijfsnaam of contactpersoon..." value="<?= htmlspecialchars($zoekterm); ?>">
                    <button id="resetBtn" class="btn btn-secondary" style="display: <?= !empty($zoekterm) ? 'block' : 'none' ?>;">Reset</button>
                </div>

                <!-- Desktop tabel -->
                <div class="d-none d-md-block">
                    <table class="table table-striped table-hover mt-2 w-100" style="table-layout: fixed;">
                        <thead>
                            <tr>
                                <th class="col-bedrijf">Bedrijfsnaam</th>
                                <th class="col-contact">Contactpersoon</th>
                                <th class="col-email">E-mail</th>
                                <th class="col-actie">Actie</th>
                            </tr>
                        </thead>
                        <tbody id="klantenBody">
                            <?php foreach($klanten as $klant): ?>
                                <tr class="klant-rij"
                                    data-naam="<?= strtolower(htmlspecialchars($klant['bedrijfsnaam'])); ?>"
                                    data-contact="<?= strtolower(htmlspecialchars($klant['contactpersoon'])); ?>">
                                    <td class="col-bedrijf" title="<?= htmlspecialchars($klant['bedrijfsnaam']); ?>">
                                        <strong><?= htmlspecialchars($klant['bedrijfsnaam']); ?></strong>
                                    </td>
                                    <td class="col-contact" title="<?= htmlspecialchars($klant['contactpersoon']); ?>">
                                        <?= htmlspecialchars($klant['contactpersoon']); ?>
                                    </td>
                                    <td class="col-email" title="<?= htmlspecialchars($klant['email']); ?>">
                                        <?= htmlspecialchars($klant['email']); ?>
                                    </td>
                                    <td class="col-actie">
                                        <a href="klant_details.php?id=<?= $klant['id']; ?>" class="btn btn-sm btn-primary">Bekijk</a>
                                        <?php if ($_SESSION['user_rol'] !== 'Medewerker'): ?>
                                            <a href="bewerk_klant.php?id=<?= $klant['id']; ?>" class="btn btn-sm btn-warning">Bewerk</a>
                                            <form method="POST" action="verwijder_klant.php" class="d-inline" onsubmit="return confirm('Weet je zeker dat je deze klant wilt verwijderen?');">
                                                <input type="hidden" name="id" value="<?= $klant['id']; ?>">
                                                <?php csrf_veld(); ?>
                                                <button type="submit" class="btn btn-sm btn-danger">Verwijder</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if(empty($klanten)): ?>
                                <tr><td colspan="4" class="text-center text-muted">Geen klanten gevonden.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <p id="geenResultaten" class="text-muted text-center mt-3" style="display:none;">Geen klanten gevonden.</p>
                </div>

                <!-- Mobiel: kaartjes -->
                <div class="d-md-none" id="klantenKaarten">
                    <?php foreach($klanten as $klant): ?>
                        <div class="card mb-2 border klant-kaart"
                             data-naam="<?= strtolower(htmlspecialchars($klant['bedrijfsnaam'])); ?>"
                             data-contact="<?= strtolower(htmlspecialchars($klant['contactpersoon'])); ?>">
                            <div class="card-body p-3">
                                <h6 class="mb-1"><strong><?= htmlspecialchars($klant['bedrijfsnaam']); ?></strong></h6>
                                <p class="mb-1 small text-muted"><?= htmlspecialchars($klant['contactpersoon']); ?></p>
                                <p class="mb-2 small text-muted"><?= htmlspecialchars($klant['email']); ?></p>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="klant_details.php?id=<?= $klant['id']; ?>" class="btn btn-sm btn-primary">Bekijk</a>
                                    <?php if ($_SESSION['user_rol'] !== 'Medewerker'): ?>
                                        <a href="bewerk_klant.php?id=<?= $klant['id']; ?>" class="btn btn-sm btn-warning">Bewerk</a>
                                        <form method="POST" action="verwijder_klant.php" class="d-inline" onsubmit="return confirm('Verwijderen?');">
                                            <input type="hidden" name="id" value="<?= $klant['id']; ?>">
                                            <?php csrf_veld(); ?>
                                            <button type="submit" class="btn btn-sm btn-danger">Verwijder</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($klanten)): ?>
                        <p class="text-center text-muted">Geen klanten gevonden.</p>
                    <?php endif; ?>
                    <p id="geenResultatenMobiel" class="text-muted text-center mt-3" style="display:none;">Geen klanten gevonden.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <?php if ($_SESSION['user_rol'] !== 'Medewerker'): ?>
                <div class="card shadow-sm p-4 bg-white border">
                    <h4>Nieuwe klant</h4>
                    <hr>
                    <form method="POST" action="klanten.php">
                        <input type="hidden" name="actie_klant_toevoegen" value="1">
                        <?php csrf_veld(); ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const zoekInput = document.getElementById('zoekInput');
    const resetBtn = document.getElementById('resetBtn');
    const rijen = document.querySelectorAll('.klant-rij');
    const kaarten = document.querySelectorAll('.klant-kaart');
    const geenResultaten = document.getElementById('geenResultaten');
    const geenResultatenMobiel = document.getElementById('geenResultatenMobiel');

    zoekInput.addEventListener('input', function () {
        const zoek = this.value.toLowerCase();
        resetBtn.style.display = zoek.length > 0 ? 'block' : 'none';
        let gevonden = 0;

        rijen.forEach(rij => {
            const match = rij.dataset.naam.includes(zoek) || rij.dataset.contact.includes(zoek);
            rij.style.display = match ? '' : 'none';
            if (match) gevonden++;
        });

        kaarten.forEach(kaart => {
            const match = kaart.dataset.naam.includes(zoek) || kaart.dataset.contact.includes(zoek);
            kaart.style.display = match ? '' : 'none';
        });

        geenResultaten.style.display = gevonden === 0 ? 'block' : 'none';
        geenResultatenMobiel.style.display = gevonden === 0 ? 'block' : 'none';
    });

    resetBtn.addEventListener('click', function () {
        zoekInput.value = '';
        rijen.forEach(rij => rij.style.display = '');
        kaarten.forEach(kaart => kaart.style.display = '');
        geenResultaten.style.display = 'none';
        geenResultatenMobiel.style.display = 'none';
        this.style.display = 'none';
    });

    // Meldingen automatisch laten verdwijnen na 4 seconden
    ['succesMelding', 'foutMelding'].forEach(function (elementId) {
        const melding = document.getElementById(elementId);
        if (melding) {
            setTimeout(() => {
                bootstrap.Alert.getOrCreateInstance(melding).close();
            }, 4000);
        }
    });
</script>
</body>
</html>