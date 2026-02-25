<?php
session_start();
require_once "config.php";

// Controlliamo che l'utente sia loggato e che i dati siano stati inviati
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['id_utente'])) {
    
    // Recuperiamo i dati dal form
    $id_p = (int)$_POST['id_prodotto'];
    $id_u = (int)$_SESSION['id_utente'];
    $voto = (int)$_POST['voto'];
    $commento = htmlspecialchars($_POST['commento']); // Protezione contro hacker (XSS)

    // Prepariamo la query usando i tuoi nomi di colonna esatti:
    // voto, commento, id_utente, id_prodotto
    $sql = "INSERT INTO recensioni (voto, commento, id_utente, id_prodotto) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isii", $voto, $commento, $id_u, $id_p);
    
    if ($stmt->execute()) {
        // Se tutto va bene, torna alla pagina del prodotto
        header("Location: prodotto.php?id=" . $id_p . "&msg=success");
        exit();
    } else {
        // Se c'è un errore nel database, scrivilo a schermo
        echo "ERRORE_SISTEMA: " . $conn->error;
    }
} else {
    // Se qualcuno prova ad accedere al file direttamente senza form
    header("Location: index.php");
    exit();
}
?>