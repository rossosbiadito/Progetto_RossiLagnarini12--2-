<?php
session_start();
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_p = (int)$_POST['id_p'];
    $id_u = $_SESSION['id_utente'];
    $action = $_POST['action'];

    if ($action == 'increase') {
        $sql = "UPDATE carrello_prodotti cp 
                JOIN carrello c ON cp.id_carrello = c.id_carrello 
                SET cp.quantita = cp.quantita + 1 
                WHERE c.id_utente = $id_u AND cp.id_prodotto = $id_p";
    } else {
        $sql = "UPDATE carrello_prodotti cp 
                JOIN carrello c ON cp.id_carrello = c.id_carrello 
                SET cp.quantita = cp.quantita - 1 
                WHERE c.id_utente = $id_u AND cp.id_prodotto = $id_p AND cp.quantita > 1";
    }
    
    $conn->query($sql);
    header("Location: carrello.php");
}