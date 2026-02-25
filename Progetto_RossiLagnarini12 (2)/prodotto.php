<?php
session_start();
require_once "config.php";

$id_prodotto = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM prodotto WHERE id_prodotto = ?");
$stmt->bind_param("i", $id_prodotto);
$stmt->execute();
$prodotto = $stmt->get_result()->fetch_assoc();

if (!$prodotto) die("Prodotto non trovato.");

$is_admin = (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'amministratore');

// --- LOGICA TAGLIE (Eccezione ID 5 e 10) ---
$taglie_disponibili = [];
$nome_minuscolo = strtolower($prodotto['nome']);
$id_cat = (int)$prodotto['id_categoria']; 

if ($id_cat === 5 || $id_cat === 10) {
    // Se è un cappellino (categoria 5 o 10)
    $taglie_disponibili = ['UNICA'];
} else {
    // Default per abbigliamento
    $taglie_disponibili = ['S', 'M', 'L', 'XL'];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title><?php echo $prodotto['nome']; ?> // RILIES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <style>
        /* Size Selector Styling */
        .size-option {
            border: 1px solid #333;
            padding: 12px 20px;
            cursor: pointer;
            font-size: 11px;
            font-family: monospace;
            transition: all 0.2s;
            background: transparent;
            color: #fff;
            min-width: 50px;
            text-align: center;
        }
        .size-option:hover { border-color: #fff; }
        .size-option.selected {
            background: #ff007a;
            border-color: #ff007a;
            color: #fff;
            box-shadow: 0 0 15px rgba(255, 0, 122, 0.3);
        }
    </style>
</head>
<body class="product-page">

<header class="topbar">
    <a href="javascript:history.back()" class="nav-links">← TORNA AL CATALOGO</a>
    <div class="logo">RILIES_SYSTEM</div>
</header>

<main class="product-detail-container">
    <div class="product-media-box">
        <img src="<?php echo $prodotto['immagine']; ?>" class="product-hero-img">
    </div>

    <div class="product-info-box">
        <h1 class="product-main-title"><?php echo strtoupper($prodotto['nome']); ?></h1>
        <p class="product-description"><?php echo $prodotto['descrizione']; ?></p>
        <div class="product-price-tag"><span>€<?php echo number_format($prodotto['prezzo'], 2); ?></span></div>

        <div class="inventory-status">
            <?php if($prodotto['stock'] > 0): ?>
                <span class="stock-available">● DISPONIBILITÀ: <?php echo $prodotto['stock']; ?> PEZZI</span>
            <?php else: ?>
                <span class="stock-out">○ STATO: ESAURITO</span>
            <?php endif; ?>
        </div>

        <div class="size-selector" style="margin: 30px 0;">
            <label style="font-size: 9px; color: #555; display: block; margin-bottom: 12px; letter-spacing: 2px;">SELECT_SIZE // ARCHIVE_FIT</label>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <?php foreach ($taglie_disponibili as $taglia): ?>
                    <div class="size-option" onclick="selectSize(this, '<?php echo $taglia; ?>')">
                        <?php echo $taglia; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <input type="hidden" id="global_size_input" value="">
        </div>

        <div class="product-actions" style="display: flex; gap: 15px; margin-top: 20px;">
            <?php if ($is_admin): ?>
                <a href="rifornimento.php?id=<?php echo $id_prodotto; ?>" class="btn-brutal" 
                   style="width: 100%; text-align: center; background: #ff007a; color: white; text-decoration: none; padding: 20px;">
                    ESEGUI_RIFORNIMENTO
                </a>
            <?php else: ?>
                <form id="form-cart" action="carrello_action.php" method="POST" style="flex: 1;">
                    <input type="hidden" name="id_prodotto" value="<?php echo $id_prodotto; ?>">
                    <input type="hidden" name="taglia" id="cart_size_input">
                    <button type="submit" class="btn-brutal" style="width: 100%; padding: 20px;">AGGIUNGI AL CARRELLO</button>
                </form>

                <form id="form-buy" action="compra_ora.php" method="POST" style="flex: 1;">
                    <input type="hidden" name="id_prodotto" value="<?php echo $id_prodotto; ?>">
                    <input type="hidden" name="taglia" id="buy_size_input">
                    <button type="submit" class="btn-brutal" style="width: 100%; padding: 20px; background: white; color: black;">COMPRA ORA</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>

<section class="reviews-section" style="max-width: 1200px; margin: 60px auto; padding: 0 40px; border-top: 1px solid #1a1a1a; padding-top: 40px;">
    <h2 class="hero-sub">LATEST_VERIFIED_REVIEWS //</h2>
    <div class="reviews-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 30px;">
        <?php
        // Mostra solo le ultime 3 recensioni
        $stmt_rev = $conn->prepare("SELECT r.*, u.nome FROM recensioni r JOIN utente u ON r.id_utente = u.id_utente WHERE r.id_prodotto = ? ORDER BY r.data_recensione DESC LIMIT 3");
        $stmt_rev->bind_param("i", $id_prodotto);
        $stmt_rev->execute();
        $res_rev = $stmt_rev->get_result();

        if($res_rev->num_rows > 0):
            while($rev = $res_rev->fetch_assoc()): ?>
            <div class="review-card" style="background: #000; border: 1px solid #111; padding: 20px;">
                <div style="color: #ff007a; font-size: 10px; margin-bottom: 10px;">
                    <?php echo str_repeat("★", $rev['voto']) . str_repeat("☆", 5 - $rev['voto']); ?>
                </div>
                <p style="font-size: 13px; letter-spacing: 1px; color: #fff;">"<?php echo htmlspecialchars($rev['commento']); ?>"</p>
                <p style="margin-top: 15px; font-size: 9px; color: #444;">
                    IDENTITY: <?php echo strtoupper($rev['nome']); ?> // DATE: <?php echo date('d.m.y', strtotime($rev['data_recensione'])); ?>
                </p>
            </div>
        <?php endwhile; 
        else: ?>
            <p style="font-size: 10px; color: #444;">NO_FEEDBACK_DATA_AVAILABLE_IN_THIS_ARCHIVE</p>
        <?php endif; ?>
    </div>
</section>

<script>
    // AUTO-SELEZIONE PER TAGLIA UNICA
    window.onload = function() {
        const options = document.querySelectorAll('.size-option');
        if (options.length === 1) {
            selectSize(options[0], options[0].innerText.trim());
        }
    };

    function selectSize(element, size) {
        document.querySelectorAll('.size-option').forEach(el => el.classList.remove('selected'));
        element.classList.add('selected');
        
        document.getElementById('global_size_input').value = size;
        document.getElementById('cart_size_input').value = size;
        document.getElementById('buy_size_input').value = size;
    }

    // VALIDAZIONE
    const forms = [document.getElementById('form-cart'), document.getElementById('form-buy')];
    forms.forEach(f => {
        if(f) {
            f.addEventListener('submit', function(e) {
                const selectedSize = document.getElementById('global_size_input').value;
                if(!selectedSize) {
                    e.preventDefault();
                    alert("ERRORE: SELEZIONARE_TAGLIA_PRIMA_DI_PROCEDERE");
                }
            });
        }
    });
</script>

</body>
</html>