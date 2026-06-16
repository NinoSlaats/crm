<?php
session_start();
require_once 'db.php';

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
    <link rel="stylesheet" href="style.css">
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
                <h1 class="h2">🏠 Dashboard</h1>
            </div>

            <div class="p-5 mb-4 bg-white rounded shadow-sm border">
                <h1 class="display-6 fw-bold text-dark">Welkom terug, <?= htmlspecialchars($_SESSION['user_naam']); ?>!</h1>
                <p class="fs-5 text-muted mt-3">
                    Je bent succesvol ingelogd bij <strong>Gilde Devops Solutions</strong>. 
                    Je rol: <span class="badge bg-primary"><?= $_SESSION['user_rol']; ?></span>.
                </p>
                <hr class="my-4">
                <p>Gebruik het menu aan de linkerkant (of de knop hierboven op mobiel) voor navigatie.</p>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>