<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_utente'])) {
    header("Location: login.php");
    exit;
}

$id_utente = $_SESSION['id_utente'];
$mode = $_GET['mode'] ?? 'cart';
$totale_ordine = 0;

// Recupero dati utente
$user_stmt = $conn->prepare("SELECT * FROM utente WHERE id_utente = ?");
$user_stmt->bind_param("i", $id_utente);
$user_stmt->execute();
$utente = $user_stmt->get_result()->fetch_assoc();

// Recupero prodotti (Singolo da sessione o lista da DB)
if ($mode === 'fast' && isset($_SESSION['acquisto_diretto_id'])) {
    $id_p = $_SESSION['acquisto_diretto_id'];
    $stmt = $conn->prepare("SELECT *, 1 as qty FROM prodotto WHERE id_prodotto = ?");
    $stmt->bind_param("i", $id_p);
} else {
    $stmt = $conn->prepare("SELECT p.*, cp.quantita as qty FROM prodotto p 
                            JOIN carrello_prodotti cp ON p.id_prodotto = cp.id_prodotto 
                            JOIN carrello c ON cp.id_carrello = c.id_carrello 
                            WHERE c.id_utente = ?");
    $stmt->bind_param("i", $id_utente);
}
$stmt->execute();
$prodotti = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>CHECKOUT // RILIES</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .checkout-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; margin-top: 40px; }
        .payment-box { background: #050505; border: 1px solid #1a1a1a; padding: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 10px; color: #444; margin-bottom: 8px; letter-spacing: 1px; }
        .form-group input { width: 100%; background: #000; border: 1px solid #333; color: #fff; padding: 15px; font-family: monospace; outline: none; }
        .form-group input:focus { border-color: #ff007a; }
        
        /* Promo Box */
        .promo-container { margin-top: 25px; padding-top: 20px; border-top: 1px dashed #222; }
        .promo-input-group { display: flex; gap: 10px; }
        .btn-apply { background: #222; color: #fff; border: none; padding: 0 20px; cursor: pointer; font-size: 10px; font-weight: bold; }
        .btn-apply:hover { background: #333; }
        #promo-msg { font-size: 9px; margin-top: 8px; font-family: monospace; min-height: 12px; }

        /* Animazione Spinner */
        #payment-overlay { display: none; text-align: center; padding: 50px 20px; border: 1px solid #ff007a; margin-top: 20px; }
        .spinner { border: 2px solid #111; border-top: 2px solid #ff007a; border-radius: 50%; width: 40px; height: 40px; margin: 0 auto 20px; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="checkout-page">

<header class="topbar">
    <a href="carrello.php" class="nav-links">← BACK_TO_SELECTION</a>
    <div class="logo">RILIES_CHECKOUT</div>
</header>

<main class="container" style="max-width: 1200px; margin: 0 auto; padding: 40px;">
    <h1 class="hero-sub">FINALIZING_ACQUISITION //</h1>

    <div class="checkout-grid">
        <div class="left-col">
            <section class="shipping-info" style="margin-bottom: 40px;">
                <h2 style="font-size: 12px; margin-bottom: 20px;">01. SHIPPING_ADDRESS</h2>
                <p style="font-size: 14px; line-height: 1.6; color: #888;">
                    <?php echo strtoupper($utente['nome'] . " " . $utente['cognome']); ?><br>
                    <?php echo $utente['indirizzo']; ?>, <?php echo $utente['cap']; ?><br>
                    <?php echo $utente['città']; ?> (<?php echo $utente['provincia']; ?>), <?php echo $utente['nazione']; ?>
                </p>
            </section>

            <section class="payment-section">
                <h2 style="font-size: 12px; margin-bottom: 20px;">02. SECURE_PAYMENT_GATEWAY</h2>
                
                <div id="card-form-container" class="payment-box">
                    <div class="form-group">
                        <label>CARD_HOLDER</label>
                        <input type="text" id="card-name" placeholder="NOME COGNOME" required>
                    </div>
                    <div class="form-group">
                        <label>CARD_NUMBER</label>
                        <input type="text" id="card-num" placeholder="0000 0000 0000 0000" maxlength="19">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>EXPIRY_DATE</label>
                            <input type="text" id="card-exp" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div class="form-group">
                            <label>CVC_CODE</label>
                            <input type="text" id="card-cvc" placeholder="***" maxlength="3">
                        </div>
                    </div>
                    <button type="button" id="btn-pay" class="btn-brutal" style="width: 100%; padding: 20px; background: #fff; color: #000; font-weight: bold; cursor: pointer; border: none; margin-top: 10px;">
                        AUTHORIZE_PAYMENT
                    </button>
                </div>

                <div id="payment-overlay">
                    <div class="spinner"></div>
                    <p style="font-size: 10px; letter-spacing: 2px; color: #fff; margin-bottom: 10px;">ENCRYPTING_TRANSACTION...</p>
                    <p id="status-msg" style="font-size: 9px; color: #444; font-family: monospace;">CONNECTING_TO_BANK_SERVER</p>
                </div>
            </section>
        </div>

        <div class="right-col">
            <div class="payment-box">
                <h2 style="font-size: 12px; margin-bottom: 25px;">03. ORDER_SUMMARY</h2>
                <div class="items-list">
                    <?php while($item = $prodotti->fetch_assoc()): 
                        $sub = $item['prezzo'] * $item['qty'];
                        $totale_ordine += $sub;
                    ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid #111; padding-bottom: 15px;">
                        <span style="font-size: 13px; color: #fff;"><?php echo strtoupper($item['nome']); ?> (x<?php echo $item['qty']; ?>)</span>
                        <span style="font-size: 13px;">€<?php echo number_format($sub, 2); ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>

                <div class="promo-container">
                    <div class="form-group">
                        <label>PROMO_CODE</label>
                        <div class="promo-input-group">
                            <input type="text" id="promo-input" placeholder="ENTER_CODE">
                            <button type="button" id="apply-promo" class="btn-apply">APPLY</button>
                        </div>
                        <p id="promo-msg"></p>
                    </div>
                </div>

                <div id="discount-display" style="display: none; justify-content: space-between; margin-bottom: 10px; color: #ff007a; font-size: 12px;">
                    <span>DISCOUNT_APPLIED</span>
                    <span id="discount-amount">-€0.00</span>
                </div>

                <div style="margin-top: 30px; display: flex; justify-content: space-between; align-items: flex-end;">
                    <span style="font-size: 10px; color: #444;">TOTAL_TO_PAY</span>
                    <span id="total-price-display" style="font-size: 28px; color: #ff007a;">€<?php echo number_format($totale_ordine, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
</main>

<form id="hidden-order-form" action="conferma_ordine.php" method="POST">
    <input type="hidden" name="totale_finale" id="final-total-input" value="<?php echo $totale_ordine; ?>">
</form>

<script>
// I codici definiti nel tuo file txt (simulati qui in JS)
const scontiValidi = {
    "RILIES10": 10,
    "SUMMER20": 20,
    "VIP50": 50
};

let totaleIniziale = <?php echo $totale_ordine; ?>;

// Gestione Sconti
document.getElementById('apply-promo').addEventListener('click', function() {
    const code = document.getElementById('promo-input').value.trim().toUpperCase();
    const msg = document.getElementById('promo-msg');
    const displayTotal = document.getElementById('total-price-display');
    const finalInput = document.getElementById('final-total-input');
    const discountRow = document.getElementById('discount-display');
    const discountVal = document.getElementById('discount-amount');

    if (scontiValidi[code]) {
        let percentuale = scontiValidi[code];
        let valoreSconto = (totaleIniziale * percentuale) / 100;
        let totaleScontato = totaleIniziale - valoreSconto;

        // Aggiorna UI
        discountRow.style.display = 'flex';
        discountVal.innerText = "-€" + valoreSconto.toFixed(2);
        displayTotal.innerText = "€" + totaleScontato.toFixed(2);
        
        // Aggiorna input per il database
        finalInput.value = totaleScontato.toFixed(2);

        msg.innerText = "CODE_ACCEPTED // " + percentuale + "% OFF";
        msg.style.color = "#00ff00";
    } else {
        msg.innerText = "INVALID_OR_EXPIRED_CODE";
        msg.style.color = "#ff0000";
        discountRow.style.display = 'none';
        displayTotal.innerText = "€" + totaleIniziale.toFixed(2);
        finalInput.value = totaleIniziale;
    }
});

// Gestione Pagamento (Simulazione)
document.getElementById('btn-pay').addEventListener('click', function() {
    const name = document.getElementById('card-name').value;
    if(name.length < 5) {
        alert("INSERIRE_NOME_VALIDO");
        return;
    }

    const cardForm = document.getElementById('card-form-container');
    const overlay = document.getElementById('payment-overlay');
    const statusMsg = document.getElementById('status-msg');
    const hiddenForm = document.getElementById('hidden-order-form');

    cardForm.style.display = 'none';
    overlay.style.display = 'block';

    setTimeout(() => { statusMsg.innerText = "VERIFYING_CREDIT_LIMITS..."; statusMsg.style.color = "#ff007a"; }, 1500);
    setTimeout(() => { statusMsg.innerText = "AUTHENTICATION_SUCCESSFUL"; }, 3000);
    setTimeout(() => { statusMsg.innerText = "FINALIZING_ORDER_ARCHIVE..."; hiddenForm.submit(); }, 4500);
});

// Auto-format carta
document.getElementById('card-num').addEventListener('input', function (e) {
    e.target.value = e.target.value.replace(/[^\d]/g, '').replace(/(.{4})/g, '$1 ').trim();
});
</script>

</body>
</html>