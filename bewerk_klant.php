<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] === 'Medewerker') { header("Location: login.php"); exit; }

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("UPDATE klanten SET bedrijfsnaam = ?, contactpersoon = ?, adres = ?, email = ? WHERE id = ?");
    $stmt->execute([$_POST['bedrijfsnaam'], $_POST['contactpersoon'], $_POST['adres'], $_POST['email'], $id]);
    header("Location: klanten.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM klanten WHERE id = ?");
$stmt->execute([$id]);
$klant = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$klant) { die("Klant niet gevonden."); }
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klant bewerken</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<?php include 'sidebar.php'; ?>

<main class="px-3 px-md-4 py-4">
    <div class="card shadow-sm p-4 bg-white border" style="max-width: 600px;">
        <h4>Klant bewerken: <?= htmlspecialchars($klant['bedrijfsnaam']) ?></h4>
        <hr>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Bedrijfsnaam</label>
                <input type="text" name="bedrijfsnaam" class="form-control" value="<?= htmlspecialchars($klant['bedrijfsnaam']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Contactpersoon</label>
                <input type="text" name="contactpersoon" class="form-control" value="<?= htmlspecialchars($klant['contactpersoon']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Adres</label>
                <input type="text" name="adres" class="form-control" value="<?= htmlspecialchars($klant['adres']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($klant['email']) ?>">
            </div>
            <button type="submit" class="btn btn-primary">Wijzigingen opslaan</button>
            <a href="klanten.php" class="btn btn-secondary">Annuleren</a>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>