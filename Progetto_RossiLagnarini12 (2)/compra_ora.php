<?php
session_start();
require_once 'config.php';

// 1. Controllo Accesso: Se l'utente non è loggato, lo mandiamo al login
if (!isset($_SESSION['id_utente'])) {
    header("Location: login.php");
    exit;
}

// 2. Recupero ID Prodotto dal modulo POST
if (isset($_POST['id_prodotto'])) {
    $id_prodotto = (int)$_POST['id_prodotto'];
    
    // Puliamo eventuali acquisti diretti precedenti per sicurezza
    unset($_SESSION['acquisto_diretto_id']);
    
    // Salviamo l'ID in sessione: questo segnalerà a checkout.php 
    // di ignorare il carrello e mostrare solo questo prodotto.
    $_SESSION['acquisto_diretto_id'] = $id_prodotto;
    
    // Reindirizziamo al checkout in modalità "fast"
    header("Location: checkout.php?mode=fast");
    exit;
} else {
    // Se qualcuno prova ad accedere alla pagina senza aver cliccato "Compra Ora"
    header("Location: index.php");
    exit;
}
?>