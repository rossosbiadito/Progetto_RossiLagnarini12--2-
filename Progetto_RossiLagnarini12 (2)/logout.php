<?php
session_start();
session_destroy(); // Distrugge tutti i dati dell'utente attuale
header("Location: login.php"); // Ti riporta al login pulito
exit();
?>