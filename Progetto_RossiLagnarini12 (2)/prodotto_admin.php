<?php
session_start();
require_once "config.php";

// Sicurezza: controllo ruolo admin
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$id_prodotto = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// MODIFICA: Recuperiamo anche i dati della categoria (nome e genere) per il link di ritorno
$query = "SELECT p.*, c.nome_categoria, c.genere 
          FROM prodotto p 
          JOIN categorie c ON p.id_categoria = c.id_categoria 
          WHERE p.id_prodotto = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_prodotto);
$stmt->execute();
$prodotto = $stmt->get_result()->fetch_assoc();

if (!$prodotto) die("Errore: Prodotto non trovato.");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>ADMIN // <?php echo $prodotto['nome']; ?></title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body class="product-page">

<header class="topbar">
    <a href="prodotti.php?cat=<?php echo $prodotto['nome_categoria']; ?>&gen=<?php echo $prodotto['genere']; ?>" class="nav-links">
        ← TORNA ALLA LISTA
    </a>
    
    <div class="logo">RILIES_SYSTEM // <span>ADMIN_MODE</span></div>
    <div class="nav-links">
        </div>
</header>

<main class="product-detail-container">
    <div class="product-media-box">
        <img src="<?php echo $prodotto['immagine']; ?>" class="product-hero-img">
    </div>

    <div class="product-info-box">
        <h1 class="product-main-title"><?php echo strtoupper($prodotto['nome']); ?></h1>
        <p class="product-ref">DATABASE_ID: #<?php echo $prodotto['id_prodotto']; ?></p>
        
        <div class="inventory-status" style="border: 1px solid #ff007a; padding: 20px; margin: 30px 0;">
            <p style="color: #ff007a; font-family: monospace; font-size: 14px;">
                UNITÀ IN MAGAZZINO: <?php echo $prodotto['stock']; ?>
            </p>
        </div>

        <div class="admin-actions">
            <a href="rifornimento.php?id=<?php echo $id_prodotto; ?>" class="btn-brutal" 
               style="background:#ff007a; color:white; text-decoration:none; display:block; text-align:center; padding:20px; font-weight: bold;">
                ESEGUI_RIFORNIMENTO
            </a>
        </div>
    </div>
</main>

</body>
</html>