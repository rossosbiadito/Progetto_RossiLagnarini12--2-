<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['id_utente'])) { header("Location: login.php"); exit(); }

$id_prodotto = $_GET['id'];
$id_utente = $_SESSION['id_utente'];

// Verifica se l'utente ha davvero acquistato il prodotto
$check = $conn->prepare("SELECT do.id_prodotto FROM dettaglio_ordine do JOIN ordine o ON do.id_ordine = o.id_ordine WHERE o.id_utente = ? AND do.id_prodotto = ? LIMIT 1");
$check->bind_param("ii", $id_utente, $id_prodotto);
$check->execute();
if ($check->get_result()->num_rows == 0) {
    die("ERRORE: Non puoi recensire un prodotto non acquistato.");
}

// Recupero info prodotto
$stmt = $conn->prepare("SELECT nome, immagine, descrizione FROM prodotto WHERE id_prodotto = ?");
$stmt->bind_param("i", $id_prodotto);
$stmt->execute();
$prodotto = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>FEEDBACK // <?php echo $prodotto['nome']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .star-rating { display: flex; gap: 15px; font-size: 30px; margin: 20px 0; }
        .star { cursor: pointer; color: #222; transition: 0.2s; }
        .star.active { color: #ff007a; text-shadow: 0 0 10px rgba(255, 0, 122, 0.5); }
    </style>
</head>
<body class="account-page">
    <header class="topbar">
        <a href="account.php" class="nav-links">← ANNULLA_PROCEDURA</a>
        <div class="logo">SUBMIT_FEEDBACK_GATE</div>
    </header>

    <main style="max-width: 800px; margin: 60px auto; padding: 0 20px;">
        <div style="display: flex; gap: 40px; background: #050505; border: 1px solid #1a1a1a; padding: 40px;">
            <img src="<?php echo $prodotto['immagine']; ?>" style="width: 200px; border: 1px solid #222;">
            
            <div style="flex: 1;">
                <h2 class="hero-sub" style="margin-bottom: 10px;">STAI_RECENSENDO:</h2>
                <h1 style="font-size: 24px; letter-spacing: 3px; margin-bottom: 20px;"><?php echo strtoupper($prodotto['nome']); ?></h1>
                
                <form action="invia_recensione.php" method="POST">
                    <input type="hidden" name="id_prodotto" value="<?php echo $id_prodotto; ?>">
                    <input type="hidden" name="voto" id="voto_input" value="5">

                    <label style="font-size: 9px; color: #444; letter-spacing: 2px;">RATING_LEVEL_</label>
                    <div class="star-rating" id="star-container">
                        <?php for($i=1; $i<=5; $i++): ?>
                            <i class="fa-solid fa-star star active" data-value="<?php echo $i; ?>"></i>
                        <?php endfor; ?>
                    </div>

                    <textarea name="commento" required placeholder="TYPE YOUR EXPERIENCE WITH THIS ARCHIVE PIECE..." style="width: 100%; height: 120px; background: #000; border: 1px solid #222; color: #fff; padding: 15px; font-family: monospace; resize: none; margin-bottom: 20px;"></textarea>
                    
                    <button type="submit" class="btn-brutal" style="width: 100%; padding: 20px; background: #ff007a; border: none; color: #fff;">SEND_TO_ARCHIVE_SYSTEM</button>
                </form>
            </div>
        </div>
    </main>

    <script>
        const stars = document.querySelectorAll('.star');
        const votoInput = document.getElementById('voto_input');
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const val = this.dataset.value;
                votoInput.value = val;
                stars.forEach(s => s.classList.toggle('active', s.dataset.value <= val));
            });
        });
    </script>
</body>
</html>