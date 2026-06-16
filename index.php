<?php
require_once 'db.php';

// Als de gebruiker NIET is ingelogd, stuur hem dan direct naar login.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gilde CRM - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                <div class="container-fluid py-2">
                    <h1 class="display-6 fw-bold text-dark">Welkom terug, <?= htmlspecialchars($_SESSION['user_naam']); ?>!</h1>
                    <p class="col-md-10 fs-5 text-muted mt-3">
                        Je bent succesvol ingelogd binnen het CRM-systeem van <strong>Gilde Devops Solutions</strong>. 
                        Je hebt momenteel de rechten van een: <span class="badge bg-primary"><?= $_SESSION['user_rol']; ?></span>.
                    </p>
                    <hr class="my-4">
                    <p>Gebruik het menu aan de linkerkant om navigeren naar het klantenbeheer of om direct je gewerkte uren te registreren.</p>
                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html> // http://localhost/crm/index.php