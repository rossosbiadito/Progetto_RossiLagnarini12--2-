<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['id_utente'])) {
    header("Location: login.php");
    exit();
}

$id_utente = $_SESSION['id_utente'];

$stmt = $conn->prepare("SELECT * FROM utente WHERE id_utente = ?");
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$utente = $stmt->get_result()->fetch_assoc();

function getTrackingStatus($data_ordine) {
    $secondi_passati = time() - strtotime($data_ordine);
    $ore_passate = $secondi_passati / 3600;

    // SOGLIE TEMPORALI RILIES
    if ($ore_passate < 1) {
        return ['msg' => "ORDER_RECEIVED: Sistema in carico.", 'prog' => 15, 'can_review' => false];
    } elseif ($ore_passate < 12) {
        return ['msg' => "PREPARING_SHIPMENT: Controllo qualità capi.", 'prog' => 40, 'can_review' => false];
    } elseif ($ore_passate < 24) {
        return ['msg' => "IN_TRANSIT: In viaggio verso l'hub locale.", 'prog' => 70, 'can_review' => false];
    } elseif ($ore_passate < 48) {
        return ['msg' => "OUT_FOR_DELIVERY: In consegna oggi.", 'prog' => 90, 'can_review' => false];
    } else {
        // DOPO 48 ORE IL PACCO È CONSEGNATO
        return ['msg' => "DELIVERED: Unità consegnata correttamente.", 'prog' => 100, 'can_review' => true];
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>ACCOUNT // RILIES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body class="account-page" style="background-color: #000; color: #fff; font-family: monospace;">

<header class="topbar" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 40px; border-bottom: 1px solid #1a1a1a;">
    <a href="index.php" class="nav-links" style="color: #fff; text-decoration: none; font-size: 10px; letter-spacing: 2px;">← TORNA ALL'ARCHIVIO</a>
    <div class="logo" style="letter-spacing: 5px;">RILIES_IDENTITY_CARD</div>
    <a href="logout.php" class="nav-links" style="color: #ff007a; text-decoration: none; font-size: 10px; letter-spacing: 2px;">LOGOUT_TERMINATE</a>
</header>

<main class="account-container" style="max-width: 1200px; margin: 40px auto; padding: 0 40px; display: grid; grid-template-columns: 1fr 2fr; gap: 60px;">
    
    <section class="profile-info">
        <h2 style="font-size: 12px; letter-spacing: 3px; color: #444; margin-bottom: 30px;">USER_DATA //</h2>
        <div style="border: 1px solid #1a1a1a; padding: 30px; background: #050505;">
            <p style="font-size: 9px; color: #444; margin-bottom: 5px;">IDENTITY_NAME</p>
            <p style="font-size: 18px; letter-spacing: 2px; margin-bottom: 20px; color: #fff;"><?php echo strtoupper($utente['nome'] . " " . ($utente['cognome'] ?? '')); ?></p>
            <p style="font-size: 9px; color: #444; margin-bottom: 5px;">EMAIL_ADDRESS</p>
            <p style="font-size: 13px; color: #eee;"><?php echo $utente['email']; ?></p>
        </div>
    </section>

    <section class="orders-history">
        <h2 style="font-size: 12px; letter-spacing: 3px; color: #444; margin-bottom: 30px;">ORDER_ARCHIVE //</h2>
        <?php
        $stmt_ordini = $conn->prepare("SELECT * FROM ordine WHERE id_utente = ? ORDER BY data_ordine DESC");
        $stmt_ordini->bind_param("i", $id_utente);
        $stmt_ordini->execute();
        $ordini = $stmt_ordini->get_result();

        while ($ordine = $ordini->fetch_assoc()): 
            $tracking = getTrackingStatus($ordine['data_ordine']);
        ?>
            <div class="order-card" style="border: 1px solid #1a1a1a; padding: 25px; margin-bottom: 30px; background: #0a0a0a;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                    <span style="font-size: 9px; color: #444;">ID_ORDINE: #<?php echo $ordine['id_ordine']; ?></span>
                    <span style="font-size: 9px; color: #ff007a;">DATA: <?php echo date('d.m.Y', strtotime($ordine['data_ordine'])); ?></span>
                </div>

                <div class="tracking-box" style="background: #000; border: 1px solid #1a1a1a; padding: 15px; margin-bottom: 25px;">
                    <p style="font-size: 11px; color: #fff; margin: 0;">> <?php echo $tracking['msg']; ?></p>
                    <div style="width: 100%; height: 2px; background: #111; margin-top: 10px;">
                        <div style="height: 100%; background: #ff007a; width: <?php echo $tracking['prog']; ?>%;"></div>
                    </div>
                </div>

                <div class="items-list">
                    <?php
                    $stmt_items = $conn->prepare("SELECT p.id_prodotto, p.nome, p.immagine FROM dettaglio_ordine do JOIN prodotto p ON do.id_prodotto = p.id_prodotto WHERE do.id_ordine = ?");
                    $stmt_items->bind_param("i", $ordine['id_ordine']);
                    $stmt_items->execute();
                    $items = $stmt_items->get_result();
                    while($item = $items->fetch_assoc()):
                    ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #151515;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <img src="<?php echo $item['immagine']; ?>" style="width: 50px; height: 50px; object-fit: cover; border: 1px solid #222;">
                            <span style="font-size: 11px; letter-spacing: 1px; color: #fff;"><?php echo strtoupper($item['nome']); ?></span>
                        </div>
                        
                        <?php if($tracking['can_review']): ?>
                            <a href="recensione.php?id=<?php echo $item['id_prodotto']; ?>" 
                               style="font-size: 9px; color: #fff; text-decoration: none; border: 1px solid #ff007a; background: #ff007a; padding: 8px 15px; letter-spacing: 1px; font-weight: bold;">
                               RECENSISCI
                            </a>
                        <?php else: ?>
                            <span style="font-size: 8px; color: #333; letter-spacing: 1px; border: 1px solid #222; padding: 5px 10px;">AWAITING_DELIVERY</span>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <span style="font-size: 14px; color: #fff; font-weight: bold;">TOTALE: €<?php echo number_format($ordine['totale'], 2); ?></span>
                </div>
            </div>
        <?php endwhile; ?>
    </section>
</main>

</body>
</html>