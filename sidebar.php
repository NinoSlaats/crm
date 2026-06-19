<!-- Hamburger knop - alleen zichtbaar op mobiel -->
<nav class="navbar navbar-dark bg-dark d-md-none px-3 py-2">
    <span class="navbar-brand fw-bold text-primary">Gilde CRM</span>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
        <span class="navbar-toggler-icon"></span>
    </button>
</nav>

<!-- Sidebar -->
<nav id="sidebarMenu" class="offcanvas-md offcanvas-start col-md-3 col-lg-2 bg-dark text-white p-3" tabindex="-1" aria-labelledby="sidebarMenuLabel">
    
    <div class="offcanvas-header d-md-none">
        <h5 class="offcanvas-title text-white" id="sidebarMenuLabel">Menu</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu"></button>
    </div>

    <div class="offcanvas-body d-md-flex flex-column p-0">
        <h4 class="text-center mb-4 text-primary d-none d-md-block">Gilde CRM</h4>
        <p class="small text-muted text-center mb-1">Ingelogd als:</p>
        <p class="text-center"><strong><?= htmlspecialchars($_SESSION['user_naam']); ?></strong></p>
        <span class="badge bg-secondary d-block mb-3"><?= $_SESSION['user_rol']; ?></span>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item mb-2"><a href="index.php" class="nav-link text-white">Dashboard</a></li>
            <li class="nav-item mb-2"><a href="klanten.php" class="nav-link text-white">Klanten & Opdrachten</a></li>
            <li class="nav-item mb-2"><a href="uren_schrijven.php" class="nav-link text-white">Uren Schrijven</a></li>
            <?php if ($_SESSION['user_rol'] !== 'Medewerker'): ?>
                <li class="nav-item mb-2"><a href="rapportages.php" class="nav-link text-white">Rapportages</a></li>
                <li class="nav-item mb-2"><a href="facturen.php" class="nav-link text-white">Facturatie</a></li>
            <?php endif; ?>
            <hr>
            <li class="nav-item"><a href="logout.php" class="nav-link text-danger">Uitloggen</a></li>
        </ul>
    </div>
</nav>