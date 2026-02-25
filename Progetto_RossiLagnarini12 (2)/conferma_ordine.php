<?php
session_start();
require_once 'config.php';

require 'libs/PHPMailer/Exception.php';
require 'libs/PHPMailer/PHPMailer.php';
require 'libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['id_utente'])) {
    header("Location: login.php");
    exit;
}

$id_utente = $_SESSION['id_utente'];
$totale_pagato = $_POST['totale_finale'] ?? 0;

// Recupero dati utente
$stmt_user = $conn->prepare("SELECT email, nome FROM utente WHERE id_utente = ?");
$stmt_user->bind_param("i", $id_utente);
$stmt_user->execute();
$dati_utente = $stmt_user->get_result()->fetch_assoc();
$email_cliente = $dati_utente['email'];
$nome_cliente = $dati_utente['nome'];

$prodotti_scontrino = [];
$conn->begin_transaction();

try {
    // 1. REGISTRA L'ORDINE
    $stmt_ordine = $conn->prepare("INSERT INTO ordine (id_utente, data_ordine, totale, stato) VALUES (?, NOW(), ?, 'PAGATO')");
    $stmt_ordine->bind_param("id", $id_utente, $totale_pagato);
    $stmt_ordine->execute();
    $id_ordine = $conn->insert_id;

    // 2. AGGIORNA IL TOTALE SPESO
    $upd_utente = $conn->prepare("UPDATE utente SET totale_speso = totale_speso + ? WHERE id_utente = ?");
    $upd_utente->bind_param("di", $totale_pagato, $id_utente);
    $upd_utente->execute();

    $stmt_dettagli = $conn->prepare("INSERT INTO dettaglio_ordine (id_ordine, id_prodotto, quantita) VALUES (?, ?, ?)");
    $upd_stock = $conn->prepare("UPDATE prodotto SET stock = stock - ? WHERE id_prodotto = ? AND stock >= ?");

    if (isset($_SESSION['acquisto_diretto_id'])) {
        $id_p = (int)$_SESSION['acquisto_diretto_id'];
        $qta = 1;
        $res_p = $conn->query("SELECT nome, prezzo FROM prodotto WHERE id_prodotto = $id_p");
        $p_info = $res_p->fetch_assoc();
        
        $prodotti_scontrino[] = ['nome' => $p_info['nome'], 'prezzo' => $p_info['prezzo'], 'quantita' => $qta];
        $stmt_dettagli->bind_param("iii", $id_ordine, $id_p, $qta);
        $stmt_dettagli->execute();
        
        $upd_stock->bind_param("iii", $qta, $id_p, $qta);
        $upd_stock->execute();
        
        unset($_SESSION['acquisto_diretto_id']);
    } else {
        $res_cart = $conn->query("SELECT cp.id_prodotto, cp.quantita, p.nome, p.prezzo 
                                  FROM carrello_prodotti cp 
                                  JOIN prodotto p ON cp.id_prodotto = p.id_prodotto
                                  JOIN carrello c ON cp.id_carrello = c.id_carrello 
                                  WHERE c.id_utente = $id_utente");

        while ($item = $res_cart->fetch_assoc()) {
            $curr_id = $item['id_prodotto'];
            $curr_qta = $item['quantita'];
            $prodotti_scontrino[] = ['nome' => $item['nome'], 'prezzo' => $item['prezzo'], 'quantita' => $curr_qta];
            
            $stmt_dettagli->bind_param("iii", $id_ordine, $curr_id, $curr_qta);
            $stmt_dettagli->execute();
            
            $upd_stock->bind_param("iii", $curr_qta, $curr_id, $curr_qta);
            $upd_stock->execute();
        }
        $conn->query("DELETE cp FROM carrello_prodotti cp JOIN carrello c ON cp.id_carrello = c.id_carrello WHERE c.id_utente = $id_utente");
    }

    $conn->commit();

    // INVIO EMAIL
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Username = '97f56e90a27293';
        $mail->Password = '14070af7f63509';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 2525;
        $mail->setFrom('noreply@rilies.com', 'RILIES // SYSTEM');
        $mail->addAddress($email_cliente, $nome_cliente);
        $mail->isHTML(true);
        $mail->Subject = "RECEIPT_CONFIRMATION // #$id_ordine";

        $itemsRows = "";
        foreach ($prodotti_scontrino as $item) {
            $itemsRows .= "<tr><td>".strtoupper($item['nome'])." (x".$item['quantita'].")</td><td style='text-align:right;'>&euro; ".number_format($item['prezzo'], 2)."</td></tr>";
        }

        $mail->Body = "<div style='font-family:monospace; padding:20px;'>
                        <div style='border:1px solid #000; padding:20px; max-width:400px;'>
                            <h2>RILIES.</h2>
                            <p>ORDINE: #$id_ordine</p>
                            <table style='width:100%;'>$itemsRows</table>
                            <hr>
                            <p>TOTALE: &euro; ".number_format($totale_pagato, 2)."</p>
                        </div>
                       </div>";
        $mail->send();
    } catch (Exception $e) {}

} catch (Exception $e) {
    $conn->rollback();
    die("ERRORE: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>SUCCESS // RILIES</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background: #000; color: #fff; font-family: monospace; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .success-box { border: 1px solid #ff007a; padding: 40px; text-align: center; }
        .btn-brutal { display: inline-block; background: #fff; color: #000; padding: 15px 30px; text-decoration: none; margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="success-box">
        <h1>ACQUISITION_COMPLETE</h1>
        <p>REFERENCE: #<?php echo $id_ordine; ?></p>
        <p style="color: #666;">Ricevuta inviata a: <?php echo $email_cliente; ?></p>
        <a href="account.php" class="btn-brutal">VAI_AL_TRACKING →</a>
    </div>
</body>
</html>