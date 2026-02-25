<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_utente']) || $_SESSION['ruolo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id_p = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantita'])) {
    $aggiunta = (int)$_POST['quantita'];
    $admin_id = $_SESSION['id_utente'];

    if ($aggiunta > 0) {
        $conn->begin_transaction();
        try {
            $stmt1 = $conn->prepare("UPDATE prodotto SET stock = stock + ? WHERE id_prodotto = ?");
            $stmt1->bind_param("ii", $aggiunta, $id_p);
            $stmt1->execute();
            
            $stmt2 = $conn->prepare("INSERT INTO rifornimenti (quantita_aggiunta, data_rifornimento, id_prodotto, id_utente) VALUES (?, NOW(), ?, ?)");
            $stmt2->bind_param("iii", $aggiunta, $id_p, $admin_id);
            $stmt2->execute();

            $conn->commit();
            
            // Reindirizzamento pulito
            header("Location: prodotto_admin.php?id=$id_p&success=1");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            die("Errore: " . $e->getMessage());
        }
    }
}

$res = $conn->query("SELECT nome, stock FROM prodotto WHERE id_prodotto = $id_p");
$p = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>RIFORNIMENTO // RILIES</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="background:#000; color:#fff; display:flex; align-items:center; justify-content:center; height:100vh; font-family: monospace; margin: 0;">
    
    <a href="index.php" style="position: absolute; top: 20px; right: 20px; color: #fff; text-decoration: none; border: 1px solid #fff; padding: 10px 20px; font-size: 12px; letter-spacing: 2px;">
        HOME [X]
    </a>

    <div style="border:1px solid #ff007a; padding:40px; text-align:center; background:#050505; width: 400px;">
        <h2 style="color:#ff007a;">RIFORNIMENTO_SISTEMA</h2>
        <p style="font-size:18px; margin:20px 0;"><?php echo strtoupper($p['nome']); ?></p>
        
        <form method="POST">
            <input type="number" name="quantita" placeholder="QUANTITÀ" required min="1"
                   style="width:100%; padding:15px; background:#111; border:1px solid #333; color:#fff; font-family:monospace; outline: none;">
            <button type="submit" style="width:100%; margin-top:20px; background:#ff007a; color:#fff; padding:15px; border:none; cursor:pointer; font-weight:bold;">
                CONFERMA_UPDATE
            </button>
        </form>

        <div style="margin-top: 20px;">
            <a href="prodotto_admin.php?id=<?php echo $id_p; ?>" style="color:#555; text-decoration:none; font-size:10px;">
                ← ANNULLA E TORNA ALLA SCHEDA
            </a>
        </div>
    </div>
</body>
</html>