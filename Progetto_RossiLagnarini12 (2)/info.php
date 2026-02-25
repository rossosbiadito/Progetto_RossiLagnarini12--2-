<?php 
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>INFO // RILIES SYSTEM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <style>
        .info-container {
            max-width: 1000px;
            margin: 80px auto;
            padding: 0 40px;
            font-family: monospace;
        }
        .info-section {
            margin-bottom: 60px;
            border-left: 1px solid #ff007a;
            padding-left: 30px;
        }
        .info-section h2 {
            font-size: 14px;
            letter-spacing: 4px;
            color: #ff007a;
            margin-bottom: 20px;
        }
        .info-section p {
            font-size: 13px;
            line-height: 1.8;
            color: #ccc;
            max-width: 700px;
        }
        .tech-data {
            font-size: 10px;
            color: #444;
            margin-top: 10px;
            text-transform: uppercase;
        }
    </style>
</head>
<body class="product-page" style="background: #000; color: #fff;">

<header class="topbar">
    <a href="index.php" class="nav-links">← TORNA ALL'ARCHIVIO</a>
    <div class="logo">RILIES_INFORMATION_MODULE</div>
</header>

<main class="info-container">
    
    <section class="info-section">
        <h2>THE_PHILOSOPHY //</h2>
        <p>
            RILIES non è moda veloce. Ogni pezzo presente nel nostro archivio è selezionato per durare e per definire un'identità. 
            Operiamo secondo il principio "NO_FAST_FASHION", riducendo gli sprechi e massimizzando la qualità costruttiva dei capi.
        </p>
        <div class="tech-data">STATUS: ACTIVE // SUSTAINABILITY_LEVEL: HIGH</div>
    </section>

    <section class="info-section">
        <h2>SHIPPING_PROTOCOL //</h2>
        <p>
            Le spedizioni vengono processate entro 24 ore dall'ordine. Utilizziamo un sistema di tracking dinamico che puoi monitorare 
            direttamente dalla tua IDENTITY_CARD (Account). La consegna media avviene in 48 ore lavorative.
        </p>
        <div class="tech-data">LOGISTICS: GLOBAL_HUB // CARRIER: RILIES_EXPRESS</div>
    </section>

    <section class="info-section">
        <h2>RETURNS_POLICY //</h2>
        <p>
            È possibile richiedere il reso entro 14 giorni dalla ricezione del pacco. Il capo deve essere integro, non lavato 
            e con tutti i sigilli originali RILIES ancora applicati.
        </p>
        <div class="tech-data">RETURN_WINDOW: 14_DAYS // QC_CHECK: MANDATORY</div>
    </section>

    <section class="info-section">
        <h2>CONTACT_SUPPORT //</h2>
        <p>
            Per assistenza tecnica o problemi con l'ordine, contatta il nostro centro di comando:
            <br><br>
            EMAIL: support@rilies-system.com<br>
            HQ: Milan, Italy // Archive Sector B
        </p>
        <div class="tech-data">RESPONSE_TIME: < 4_HOURS</div>
    </section>

</main>

<footer style="text-align: center; padding: 40px; border-top: 1px solid #111;">
    <p style="font-size: 9px; color: #333; letter-spacing: 2px;">RILIES_SYSTEM © 2026 // ALL_RIGHTS_RESERVED</p>
</footer>

</body>
</html>