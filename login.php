<?php
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $wachtwoord = $_POST['wachtwoord'];

    // Zoek de medewerker op basis van e-mail
    $stmt = $conn->prepare("SELECT * FROM medewerkers WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Controleer of de gebruiker bestaat en het wachtwoord klopt
    if ($user && password_verify($wachtwoord, $user['wachtwoord'])) {
        // Sla gegevens op in de sessie
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_naam'] = $user['naam'];
        $_SESSION['user_rol'] = $user['rol'];

        // Stuur door naar de hoofdpagina
        header("Location: index.php");
        exit;
    } else {
        $error = "Onjuist e-mailadres of wachtwoord.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM - Inloggen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">

<div class="card shadow-sm" style="width: 100%; max-width: 400px;">
    <div class="card-body p-4">
        <h3 class="card-title text-center mb-4">Gilde CRM</h3>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="mb-3">
                <label for="email" class="form-label">E-mailadres</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="wachtwoord" class="form-label">Wachtwoord</label>
                <input type="password" name="wachtwoord" id="wachtwoord" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Inloggen</button>
        </form>
    </div>
</div>

</body>
</html>