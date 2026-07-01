<?php
// Dit bestand bevat gevoelige gegevens en hoort NOOIT in git/versiebeheer
// of gedeeld te worden. Zet 'm in .gitignore.

// --- Database ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'crm');

// --- SMTP (mail) ---
// Belangrijk: het app-wachtwoord dat eerder in facturen.php stond, stond
// zichtbaar in de broncode. Maak een NIEUW Gmail app-wachtwoord aan
// (het oude intrekken via https://myaccount.google.com/apppasswords)
// en vul dat hieronder in.
define('SMTP_USERNAME', 'jouw-email@gmail.com');
define('SMTP_PASSWORD', 'nieuw-app-wachtwoord-hier');
define('SMTP_FROM_NAME', 'Gilde CRM B.V.');