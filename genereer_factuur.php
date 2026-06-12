<?php
// Start de sessie altijd als eerste
session_start();

require_once 'db.php';

// Flexibele check: accepteer zowel user_id als medewerker_id zodat je ingelogd blijft
if (!isset($_SESSION['user_id']) && !isset($_SESSION['medewerker_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['opdracht_id']) || empty($_GET['opdracht_id'])) {
    die("Geen opdracht geselecteerd.");
}

$opdracht_id = intval($_GET['opdracht_id']);

// Haal de opdracht, klant en urengegevens op via $conn (PDO verbinding)
$stmt = $conn->prepare("
    SELECT o.*, k.bedrijfsnaam, k.contactpersoon, k.email, k.adres,
           SUM(w.aantal_uren) as totaal_uren
    FROM opdrachten o
    JOIN klanten k ON o.klant_id = k.id
    LEFT JOIN werkzaamheden w ON w.opdracht_id = o.id
    WHERE o.id = ?
    GROUP BY o.id
");
$stmt->execute([$opdracht_id]);
$factuur_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Mocht de query niks vinden of zijn er geen uren, geef een nette melding
if (!$factuur_data || !$factuur_data['totaal_uren']) {
    die("Geen te factureren uren gevonden voor deze opdracht.");
}

$totaal_uren = $factuur_data['totaal_uren'];
$tarief = $factuur_data['uurprijs'];
$subtotaal = $totaal_uren * $tarief;
$btw = $subtotaal * 0.21;
$totaalbedrag = $subtotaal + $btw;
$factuurnummer = "FAC-" . date('Y') . "-" . str_pad($opdracht_id, 4, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factuur <?php echo $factuurnummer; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .factuur-box { max-width: 800px; margin: 30px auto; padding: 40px; border: 1px solid #eee; background-color: #fff; box-shadow: 0 0 15px rgba(0, 0, 0, 0.05); border-radius: 8px; }
        
        /* Zorgt ervoor dat de knoppen onzichtbaar zijn tijdens het printen/opslaan als PDF */
        @media print {
            body { background-color: #fff; }
            .no-print { display: none; }
            .factuur-box { border: none; box-shadow: none; padding: 0; margin: 0; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="container my-4 no-print text-center">
    <button onclick="window.print();" class="btn btn-primary btn-lg me-2">
        <i class="bi bi-file-earmark-pdf"></i> Handmatig Opslaan als PDF
    </button>
    <a href="facturen.php" class="btn btn-outline-secondary btn-lg">
        <i class="bi bi-arrow-left"></i> Terug naar CRM
    </a>
</div>

<div class="factuur-box mb-5">
    <div class="row mb-4">
        <div class="col-6">
            <h1 class="text-primary display-6 fw-bold mb-1">Gilde CRM B.V.</h1>
            <p class="text-muted small">
                Onderwijsboulevard 12<br>
                3500 GE Utrecht<br>
                facturen@gilde.nl
            </p>
        </div>
        <div class="col-6 text-end">
            <h2 class="h3 text-uppercase text-muted fw-light mt-2">Factuur</h2>
            <p class="small">
                <strong>Factuurnummer:</strong> <?php echo $factuurnummer; ?><br>
                <strong>Datum:</strong> <?php echo date('d-m-Y'); ?><br>
                <strong>Vervaldatum:</strong> <?php echo date('d-m-Y', strtotime('+14 days')); ?>
            </p>
        </div>
    </div>

    <hr class="text-muted">

    <div class="row my-4">
        <div class="col-6">
            <h5 class="text-muted text-uppercase small tracking-wider">Gefactureerd aan:</h5>
            <p class="mb-0">
                <strong><?php echo htmlspecialchars($factuur_data['bedrijfsnaam']); ?></strong><br>
                T.a.v. <?php echo htmlspecialchars($factuur_data['contactpersoon']); ?><br>
                <?php echo nl2br(htmlspecialchars($factuur_data['adres'])); ?><br>
                <span class="text-muted small"><?php echo htmlspecialchars($factuur_data['email']); ?></span>
            </p>
        </div>
    </div>

    <table class="table table-striped my-4 align-middle">
        <thead class="table-primary">
            <tr>
                <th>Omschrijving</th>
                <th class="text-center" style="width: 120px;">Uren</th>
                <th class="text-end" style="width: 120px;">Tarief</th>
                <th class="text-end" style="width: 150px;">Bedrag</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Geleverde IT-diensten t.b.v. project: <strong><?php echo htmlspecialchars($factuur_data['naam']); ?></strong></td>
                <td class="text-center"><?php echo number_format($totaal_uren, 2, ',', '.'); ?> uren</td>
                <td class="text-end">€ <?php echo number_format($tarief, 2, ',', '.'); ?></td>
                <td class="text-end">€ <?php echo number_format($subtotaal, 2, ',', '.'); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="row justify-content-end mt-4">
        <div class="col-6">
            <table class="table table-borderless border-top">
                <tr>
                    <th class="text-muted fw-normal">Subtotaal:</th>
                    <td class="text-end">€ <?php echo number_format($subtotaal, 2, ',', '.'); ?></td>
                </tr>
                <tr>
                    <th class="text-muted fw-normal">BTW (21%):</th>
                    <td class="text-end">€ <?php echo number_format($btw, 2, ',', '.'); ?></td>
                </tr>
                <tr class="table-light fs-5 fw-bold border-top">
                    <th class="text-primary">Totaalbedrag:</th>
                    <td class="text-end text-primary">€ <?php echo number_format($totaalbedrag, 2, ',', '.'); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="text-center text-muted small mt-5 pt-4 border-top">
        Wij verzoeken u vriendelijk het bovenstaande totaalbedrag binnen 14 dagen over te maken o.v.v. het factuurnummer. Dit is een automatisch gegenereerd document vanuit Gilde CRM.
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', (event) => {
        // Start automatisch de browser-print functionaliteit (Opslaan als PDF)
        setTimeout(() => {
            window.print();
        }, 500);
    });
</script>

</body>
</html>