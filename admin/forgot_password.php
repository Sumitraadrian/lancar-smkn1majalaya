<?php
session_start();
include 'db.php';

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = $success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        // Periksa apakah email terdaftar
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        if (!$stmt) {
            die("Error dalam prepare statement: " . $conn->error);
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $userId = $user['id'];

            // Hitung jumlah permintaan dalam 1 jam terakhir
            $stmt = $conn->prepare("
                SELECT COUNT(*) AS request_count 
                FROM password_resets 
                WHERE user_id = ? AND created_at > NOW() - INTERVAL 1 HOUR
            ");
            if (!$stmt) {
                die("Error dalam prepare statement (COUNT): " . $conn->error);
            }
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $countData = $result->fetch_assoc();

            if ($countData['request_count'] >= 5) {
                $error = "Anda telah mencapai batas maksimal 5 permintaan reset password dalam 1 jam. Silakan coba lagi nanti.";
            } else {
                $token = bin2hex(random_bytes(32));
                $expiresAt = date("Y-m-d H:i:s", time() + (60 * 15)); // Token berlaku selama 15 menit

                // Simpan token di tabel password_resets
                $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (?, ?, ?, NOW())");
                if (!$stmt) {
                    die("Error dalam prepare statement (INSERT): " . $conn->error);
                }
                
                $stmt->bind_param("iss", $userId, $token, $expiresAt);
                if ($stmt->execute()) {
                    // Fetch SMTP configuration from database
                    $query = "SELECT * FROM smtp_config WHERE id = 1";
                    $result = $conn->query($query);
                    $smtp_config = $result->fetch_assoc();

                    // Kirim email dengan PHPMailer
                    $mail = new PHPMailer(true);

                    try {
                        // Konfigurasi SMTP
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com'; // Gunakan server SMTP Gmail
                        $mail->SMTPAuth = true;
                        $mail->Username = $smtp_config['smtp_username']; // Fetch SMTP username from DB
                        $mail->Password = $smtp_config['smtp_password']; // Fetch SMTP password from DB
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;

                        // Informasi pengirim dan penerima
                        $mail->setFrom($smtp_config['smtp_username'], 'LANCAR Admin');
                        $mail->addAddress($email, $user['username']);

                        // Konten email
                        $resetLink = "http://localhost/dispensasikp/admin/reset_password.php?token=$token";
                        $mail->isHTML(true);
                        $mail->Subject = 'Reset Password - LANCAR';
                        $mail->Body = "
                                <html>
                                <head>
                                    <style>
                                        .card {
                                            background-color:#fff;
                                            border-radius: 8px;
                                            padding: 20px;
                                            width: 100%;
                                            max-width: 600px;
                                            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                                        }
                                        .card-header {
                                            font-size: 1.5rem;
                                            font-weight: bold;
                                            color:rgb(10, 10, 10);
                                            margin-bottom: 15px;
                                        }
                                        .card-body {
                                            font-size: 1rem;
                                            color: #333;
                                        }
                                        .card-footer {
                                            font-size: 0.875rem;
                                            color: #6c757d;
                                            margin-top: 20px;
                                        }
                                        a {
                                            color:rgb(8, 8, 8);
                                            text-decoration: none;
                                        }
                                    </style>
                                </head>
                                <body>
                                    <div class='card'>
                                        <div class='card-header'>
                                            Reset Password
                                        </div>
                                        <div class='card-body'>
                                            <p>Kami menerima permintaan untuk reset password Anda. Klik tautan di bawah untuk mengatur ulang password Anda:</p>
                                            <a href='$resetLink'>$resetLink</a>
                                            <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
                                        </div>
                                        <div class='card-footer'>
                                            <p>Salam,</p>
                                            <p>LANCAR Admin</p>
                                        </div>
                                    </div>
                                </body>
                                </html>
                            ";


                        $mail->send();
                        $success = "Email reset password telah dikirim ke $email.";
                    } catch (Exception $e) {
                        $error = "Gagal mengirim email: {$mail->ErrorInfo}";
                    }
                } else {
                    $error = "Gagal menyimpan token reset password.";
                }
            }
        } else {
            $error = "Email tidak ditemukan.";
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="row w-100 align-items-center">
            <div class="col-md-6">
                <div class="card p-5 shadow-lg">
                    <h2 class="card-title mb-3 text-center text-primary font-weight-bold">Lupa Password?</h2>
                    <p class="text-muted text-center">Masukkan email Anda untuk mereset password</p>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success" role="alert">
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="email" class="text-dark">Email</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <button type="submit" name="reset_password" class="btn btn-success btn-block">Kirim Link Reset</button>
                    </form>

                    <p class="text-muted mt-3 text-center">Kembali ke <a href="index.php">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
