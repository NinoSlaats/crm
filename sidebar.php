<?php
// Bepaal huidige pagina voor actieve markering
$huidige_pagina = basename($_SERVER['PHP_SELF']);
?>
<!-- Favicon & laadoverlay: automatisch op elke pagina via sidebar -->
<link rel="icon" type="image/svg+xml" href="favicon.svg">

<!-- Laadoverlay -->
<div id="laadOverlay" style="
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(255,255,255,0.75);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    gap: 12px;
">
    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
    <span class="text-muted fw-semibold">Even geduld...</span>
</div>

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
            <li class="nav-item mb-2">
                <a href="index.php" class="nav-link <?= $huidige_pagina === 'index.php' ? 'active' : 'text-white' ?>">Dashboard</a>
            </li>
            <li class="nav-item mb-2">
                <a href="klanten.php" class="nav-link <?= in_array($huidige_pagina, ['klanten.php','klant_details.php','bewerk_klant.php','bewerk_opdracht.php']) ? 'active' : 'text-white' ?>">Klanten & Opdrachten</a>
            </li>
            <li class="nav-item mb-2">
                <a href="uren_schrijven.php" class="nav-link <?= in_array($huidige_pagina, ['uren_schrijven.php','uren_schrijven_dashboard.php','bewerk_werkzaamheid.php']) ? 'active' : 'text-white' ?>">Uren Schrijven</a>
            </li>
            <?php if ($_SESSION['user_rol'] !== 'Medewerker'): ?>
                <li class="nav-item mb-2">
                    <a href="rapportages.php" class="nav-link <?= $huidige_pagina === 'rapportages.php' ? 'active' : 'text-white' ?>">Rapportages</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="facturen.php" class="nav-link <?= $huidige_pagina === 'facturen.php' ? 'active' : 'text-white' ?>">Facturatie</a>
                </li>
            <?php endif; ?>
        </ul>
        <hr>
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a href="logout.php" class="nav-link text-danger">Uitloggen</a>
            </li>
        </ul>
    </div>
</nav>

<script>
// Laadanimatie op alle formulieren en verwijder-links
document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.getElementById('laadOverlay');

    function toonLaad() {
        overlay.style.display = 'flex';
    }

    // Alle formulieren (behalve zoekformulieren)
    document.querySelectorAll('form').forEach(function (form) {
        // Sla zoekformulieren over (GET forms met zoek-input)
        if (form.method.toLowerCase() === 'get') return;
        form.addEventListener('submit', toonLaad);
    });

    // Verwijder-links: toon overlay alleen als gebruiker bevestigt
    document.querySelectorAll('a.btn-danger').forEach(function (link) {
        const bestaandeOnclick = link.getAttribute('onclick');
        if (bestaandeOnclick && bestaandeOnclick.includes('confirm')) {
            link.removeAttribute('onclick');
            link.addEventListener('click', function (e) {
                e.preventDefault();
                if (confirm('Weet je zeker dat je dit wilt verwijderen?')) {
                    toonLaad();
                    window.location.href = this.href;
                }
            });
        } else {
            link.addEventListener('click', toonLaad);
        }
    });
});
</script>