<?php
session_start();
require_once "config.php";

if($_SESSION['ruolo'] != "admin"){
    header("Location: index.php");
    exit;
}

// Aggiunta stock
if(isset($_POST['id_prodotto'])){
    $stmt = $conn->prepare("
        UPDATE prodotto
        SET stock = stock + ?
        WHERE id_prodotto = ?
    ");
    $stmt->bind_param("ii", $_POST['quantita'], $_POST['id_prodotto']);
    $stmt->execute();
}

$prodotti = $conn->query("SELECT * FROM prodotto");
?>

<h1>Rifornimenti</h1>

<?php while($p = $prodotti->fetch_assoc()): ?>
<form method="POST">
    <p><?php echo $p['nome']; ?> (Stock: <?php echo $p['stock']; ?>)</p>
    <input type="hidden" name="id_prodotto" value="<?php echo $p['id_prodotto']; ?>">
    <input type="number" name="quantita" placeholder="Quantità">
    <button type="submit">Rifornisci</button>
</form>
<?php endwhile; ?>
