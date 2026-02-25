<?php
session_start();
require_once "config.php";

// 1. Controllo Accesso: impedisce l'aggiunta se l'utente non è loggato
if (!isset($_SESSION['id_utente'])) {
    header("Location: login.php?msg=effettua_il_login");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_prodotto'])) {
    $id_utente = $_SESSION['id_utente'];
    $id_prodotto = (int)$_POST['id_prodotto'];

    // 2. Recupero o creazione del carrello per l'utente
    $res_cart = $conn->query("SELECT id_carrello FROM carrello WHERE id_utente = $id_utente");
    
    if ($res_cart->num_rows > 0) {
        $id_carrello = $res_cart->fetch_assoc()['id_carrello'];
    } else {
        // Se l'utente non ha un carrello attivo, ne creiamo uno nuovo
        $conn->query("INSERT INTO carrello (id_utente, data_creazione) VALUES ($id_utente, NOW())");
        $id_carrello = $conn->insert_id;
    }

    // 3. Inserimento con logica di raggruppamento (Amazon style)
    // Utilizziamo 'ON DUPLICATE KEY UPDATE' per incrementare la quantità se il prodotto esiste già
    $stmt = $conn->prepare("
        INSERT INTO carrello_prodotti (id_carrello, id_prodotto, quantita) 
        VALUES (?, ?, 1) 
        ON DUPLICATE KEY UPDATE quantita = quantita + 1
    ");
    
    $stmt->bind_param("ii", $id_carrello, $id_prodotto);
    
    if ($stmt->execute()) {
        // Una volta aggiunto, rimandiamo l'utente alla visualizzazione del carrello
        header("Location: carrello.php");
        exit;
    } else {
        echo "ERRORE_SISTEMA: " . $conn->error;
    }
}
?>