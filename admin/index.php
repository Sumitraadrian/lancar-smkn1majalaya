<?php
session_start();
include 'db.php';

const MAX_ATTEMPTS = 5; // Maksimal percobaan login
const BLOCK_TIME = 30; // Waktu blokir dalam detik
const SESSION_TIMEOUT = 60;

$remaining = 0;
$secret_key = '6LcWuq0qAAAAAHfdZyBkPf6FvCSsOg3ny4MnbYuY'; // Ganti dengan Secret Key Anda

// Cek apakah user sudah login sebagai admin
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {

      // Cek apakah sesi sudah kadaluarsa
      if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        // Jika sesi kedaluwarsa, hapus sesi dan arahkan ke halaman login
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit();
    }

    // Perbarui waktu aktivitas terakhir
    $_SESSION['last_activity'] = time();


    // Validasi ulang apakah user memang sudah login dengan benar
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'admin'");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header('Location: dashboard_admin.php');
        exit();
    } else {
        // Jika validasi gagal, hapus sesi
        session_unset();
        session_destroy();
    }
}

// Inisialisasi percobaan login jika belum ada
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['block_until'] = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Cek apakah user sedang diblokir
    if (isset($_SESSION['block_until']) && time() < $_SESSION['block_until']) {
        $remaining = $_SESSION['block_until'] - time();
        $error = "Terlalu banyak percobaan login. Silakan coba lagi dalam $remaining detik.";
    } else {
        // Reset blokir jika waktu sudah habis
        if (isset($_SESSION['block_until']) && time() >= $_SESSION['block_until']) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['block_until'] = null;
        }

        // Validasi login
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Verifikasi reCAPTCHA
        $recaptcha_response = $_POST['g-recaptcha-response'];
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha_data = array(
            'secret' => $secret_key,
            'response' => $recaptcha_response
        );

        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($recaptcha_data)
            )
        );
        $context = stream_context_create($options);
        $verify_response = file_get_contents($recaptcha_url, false, $context);
        $response_data = json_decode($verify_response);

        if ($response_data->success) {
            // Lanjutkan validasi login jika reCAPTCHA valid
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // Reset percobaan login jika berhasil
                    $_SESSION['login_attempts'] = 0;
                    $_SESSION['block_until'] = null;

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];

                    if ($user['role'] === 'admin') {
                        header('Location: dashboard_admin.php');
                        exit();
                    } else {
                        $error = "Hanya admin yang dapat login di sini.";
                    }
                } else {
                    $error = "Password/Username Salah!";
                }
            } else {
                $error = "Password/Username Salah!";
            }

            // Tambah percobaan login jika gagal
            if (isset($error)) {
                $_SESSION['login_attempts']++;
                if ($_SESSION['login_attempts'] >= MAX_ATTEMPTS) {
                    $_SESSION['block_until'] = time() + BLOCK_TIME;
                    $remaining = BLOCK_TIME;
                    $error = "Terlalu banyak percobaan login. Silakan coba lagi dalam $remaining detik.";
                }
            }
        } else {
            $error = "Verifikasi reCAPTCHA gagal. Silakan coba lagi.";
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LANCAR - Layanan Surat SMKN 1 Majalaya</title>
    <link rel="icon" href="favicon.ico">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link rel="icon" type="image/png" href="image/logowebsite.png">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg,rgb(229, 246, 255), #ffffff);
            color: #333;
        }
        .card {
            border: none;
            border-radius: 20px;
            background: rgb(247, 252, 255);
            
        }
        .btn-primary, .btn-success {
            border-radius: 50px;
            padding: 12px 25px;
            font-weight: bold;
        }
        .custom-img-shift {
            width: 100%; /* Lebar penuh agar responsif */
            max-width: 1200px; /* Batas maksimum lebar */
            height: auto; /* Menjaga rasio aspek */
            transition: transform 0.3s ease; /* Efek animasi saat hover */
            border-radius: 20px; /* Membuat sudut lebih halus */
        }
        .custom-img-shift:hover {
            transform: translateY(-10px);
        }

        /* Title and Subtitle 
        .sudisma-title {
            font-size: 3.5rem;
            font-weight: 700;
            color: #007bff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        .sudisma-subtitle {
            font-size: 1.6rem;
            font-weight: 500;
            color: #007bff;
        }*/
        
        /* Media query for smaller screens */
        @media (max-width: 768px) {
            .sudisma-title {
                font-size: 2.5rem;
            }
            .sudisma-subtitle {
                font-size: 1.3rem;
            }
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 70%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        
        /* Custom form styling */
        .form-group input {
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 10px;
        }
        
        .form-group input:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="row w-100 align-items-center">
            <div class="col-md-6 text-center text-white mb-4 mb-md-0">
                <!--<h1 class="sudisma-title">LANCAR</h1>-->
                <img src="image/logologin.png" alt="Illustration" class="img-fluid mt-3 custom-img-shift">

                <!--<p class="sudisma-subtitle">Layanan Administrasi Surat dan Dispensasi SMK Negeri 1 Majalaya</p>-->
            </div>
            
            <!-- Login / Register Form Section -->
            <div class="col-md-6">
                <div class="card p-5 shadow-lg">
                    <h2 class="card-title mb-3 text-center text-primary font-weight-bold">Selamat Datang di Lancar!</h2>
                    <p class="text-muted text-center">Masuk mengakses aplikasi</p>

                    <?php if (isset($error)) : ?> 
                        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                        <?php elseif ($remaining > 0): ?>
                            <div id="blockCountdown">
                                <span id="countdown"><?php echo $remaining; ?></span> detik.
                            </div>
                    <?php elseif (isset($success)) : ?>
                        <div class="alert alert-success text-center"><?php echo $success; ?></div>
                    <?php else : ?>
                        <div id="captchaError" class="alert alert-danger text-center" style="display: none;"></div>
                    <?php endif; ?>


                    <!-- Login Form -->
                    <form id="loginForm" action="" method="POST">
                        <div class="form-group">
                            <label for="username" class="text-dark">Username</label>
                            <input type="text" id="username" name="username" class="form-control" required>
                        </div>
                        <div class="form-group position-relative">
                            <label for="password" class="text-dark">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                            <span class="password-toggle" onclick="togglePassword('password')">
                                <i class="fa fa-eye" id="togglePasswordIcon"></i>
                            </span>
                        </div>
                        <!-- Tambahkan reCAPTCHA -->
                        <!-- Tambahkan ID yang konsisten -->
                        <div id="g-recaptcha" class="g-recaptcha mb-3" data-sitekey="6LcWuq0qAAAAAMKTm3qTqHDZnkGNf5YqEzk27Gbr"></div>
                        <button type="submit" name="login" class="btn btn-primary btn-block" id="loginButton">Login</button>


                    </form>


                    <hr>
                    <p class="text-muted text-center">
                        <a href="forgot_password.php" id="forgotPasswordLink">Lupa Password?</a>
                    </p>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    


    <script>
        function togglePassword(id) {
    const passwordField = document.getElementById(id);
    const toggleIcon = passwordField.nextElementSibling.querySelector('i');

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
document.getElementById('loginForm').addEventListener('submit', function (event) {
        // Ambil nilai dari reCAPTCHA
        const recaptchaResponse = document.querySelector('.g-recaptcha-response').value;

        // Temukan elemen untuk menampilkan error
        const errorAlert = document.getElementById('captchaError');

        // Cek apakah reCAPTCHA diisi
        if (!recaptchaResponse.trim()) {
            event.preventDefault(); // Mencegah form dikirim
            errorAlert.textContent = "Silakan selesaikan CAPTCHA terlebih dahulu.";
            errorAlert.style.display = 'block'; // Tampilkan elemen error

            // Sembunyikan alert secara otomatis setelah 3 detik
            setTimeout(() => {
                errorAlert.style.display = 'none';
            }, 3000); // Waktu dalam milidetik
        } else {
            errorAlert.style.display = 'none'; // Sembunyikan jika valid
        }
    });
  // Inisialisasi waktu tersisa dari PHP
let timeLeft = <?php echo $remaining; ?>;

// Jika waktu tersisa lebih dari 0, mulai countdown
if (timeLeft > 0) {
    const countdownElement = document.getElementById('countdown');
    const loginButton = document.getElementById('loginButton');
    const loginForm = document.getElementById('loginForm');

    // Nonaktifkan tombol login dan form
    loginButton.disabled = true;
    loginForm.style.pointerEvents = 'none';

    // Perbarui countdown setiap detik
    const timer = setInterval(() => {
        timeLeft--;
        countdownElement.textContent = timeLeft;

        if (timeLeft <= 0) {
            clearInterval(timer); // Hentikan timer
            countdownElement.parentElement.style.display = 'none'; // Sembunyikan countdown
            loginButton.disabled = false; // Aktifkan tombol login
            loginForm.style.pointerEvents = 'auto'; // Aktifkan form
        }
    }, 1000); // Interval 1 detik
}


    </script>
</body>
</html>
