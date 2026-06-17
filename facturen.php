<?php
// 1. Start de sessie altijd als allereerste
session_start();

// 2. PHPMailer klassen importeren
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

// 3. Databaseverbinding inladen
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

// 4. Haal alle openstaande opdrachten en uren op uit de database via $conn
$stmt = $conn->query("
    SELECT k.id AS klant_id, o.id AS opdracht_id, k.bedrijfsnaam, k.email, o.naam AS opdracht_naam, SUM(w.aantal_uren) AS openstaande_uren, o.uurprijs
    FROM werkzaamheden w
    JOIN opdrachten o ON w.opdracht_id = o.id
    JOIN klanten k ON o.klant_id = k.id
    GROUP BY o.id
");
$factuur_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$verzonden_melding = '';
$fout_melding = '';

// 5. DE ECHTE PHPMAILER LOGICA (Wordt uitgevoerd zodra je op de groene knop drukt)
if (isset($_GET['verzend_id']) && isset($_GET['opdracht_id'])) {
    $verzend_id = intval($_GET['verzend_id']);
    $opdracht_id = intval($_GET['opdracht_id']);
    
    // Haal de specifieke gegevens op van de geselecteerde rij
    $stmt_mail = $conn->prepare("
        SELECT k.bedrijfsnaam, k.email, o.naam AS opdracht_naam, SUM(w.aantal_uren) AS openstaande_uren, o.uurprijs
        FROM opdrachten o
        JOIN klanten k ON o.klant_id = k.id
        LEFT JOIN werkzaamheden w ON w.opdracht_id = o.id
        WHERE o.id = ? AND k.id = ?
        GROUP BY o.id
    ");
    $stmt_mail->execute([$opdracht_id, $verzend_id]);
    $mail_data = $stmt_mail->fetch(PDO::FETCH_ASSOC);

    if ($mail_data) {
        // Berekening voor in de mail (Subtotaal + 21% BTW)
        $subtotaal = $mail_data['openstaande_uren'] * $mail_data['uurprijs'];
        $btw = $subtotaal * 0.21;
        $totaal_bedrag = $subtotaal + $btw;
        
        $mail = new PHPMailer(true);
$mail->CharSet = 'UTF-8';

        try {
            // SMTP Server Instellingen voor Gmail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            
            // JOUW LIVE GEGEVENS INGEBOUWD:
            $mail->Username   = 'ninoslaats31@gmail.com'; 
            $mail->Password   = 'jwkoydkqwcjbfqpw'; 
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Afzender & Ontvanger instellen
            $mail->setFrom('facturen@gilde.nl', 'Gilde CRM Facturatie');
            $mail->addAddress($mail_data['email'], $mail_data['bedrijfsnaam']); 

            // E-mail Content bouwen
            $mail->isHTML(true);
            $mail->Subject = 'Factuur specificatie: ' . $mail_data['opdracht_naam'];
            
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #eee;'>
                    <h2 style='color: #0d6efd; margin-bottom: 5px;'>Gilde CRM B.V.</h2>
                    <p style='font-size: 12px; color: #777; margin-top: 0;'>Digitale urenspecificatie</p>
                    <hr style='border: 0; border-top: 1px solid #eee;'>
                    
                    <p>Beste <strong>" . htmlspecialchars($mail_data['bedrijfsnaam']) . "</strong>,</p>
                    <p>Hierbij ontvangt u de digitale specificatie van de openstaande uren voor het project: <strong>" . htmlspecialchars($mail_data['opdracht_naam']) . "</strong>.</p>
                    
                    <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                        <tr style='background-color: #f8f9fa;'>
                            <th style='padding: 10px; border: 1px solid #ddd; text-align: left;'>Omschrijving</th>
                            <th style='padding: 10px; border: 1px solid #ddd; text-align: center; width: 80px;'>Uren</th>
                            <th style='padding: 10px; border: 1px solid #ddd; text-align: right; width: 90px;'>Tarief</th>
                            <th style='padding: 10px; border: 1px solid #ddd; text-align: right; width: 100px;'>Subtotaal</th>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($mail_data['opdracht_naam']) . "</td>
                            <td style='padding: 10px; border: 1px solid #ddd; text-align: center;'>" . number_format($mail_data['openstaande_uren'], 2, ',', '.') . " u</td>
                            <td style='padding: 10px; border: 1px solid #ddd; text-align: right;'>€ " . number_format($mail_data['uurprijs'], 2, ',', '.') . "</td>
                            <td style='padding: 10px; border: 1px solid #ddd; text-align: right;'>€ " . number_format($subtotaal, 2, ',', '.') . "</td>
                        </tr>
                        <tr>
                            <td colspan='3' style='padding: 10px; text-align: right;'>Subtotaal:</td>
                            <td style='padding: 10px; border: 1px solid #ddd; text-align: right;'>€ " . number_format($subtotaal, 2, ',', '.') . "</td>
                        </tr>
                        <tr>
                            <td colspan='3' style='padding: 10px; text-align: right;'>BTW (21%):</td>
                            <td style='padding: 10px; border: 1px solid #ddd; text-align: right;'>€ " . number_format($btw, 2, ',', '.') . "</td>
                        </tr>
                        <tr style='font-size: 16px; font-weight: bold; background-color: #f1f7ff;'>
                            <td colspan='3' style='padding: 10px; text-align: right; color: #0d6efd;'>Totaalbedrag:</td>
                            <td style='padding: 10px; border: 1px solid #ddd; text-align: right; color: #0d6efd;'>€ " . number_format($totaal_bedrag, 2, ',', '.') . "</td>
                        </tr>
                    </table>
                    
                    <p>Wij verzoeken u vriendelijk dit bedrag binnen 14 dagen aan ons over te maken.</p>
                    <hr style='border: 0; border-top: 1px solid #eee;'>
                    <p style='font-size: 12px; color: #999; text-align: center;'>Met vriendelijke groet,<br><strong>Gilde CRM Team</strong></p>
                </div>
            ";

            $mail->send();
            $verzonden_melding = "Factuur is succesvol verzonden naar " . htmlspecialchars($mail_data['email']) . "!";
        } catch (Exception $e) {
            $fout_melding = "E-mail kon niet verzonden worden. Mailer Foutmelding: {$mail->ErrorInfo}";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM - Facturatie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
            <h1 class="h2 mb-4">🧾 Uren Factureren</h1>

            <?php if (!empty($verzonden_melding)): ?>
                <div class="alert alert-success shadow-sm alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= $verzonden_melding; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($fout_melding)): ?>
                <div class="alert alert-danger shadow-sm alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $fout_melding; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                                <th>Totaalbedrag</th>
                                <th style="width: 250px;">Actie</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($factuur_data) > 0): ?>
                                <?php foreach ($factuur_data as $f): 
                                    $totaal = $f['openstaande_uren'] * $f['uurprijs'];
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($f['bedrijfsnaam']); ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($f['email']); ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($f['opdracht_naam']); ?></td>
                                        <td><?= number_format($f['openstaande_uren'], 2, ',', '.'); ?> uur</td>
                                        <td>€ <?= number_format($f['uurprijs'], 2, ',', '.'); ?></td>
                                        <td><strong>€ <?= number_format($totaal, 2, ',', '.'); ?></strong></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="genereer_factuur.php?opdracht_id=<?= $f['opdracht_id']; ?>" target="_blank" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                                </a>
                                                
                                                <a href="facturen.php?verzend_id=<?= $f['klant_id']; ?>&opdracht_id=<?= $f['opdracht_id']; ?>" class="btn btn-sm btn-success flex-grow-1">
                                                    <i class="bi bi-envelope"></i> Mail Factuur
                                                </a>
                                            </div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>