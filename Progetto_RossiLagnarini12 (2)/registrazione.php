<?php
session_start();
require_once 'config.php';
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['n'])) {
    $nome     = $_POST['n'];
    $cognome  = $_POST['c'];
    $email    = $_POST['e'];
    $password = password_hash($_POST['p'], PASSWORD_DEFAULT);
    $indirizzo = $_POST['i'];
    $citta    = $_POST['ct'];
    $cap      = $_POST['cp'];
    $provincia = $_POST['pr'];
    $nazione  = $_POST['nz'];
    $telefono = $_POST['tl'];
    
    // Default: Utente standard
    $ruolo = 'user';
    $totale_speso = 0.00;

    // Controllo Admin: Solo se è stata spuntata la richiesta admin
    if (isset($_POST['is_admin']) && $_POST['is_admin'] === '1') {
        $codice_segreto = "RILIES_ADMIN_2026";
        $codice_inserito = $_POST['codice_admin'] ?? '';
        
        if ($codice_inserito === $codice_segreto) {
            $ruolo = 'admin';
        } else {
            $msg = "ERRORE: Codice autorizzativo admin non valido.";
        }
    }

    // Procediamo solo se non ci sono errori (es. codice admin sbagliato)
    if (empty($msg)) {
        $sql = "INSERT INTO utente (nome, cognome, email, password, indirizzo, città, cap, provincia, nazione, telefono, ruolo, totale_speso) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssd", $nome, $cognome, $email, $password, $indirizzo, $citta, $cap, $provincia, $nazione, $telefono, $ruolo, $totale_speso);

        if ($stmt->execute()) {
            $msg = "success";
        } else {
            $msg = "ERRORE_DATABASE: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>UNISCITI // RILIES</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <style>
        .reg-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 40px;
        }
        .reg-grid .full-width { grid-column: 1 / -1; }
        .reg-card { width: 100%; max-width: 760px; }
        
        /* Stile per la sezione Admin */
        .admin-toggle-section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid var(--border-subtle);
            grid-column: 1 / -1;
        }
        #admin-code-wrapper { display: none; margin-top: 10px; }
    </style>
</head>
<body class="auth-page">

    <div class="auth-card reg-card">
        <p class="hero-sub">NUOVO_UTENTE</p>
        <h1>CREA ACCOUNT</h1>

        <?php if($msg === "success"): ?>
            <div class="status-box">
                IDENTITÀ CREATA. <a href="login.php" class="link-highlight">ACCEDI ORA →</a>
            </div>
        <?php elseif($msg): ?>
            <div class="status-box" style="color: red; border-color: red;"><?php echo $msg; ?></div>
        <?php endif; ?>

        <form method="POST" action="registrazione.php">
            <div class="reg-grid">
                <input type="text"     name="n"  placeholder="Nome"      required>
                <input type="text"     name="c"  placeholder="Cognome"    required>
                <input type="email"    name="e"  placeholder="Email"      required class="full-width">
                <input type="password" name="p"  placeholder="Password"   required class="full-width">
                <input type="text"     name="i"  placeholder="Indirizzo"  required class="full-width">
                <input type="text"     name="ct" placeholder="Città"      required>
                <input type="text"     name="cp" placeholder="CAP"        required>
                <input type="text"     name="pr" placeholder="Provincia"  required maxlength="2">
                <input type="text"     name="nz" placeholder="Nazione"    required>
                <input type="text"     name="tl" placeholder="Telefono"   required class="full-width">

                <div class="admin-toggle-section">
                    <label style="cursor: pointer; font-family: var(--font-mono); font-size: 12px;">
                        <input type="checkbox" name="is_admin" value="1" id="admin-checkbox" onchange="toggleAdminField()"> 
                        REGISTRATI COME AMMINISTRATORE
                    </label>
                    
                    <div id="admin-code-wrapper">
                        <input type="password" name="codice_admin" placeholder="Inserisci Codice Autorizzativo Admin" style="margin-bottom: 0;">
                    </div>
                </div>

                <button type="submit" class="full-width">REGISTRATI</button>
            </div>
        </form>

        <p class="auth-switch">HAI GIÀ UN ACCOUNT? <a href="login.php" class="link-highlight">ACCEDI</a></p>
    </div>

    <script>
        function toggleAdminField() {
            const checkbox = document.getElementById('admin-checkbox');
            const wrapper = document.getElementById('admin-code-wrapper');
            wrapper.style.display = checkbox.checked ? 'block' : 'none';
        }
    </script>
</body>
</html>