<?php
// Questi sono i percorsi per caricare la libreria che hai messo nella cartella libs
require 'libs/PHPMailer/Exception.php';
require 'libs/PHPMailer/PHPMailer.php';
require 'libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function inviaScontrino($emailCliente, $idOrdine, $prodotti, $totale) {
    $mail = new PHPMailer(true);

    try {
        // --- Configurazione Mailtrap ---
        $mail->isSMTP();
        $mail->Host       = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth   = true;
        $mail->Username   = '97f56e90a27293';
        $mail->Password   = '50b06b64b13509';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // --- Mittente e Destinatario ---
        $mail->setFrom('system@rilies.com', 'RILIES ARCHIVE');
        $mail->addAddress($emailCliente);

        // --- Contenuto Scontrino ---
        $mail->isHTML(true);
        $mail->Subject = "RECEIPT_CONFIRMATION_#" . $idOrdine;

        // Generiamo il testo dello scontrino
        $itemsHtml = "";
        foreach ($prodotti as $p) {
            $itemsHtml .= "<tr><td>{$p['nome']} x{$p['qty']}</td><td align='right'>€{$p['prezzo']}</td></tr>";
        }

        $mail->Body = "
            <div style='font-family:monospace; border:1px solid #000; padding:20px; max-width:300px;'>
                <h2 style='text-align:center;'>RILIES.</h2>
                <p>ORDINE: #$idOrdine</p>
                <hr>
                <table width='100%'>$itemsHtml</table>
                <hr>
                <p><strong>TOTALE: €$totale</strong></p>
                <p style='font-size:10px; text-align:center;'>GRAZIE PER L'ACQUISTO</p>
            </div>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}