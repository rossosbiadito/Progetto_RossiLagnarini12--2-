<?php
session_start();
require_once 'config.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['e'];
    $password = $_POST['p'];

    // Cerchiamo l'utente (Tabella: utente)
    $sql = "SELECT id_utente, password, ruolo FROM utente WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            
            // SETTAGGIO SESSIONE UNIFICATO
            $_SESSION['id_utente'] = $user['id_utente'];
            $_SESSION['ruolo'] = $user['ruolo']; 

            // Reindirizzamento
            if ($user['ruolo'] === 'amministratore') {
                header("Location: account_admin.php");
            } else {
                header("Location: account.php");
            }
            exit;

        } else {
            $error = "PASSWORD_ERRATA";
        }
    } else {
        $error = "UTENTE_NON_TROVATO";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>ACCEDI // RILIES</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body class="auth-page">
    <div class="auth-card">
        <p class="hero-sub">RESTRICTED_ACCESS</p>
        <h1>LOGIN SYSTEM</h1>
        <?php if($error): ?>
            <div class="status-box" style="color: #ff007a; border: 1px solid #ff007a; padding: 10px; margin-bottom: 20px;">
                ERRORE: <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <input type="email" name="e" placeholder="EMAIL" required style="width:100%; padding:10px; margin-bottom:10px;">
            <input type="password" name="p" placeholder="PASSWORD" required style="width:100%; padding:10px; margin-bottom:10px;">
            <button type="submit" class="btn-brutal" style="width: 100%; padding:15px; background: black; color: white; cursor: pointer;">ENTER_SYSTEM</button>
        </form>
        <p style="margin-top:20px;">NON HAI UN ACCOUNT? <a href="registrazione.php">REGISTRATI</a></p>
    </div>
</body>
</html>