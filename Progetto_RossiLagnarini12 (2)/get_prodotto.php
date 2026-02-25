<?php
include "config.php";

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM prodotto WHERE id_prodotto = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
echo json_encode($row);
?>