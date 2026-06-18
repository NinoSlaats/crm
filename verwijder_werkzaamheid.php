<?php
require_once 'db.php';

// Controleer of er een ID is meegegeven
if (isset($_GET['id'])) {
    // Verwijder uit de juiste tabel (werkzaamheden_personeel)
    // De 'AND medewerker_id = ?' is cruciaal voor de veiligheid!
    $stmt = $conn->prepare("DELETE FROM werkzaamheden_personeel WHERE id = ? AND medewerker_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
}

// Stuur altijd terug naar de index, ook als de verwijdering mislukt
header("Location: index.php");
exit;
?>