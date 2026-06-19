<?php
session_start();
require_once 'db.php';

// --- PHPMailer laden ---
// Pad: C:\wamp64\www\crm\phpmailer\ (geen src-submap)
require __DIR__ . '/phpmailer/Exception.php';
require __DIR__ . '/phpmailer/PHPMailer.php';
require __DIR__ . '/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

// ============================================
// MAIL FACTUUR VERSTUREN
// ============================================
$verzend_melding = null;

if (isset($_GET['verzend_id']) && isset($_GET['opdracht_id'])) {

    $opdracht_id = intval($_GET['opdracht_id']);

    $stmt = $conn->prepare("
        SELECT o.*, k.bedrijfsnaam, k.contactpersoon, k.email,
               SUM(w.aantal_uren) AS totaal_uren
        FROM opdrachten o
        JOIN klanten k ON o.klant_id = k.id
        LEFT JOIN werkzaamheden w ON w.opdracht_id = o.id
        WHERE o.id = ?
        GROUP BY o.id
    ");
    $stmt->execute([$opdracht_id]);
    $factuur = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$factuur || !$factuur['totaal_uren']) {
        $verzend_melding = ['type' => 'danger', 'tekst' => 'Geen openstaande uren gevonden voor deze opdracht.'];
    } else {
        $totaal_uren   = $factuur['totaal_uren'];
        $tarief        = $factuur['uurprijs'];
        $subtotaal     = $totaal_uren * $tarief;
        $btw           = $subtotaal * 0.21;
        $totaalbedrag  = $subtotaal + $btw;
        $factuurnummer = "FAC-" . date('Y') . "-" . str_pad($opdracht_id, 4, '0', STR_PAD_LEFT);

        $mail = new PHPMailer(true);

        try {
            // --- Server instellingen (Gmail SMTP) ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'Ninoslaats31@gmail.com';  // TODO: vul je Gmail-adres in
            $mail->Password   = 'mtyr scmo osys buhg';           // TODO: vul je app-wachtwoord in (zonder spaties)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // --- Afzender en ontvanger ---
            $mail->setFrom('Ninoslaats31@gmail.com', 'Gilde CRM B.V.');  // TODO: zelfde adres als Username
            $mail->addAddress($factuur['email'], $factuur['bedrijfsnaam']);

            // --- Inhoud ---
            $mail->isHTML(true);
            $mail->Subject = "Factuur {$factuurnummer} - Gilde CRM B.V.";
            $mail->Body = "
                <h2>Factuur {$factuurnummer}</h2>
                <p>Beste " . htmlspecialchars($factuur['contactpersoon']) . ",</p>
                <p>Bijgaand de factuur voor de geleverde diensten t.b.v. project <strong>" . htmlspecialchars($factuur['naam']) . "</strong>.</p>
                <table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse;'>
                    <tr><th>Omschrijving</th><th>Uren</th><th>Tarief</th><th>Bedrag</th></tr>
                    <tr>
                        <td>Geleverde IT-diensten</td>
                        <td>" . number_format($totaal_uren, 2, ',', '.') . " uur</td>
                        <td>€ " . number_format($tarief, 2, ',', '.') . "</td>
                        <td>€ " . number_format($subtotaal, 2, ',', '.') . "</td>
                    </tr>
                </table>
                <p>
                    Subtotaal: € " . number_format($subtotaal, 2, ',', '.') . "<br>
                    BTW (21%): € " . number_format($btw, 2, ',', '.') . "<br>
                    <strong>Totaalbedrag: € " . number_format($totaalbedrag, 2, ',', '.') . "</strong>
                </p>
                <p>Wij verzoeken u vriendelijk het bedrag binnen 14 dagen te voldoen onder vermelding van het factuurnummer.</p>
                <p>Met vriendelijke groet,<br>Gilde CRM B.V.</p>
            ";
            $mail->AltBody = "Factuur {$factuurnummer} - Totaalbedrag: € " . number_format($totaalbedrag, 2, ',', '.');

            $mail->send();
            $verzend_melding = ['type' => 'success', 'tekst' => "Factuur {$factuurnummer} is verstuurd naar {$factuur['email']}."];

        } catch (Exception $e) {
            $verzend_melding = ['type' => 'danger', 'tekst' => "Versturen mislukt: {$mail->ErrorInfo}"];
        }
    }
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
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM - Facturatie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<?php include 'sidebar.php'; ?>

<main class="px-3 px-md-4 py-4">
    <h1 class="h2 mb-4">Uren Factureren</h1>

    <?php if ($verzend_melding): ?>
        <div class="alert alert-<?= $verzend_melding['type']; ?>">
            <?= htmlspecialchars($verzend_melding['tekst']); ?>
        </div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>