<div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse min-vh-100 text-white p-3">
    <h4 class="text-center mb-4 text-primary">Gilde CRM</h4>
    <p class="small text-muted text-center mb-1">Ingelogd als:</p>
    <p class="text-center json-text"><strong><?= htmlspecialchars($_SESSION['user_naam']); ?></strong></p>
    <span class="badge bg-secondary d-block mb-3"><?= $_SESSION['user_rol']; ?></span>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item mb-2">
            <a href="index.php" class="nav-link text-white btn-outline-secondary">🏠 Dashboard</a>
        </li>
        <li class="nav-item mb-2">
            <a href="klanten.php" class="nav-link text-white btn-outline-secondary">🏢 Klanten & Opdrachten</a>
        </li>
        <li class="nav-item mb-2">
            <a href="uren_schrijven.php" class="nav-link text-white btn-outline-secondary">✍️ Uren Schrijven</a>
        </li>
        <?php if ($_SESSION['user_rol'] !== 'Medewerker'): ?>
            <li class="nav-item mb-2">
                <a href="rapportages.php" class="nav-link text-white btn-outline-secondary">📊 Rapportages</a>
            </li>
            <li class="nav-item mb-2">
                <a href="facturen.php" class="nav-link text-white btn-outline-secondary">🧾 Facturatie</a>
            </li>
        <?php endif; ?>
        <hr>
        <li class="nav-item mt-4">
            <a href="logout.php" class="nav-link text-danger">➡️ Uitloggen</a>
        </li>
    </ul>
</div>