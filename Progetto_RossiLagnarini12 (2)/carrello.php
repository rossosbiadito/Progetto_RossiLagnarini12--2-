<?php
session_start();
require_once "config.php";

// 1. Controllo Accesso: se non sei loggato, vai al login
if(!isset($_SESSION['id_utente'])){
    header("Location: login.php");
    exit;
}

$id_utente = $_SESSION['id_utente'];

// 2. LOGICA DI RIMOZIONE PRODOTTO
if(isset($_POST['remove_id'])){
    $id_p = (int)$_POST['remove_id'];
    $conn->query("DELETE cp FROM carrello_prodotti cp 
                  JOIN carrello c ON cp.id_carrello = c.id_carrello 
                  WHERE c.id_utente = $id_utente AND cp.id_prodotto = $id_p");
    header("Location: carrello.php");
    exit;
}

// 3. QUERY RECUPERO PRODOTTI RAGGRUPPATI
// Questa query unisce le tabelle per mostrarti nome, immagine e quantità corretta
$stmt = $conn->prepare("
    SELECT p.id_prodotto, p.nome, p.prezzo, p.immagine, cp.quantita
    FROM carrello_prodotti cp
    JOIN prodotto p ON cp.id_prodotto = p.id_prodotto
    JOIN carrello c ON cp.id_carrello = c.id_carrello
    WHERE c.id_utente = ?
");
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$result = $stmt->get_result();

$totale_carrello = 0;
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>RILIES // CARRELLO</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="cart-page">

<header class="topbar">
    <div class="logo">
        <a href="index.php">RILIES<span>.</span></a>
    </div>
    <div class="nav-links">
        <a href="index.php" class="nav-icon-link">TORNA_ALLO_SHOP</a>
    </div>
</header>

<main class="cart-wrapper">
    <h1 class="hero-title">IL_TUO_CARRELLO</h1>

    <?php if($result->num_rows > 0): ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th class="cart-header-prod">PRODOTTO</th>
                    <th class="cart-header-qty">QUANTITÀ</th>
                    <th class="cart-header-price">PREZZO UNITARIO</th>
                    <th class="cart-header-sub">SUBTOTALE</th>
                    <th class="cart-header-actions">AZIONI</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): 
                    $subtotale_prodotto = $row['prezzo'] * $row['quantita'];
                    $totale_carrello += $subtotale_prodotto;
                ?>
                <tr class="cart-item">
                    <td class="cart-product-info">
                        <img src="<?php echo $row['immagine']; ?>" class="cart-img" alt="Product Image">
                        <div class="product-text">
                            <p class="product-name"><?php echo $row['nome']; ?></p>
                            <p class="product-ref">REF_ID: 00<?php echo $row['id_prodotto']; ?></p>
                        </div>
                    </td>

                    <td class="cart-qty">
                        <div class="qty-selector">
                            <form action="update_qty.php" method="POST" class="inline-form">
                                <input type="hidden" name="id_p" value="<?php echo $row['id_prodotto']; ?>">
                                <input type="hidden" name="action" value="decrease">
                                <button type="submit" class="qty-btn">-</button>
                            </form>
                            
                            <span class="qty-number"><?php echo $row['quantita']; ?></span>
                            
                            <form action="update_qty.php" method="POST" class="inline-form">
                                <input type="hidden" name="id_p" value="<?php echo $row['id_prodotto']; ?>">
                                <input type="hidden" name="action" value="increase">
                                <button type="submit" class="qty-btn">+</button>
                            </form>
                        </div>
                    </td>

                    <td class="cart-unit-price">€<?php echo number_format($row['prezzo'], 2); ?></td>
                    <td class="cart-subtotal">€<?php echo number_format($subtotale_prodotto, 2); ?></td>

                    <td class="cart-item-actions">
                        <form action="compra_ora.php" method="POST" class="inline-form">
                            <input type="hidden" name="id_prodotto" value="<?php echo $row['id_prodotto']; ?>">
                            <button type="submit" class="btn-action buy-single" title="Compra solo questo">
                                <i class="fa-solid fa-bolt"></i>
                            </button>
                        </form>

                        <form method="POST" class="inline-form">
                            <input type="hidden" name="remove_id" value="<?php echo $row['id_prodotto']; ?>">
                            <button type="submit" class="btn-action remove-item" title="Rimuovi">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="cart-total-section">
            <div class="total-box">
                <p class="total-label">TOTALE_CARRELLO</p>
                <p class="total-amount">€<?php echo number_format($totale_carrello, 2); ?></p>
            </div>
            
            <div class="cart-actions-bottom">
                <a href="index.php" class="btn-mega secondary">
                    <span class="btn-inner">CONTINUA_SHOPPING</span>
                </a>
                <a href="checkout.php" class="btn-mega">
                    <span class="btn-inner">PAGA_TUTTI_I_PRODOTTI <i class="fa-solid fa-arrow-right"></i></span>
                </a>
            </div>
        </div>

    <?php else: ?>
        <div class="empty-cart-message">
            <p>IL CARRELLO È ATTUALMENTE VUOTO</p>
            <a href="index.php" class="btn-mega"><span class="btn-inner">TORNA ALL'ARCHIVIO</span></a>
        </div>
    <?php endif; ?>
</main>

</body>
</html>