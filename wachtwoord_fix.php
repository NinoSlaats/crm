<?php
require_once 'db.php';

// We genereren de exacte hash die PHP verwacht voor 'Welkom01!'
$nieuw_wachtwoord = password_hash('Welkom01!', PASSWORD_DEFAULT);

try {
    // We updaten alle 3 de medewerkers met dit verse wachtwoord
    $stmt = $conn->prepare("UPDATE medewerkers SET wachtwoord = :wachtwoord");
    $stmt->execute(['wachtwoord' => $nieuw_wachtwoord]);
    
    echo "<h1>Wachtwoorden succesvol gereset!</h1>";
    echo "<p>Je kunt dit bestand nu sluiten en opnieuw inloggen op de inlogpagina.</p>";
} catch(PDOException $e) {
    echo "Fout tijdens resetten: " . $e->getMessage();
}
?>