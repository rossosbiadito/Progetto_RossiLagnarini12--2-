<?php 
session_start();
require_once 'config.php';

$cat_nome = isset($_GET['cat']) ? $_GET['cat'] : ''; 
$gen = isset($_GET['gen']) ? $_GET['gen'] : 'uomo';

// CORREZIONE: Qui usiamo 'admin' come nel tuo database
$is_admin = (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin');

$query = "SELECT p.* FROM prodotto p 
          JOIN categorie c ON p.id_categoria = c.id_categoria 
          WHERE c.nome_categoria = ? AND c.genere = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $cat_nome, $gen);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>RILIES // <?php echo strtoupper($cat_nome); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body class="catalog-page">

<header class="topbar">
    <a href="scelta_categoria.php?gen=<?php echo $gen; ?>" class="nav-links">← INDIETRO</a>
    <div class="logo">RILIES. <?php echo $is_admin ? "ADMIN_VIEW" : strtoupper($cat_nome); ?></div>
    <div class="nav-links">
        <?php if(!$is_admin): ?>
            <a href="carrello.php" class="nav-icon-link"><i class="fa-solid fa-cart-shopping"></i></a>
        <?php endif; ?>
    </div>
</header>

<main class="catalog-container">
    <div class="grid-parent">
        <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <?php $link_file = $is_admin ? "prodotto_admin.php" : "prodotto.php"; ?>
                
                <div class="product-card">
                    <a href="<?php echo $link_file; ?>?id=<?php echo $row['id_prodotto']; ?>">
                        <img src="<?php echo $row['immagine']; ?>" class="catalog-img">
                        <div class="product-info">
                            <h2 class="product-name"><?php echo strtoupper($row['nome']); ?></h2>
                            <p class="product-price">€<?php echo number_format($row['prezzo'], 2); ?></p>
                        </div>
                    </a>
                    <div class="card-footer">
                        <a href="<?php echo $link_file; ?>?id=<?php echo $row['id_prodotto']; ?>" class="btn-brutal" 
                           style="<?php echo $is_admin ? 'background:#ff007a; color:white;' : ''; ?>">
                            <?php echo $is_admin ? 'GESTISCI' : 'DETTAGLI'; ?>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</main>
</body>
</html>