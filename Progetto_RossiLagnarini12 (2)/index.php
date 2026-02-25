<?php 
session_start();
require_once 'config.php'; // Inizializza la connessione $conn

// 1. Controllo se l'utente è un amministratore
$is_admin = (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin');

// 2. LOGICA CONTATORE CARRELLO (Sincronizzata con il tuo database)
$totale_articoli = 0;
if (!$is_admin && isset($_SESSION['id_utente'])) {
    $id_utente = $_SESSION['id_utente'];
    
    // Query MySQLi per sommare le quantità nel carrello
    $query_cart = "SELECT SUM(cp.quantita) as totale 
                   FROM carrello_prodotti cp 
                   JOIN carrello c ON cp.id_carrello = c.id_carrello 
                   WHERE c.id_utente = ?";
    
    if ($stmt = $conn->prepare($query_cart)) {
        $stmt->bind_param("i", $id_utente);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $totale_articoli = $row['totale'] ?? 0;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>RILIES // PRIVATE ARCHIVE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body class="loading">

<header class="topbar">
    <div class="nav-icon-link" onclick="openNav()" style="cursor:pointer;">
        <i class="fa-solid fa-bars"></i> MENU
    </div>
    <div class="logo">
        <a href="index.php">RILIES<span>.</span></a>
    </div>
    <div class="nav-links">
        <a href="account.php" class="nav-icon-link"><i class="fa-solid fa-user"></i></a>
        
        <?php if (!$is_admin): ?>
            <a href="carrello.php" class="nav-icon-link">
                <i class="fa-solid fa-cart-shopping"></i>
                <?php if ($totale_articoli > 0): ?>
                    <span class="cart-count"><?php echo $totale_articoli; ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>
    </div>
</header>

<div id="mySidebar" class="sidebar">
    <div class="sidebar-header">
        <div class="logo">RILIES-SYSTEM <?php echo $is_admin ? "[ADMIN]" : ""; ?></div>
        <div onclick="closeNav()" style="cursor:pointer; font-size:11px; letter-spacing:2px; color:var(--text-light);">[ CLOSE_X ]</div>
    </div>
    <div class="sidebar-content">

        <div class="menu-section">
            <h3>SEZIONE</h3>
            <a href="scelta_categoria.php?gen=uomo">UOMO</a>
            <a href="scelta_categoria.php?gen=donna">DONNA</a>
            <a href="info.php">INFORMAZIONI</a>
        </div>

        <div class="menu-section">
            <h3>USER_NAVIGATION</h3>
            <div style="display:flex; gap:40px;">
                <a href="account.php">MY_ACCOUNT</a>
                </div>
        </div>

    </div>
</div>

<main class="hero">
    <div class="hero-content">
        <p class="hero-sub">PRIVATE ARCHIVE // EST. 2026</p>
        <h1 class="hero-title">Rilies</h1>

        <div class="search-container">
            <form action="ricerca.php" method="GET">
                <input type="text" name="q" placeholder="Cerca nel catalogo (es. hoodie, shirt donna...)">
                <button type="submit"><span>CERCO</span></button>
            </form>
            <p>DISCOVER THE LIMITED DROP // EST. 2026</p>
        </div>
    </div>

    <div class="ticker-wrap">
        <div class="ticker">
            RILIES PRIVATE ARCHIVE // SHIPPING WORLDWIDE // NO FAST FASHION // RILIES SYSTEM ACTIVE //&nbsp;&nbsp;&nbsp;RILIES PRIVATE ARCHIVE // SHIPPING WORLDWIDE // NO FAST FASHION // RILIES SYSTEM ACTIVE //
        </div>
    </div>
</main>

<script>
function openNav()  { document.getElementById("mySidebar").style.width = "100%"; }
function closeNav() { document.getElementById("mySidebar").style.width = "0"; }
window.addEventListener('load', () => { document.body.classList.remove('loading'); });
</script>

</body>
</html>