<?php 
session_start();
require_once 'config.php';

// Protezione: Solo admin
if (!isset($_SESSION['id_utente']) || $_SESSION['ruolo'] !== 'amministratore') {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['id_utente'];

// Dati Admin
$stmt = $conn->prepare("SELECT nome, cognome, email FROM utente WHERE id_utente = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_data = $stmt->get_result()->fetch_assoc();

// Storico Rifornimenti
$sql_history = "SELECT r.quantita_aggiunta, r.data_operazione, p.nome as nome_prodotto 
                FROM storico_rifornimenti r 
                JOIN prodotto p ON r.id_prodotto = p.id_prodotto 
                WHERE r.id_admin = ? 
                ORDER BY r.data_operazione DESC";
$stmt_h = $conn->prepare($sql_history);
$stmt_h->bind_param("i", $admin_id);
$stmt_h->execute();
$history_result = $stmt_h->get_result();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>ADMIN // RILIES</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body>
<header class="topbar" style="padding: 20px; display: flex; justify-content: space-between; border-bottom: 2px solid #ff007a;">
    <a href="index.php">← BACK_TO_SYSTEM</a>
    <div class="logo">ADMIN_CONTROL_PANEL</div>
</header>
<main style="display: flex; padding: 40px;">
    <aside style="width: 300px;">
        <h3 class="group-title">MANAGEMENT</h3>
        <nav style="display: flex; flex-direction: column; gap: 10px;">
            <a href="scelta_categoria.php">CATALOGO_GESTIONALE</a>
            <a href="account_admin.php" style="font-weight: bold;">STORICO_RIFORNIMENTI</a>
            <a href="logout.php" style="color: red; margin-top: 20px;">TERMINATE_SESSION</a>
        </nav>
    </aside>
    <section style="flex: 1; padding-left: 40px;">
        <h2>DATI_AMMINISTRATORE</h2>
        <p>EMAIL: <?php echo $admin_data['email']; ?></p>
        <hr>
        <h3>STORICO_RIFORNIMENTI</h3>
        <table border="1" width="100%" style="border-collapse: collapse; text-align: left;">
            <thead>
                <tr>
                    <th>DATA</th>
                    <th>PRODOTTO</th>
                    <th>QUANTITÀ</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $history_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['data_operazione']; ?></td>
                        <td><?php echo strtoupper($row['nome_prodotto']); ?></td>
                        <td>+<?php echo $row['quantita_aggiunta']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>