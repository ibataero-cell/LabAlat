<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

function kirim_email($penerima_email, $penerima_nama, $subjek, $pesan_body) {
    
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("PHPMailer belum terinstall. Email tidak dikirim.");
        return false;
    }

    $email_pengirim = 'ibataero@gmail.com'; 
    $nama_pengirim  = 'Admin LabAlat';
    $app_password   = 'ftsi jpcx luvt eaic';  

    $mail = new PHPMailer(true);

    try {
        // Server SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $email_pengirim;
        $mail->Password   = $app_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Penerima
        $mail->setFrom($email_pengirim, $nama_pengirim);
        $mail->addAddress($penerima_email, $penerima_nama);

        // Konten Email
        $mail->isHTML(true);
        $mail->Subject = $subjek;
        $mail->Body    = $pesan_body;
        $mail->AltBody = strip_tags($pesan_body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        // JIKA GAGAL: Catat di log error server, TAPI JANGAN TAMPILKAN DI LAYAR
        error_log("Gagal kirim email: {$mail->ErrorInfo}");
        return false;
    }
}
?>