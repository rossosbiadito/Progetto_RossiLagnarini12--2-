<?php 
session_start();
require_once 'config.php';

$gen = isset($_GET['gen']) ? $_GET['gen'] : 'uomo';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>RILIES // <?php echo strtoupper($gen); ?>_ARCHIVE</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="archive-layout">

<header class="topbar">
    <a href="index.php" class="nav-icon-link">← BACK_TO_SYSTEM</a>
    <div class="logo">RILIES. <?php echo strtoupper($gen); ?></div>
    <div class="nav-links">
        <a href="carrello.php" class="nav-icon-link">
            <i class="fa-solid fa-cart-shopping"></i>
        </a>
    </div>
</header>

<main class="category-page-wrapper">

    <!-- Breadcrumb -->
    <div class="cat-page-header">
        <a href="index.php" class="back-crumb">← ARCHIVE</a>
        <span class="crumb-sep">/</span>
        <span class="current-crumb"><?php echo strtoupper($gen); ?> — SCEGLI CATEGORIA</span>
    </div>

    <!-- Rettangoli verticali -->
    <div class="cat-grid">

        <a href="prodotti.php?gen=<?php echo $gen; ?>&cat=Hoodie" class="cat-box" data-num="01">
            <span>Hoodie</span>
        </a>

        <a href="prodotti.php?gen=<?php echo $gen; ?>&cat=Bottom" class="cat-box" data-num="02">
            <span>Bottom</span>
        </a>

        <a href="prodotti.php?gen=<?php echo $gen; ?>&cat=Hat" class="cat-box" data-num="03">
            <span>Headwear</span>
        </a>

        <a href="prodotti.php?gen=<?php echo $gen; ?>&cat=Shirt" class="cat-box" data-num="04">
            <span>Tshirt</span>
        </a>

        <a href="prodotti.php?gen=<?php echo $gen; ?>&cat=Jacket" class="cat-box" data-num="05">
            <span>Outerwear</span>
        </a>

    </div>

</main>

<script>
    window.addEventListener('load', () => { document.body.classList.remove('loading'); });
</script>

</body>
</html>