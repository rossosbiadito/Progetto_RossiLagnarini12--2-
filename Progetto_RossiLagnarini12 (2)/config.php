<?php
// Parametri di connessione
$host = "localhost";
$user = "root";          // Default di XAMPP
$password = "";          // Default di XAMPP (vuota)
$dbname = "db_rilies";   // Il nome del database che hai nel file .sql

// Creazione connessione
$conn = new mysqli($host, $user, $password, $dbname);

// Controllo connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Imposta il set di caratteri per vedere bene l'Euro (€) e gli accenti
$conn->set_charset("utf8mb4");

// Avvia la sessione per gestire carrello e login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>