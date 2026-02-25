<?php 
session_start(); // Necessario per controllare il ruolo
require_once 'config.php';

// Controllo ruolo admin
$is_admin = (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin');

// Pulizia della ricerca dell'utente
$search_query = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';
$parole_cercate = explode(' ', $search_query);

$mappa_categorie = [
    'maglietta' => 'Shirt', 'maglia' => 'Shirt', 'tshirt' => 'Shirt', 't-shirt' => 'Shirt', 
    'tee' => 'Shirt', 'top' => 'Shirt', 'canotta' => 'Shirt', 'polo' => 'Shirt',
    'pantaloni' => 'Bottom', 'panta' => 'Bottom', 'jeans' => 'Bottom', 'denim' => 'Bottom', 
    'pantalone' => 'Bottom', 'tuta' => 'Bottom', 'leggings' => 'Bottom', 'shorts' => 'Bottom',
    'felpa' => 'Hoodie', 'cappuccio' => 'Hoodie', 'felpata' => 'Hoodie', 'sweater' => 'Hoodie', 
    'maglione' => 'Hoodie', 'pullover' => 'Hoodie', 'zip-up' => 'Hoodie',
    'giubbotto' => 'Jacket', 'giacca' => 'Jacket', 'cappotto' => 'Jacket', 'bomber' => 'Jacket', 
    'piumino' => 'Jacket', 'windbreaker' => 'Jacket', 'outerwear' => 'Jacket',
    'cappello' => 'Hat', 'berretto' => 'Hat', 'beanie' => 'Hat', 'cappellino' => 'Hat', 
    'cup' => 'Hat', 'headwear' => 'Hat', 'trucker' => 'Hat'
];

$mappa_colori = [
    'rosa' => 'Pink', 'fucsia' => 'Pink',
    'nero' => 'Black', 'scuro' => 'Black', 'dark' => 'Black',
    'marrone' => 'Brown', 'terra' => 'Brown',
    'verde' => 'Green', 'acid' => 'Acid', 'fluo' => 'Acid',
    'bianco' => 'White', 'panna' => 'White',
    'leopardato' => 'Leopard', 'maculato' => 'Leopard', 'animalier' => 'Leopard',
    'tigre' => 'Tiger', 'tigrato' => 'Tiger',
    'stella' => 'Star', 'stelle' => 'Star',
    'logo' => 'Rilies', 'scritta' => 'Rilies',
    'stampa' => 'Print', 'grafica' => 'Print'
];

$sql = "SELECT p.*, c.genere, c.nome_categoria 
        FROM prodotto p 
        JOIN categorie c ON p.id_categoria = c.id_categoria 
        WHERE (";

$params = [];
$types = "";
$condizioni = [];

foreach ($parole_cercate as $parola) {
    $p_db = $parola;
    if (array_key_exists($parola, $mappa_categorie)) $p_db = $mappa_categorie[$parola];
    if (array_key_exists($parola, $mappa_colori)) $p_db = $mappa_colori[$parola];

    $term_like = "%$p_db%";
    $condizioni[] = "(p.nome LIKE ? OR p.descrizione LIKE ? OR c.genere LIKE ? OR c.nome_categoria LIKE ?)";
    array_push($params, $term_like, $term_like, $term_like, $term_like);
    $types .= "ssss";
}

$sql .= implode(" AND ", $condizioni) . ")";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>RILIES // SEARCH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body>

<header class="topbar">
    <a href="index.php" class="nav-links">← ARCHIVE</a>
    <div class="logo">RILIES. SEARCH_ENGINE <?php echo $is_admin ? "[ADMIN_MODE]" : ""; ?></div>
    <div class="nav-links">
        <?php if (!$is_admin): ?>
            <a href="carrello.php" style="text-decoration: none; color: inherit;">
                <i class="fa-solid fa-cart-shopping"></i>
            </a>
        <?php endif; ?>
    </div>
</header>

<main class="search-results">
    <h1 class="filter-label">
        FILTRO_ATTIVO: "<?php echo strtoupper(htmlspecialchars($search_query)); ?>"
    </h1>

    <div class="grid-parent">
        <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                
                <?php 
                // SMISTAMENTO DESTINAZIONE
                $target_page = $is_admin ? "prodotto_admin.php" : "prodotto.php"; 
                ?>
                
                <div class="product-card">
                    <a href="<?php echo $target_page; ?>?id=<?php echo $row['id_prodotto']; ?>">
                        <img src="<?php echo $row['immagine']; ?>" class="product-img">
                        <div class="product-info">
                            <p class="category-tag">
                                <?php echo strtoupper($row['genere']); ?> // <?php echo $row['nome_categoria']; ?>
                            </p>
                            <h2 class="product-title"><?php echo strtoupper($row['nome']); ?></h2>
                            <p class="product-price">€ <?php echo number_format($row['prezzo'], 2); ?></p>
                            
                            <?php if($is_admin): ?>
                                <p style="color: #ff007a; font-size: 10px; margin-top: 10px; font-family: monospace;">
                                    STOCK: <?php echo $row['stock']; ?> UNITÀ
                                </p>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-results">
                <p>NESSUNA CORRISPONDENZA TROVATA PER I TUOI TERMINI DI RICERCA.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

</body>
</html>