<?php
session_start();
include 'db.php';

// Check if the user is already logged in as admin
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header('Location: dashboard_admin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: dashboard_admin.php');
                exit();
            } else {
                $error = "Hanya admin yang dapat login di sini.";
            }
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Pengguna tidak ditemukan!";
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
                <img src="image/logo.png" alt="Illustration" class="img-fluid mt-3 custom-img-shift">

                <!--<p class="sudisma-subtitle">Layanan Administrasi Surat dan Dispensasi SMK Negeri 1 Majalaya</p>-->
            </div>
            
            <!-- Login / Register Form Section -->
            <div class="col-md-6">
                <div class="card p-5 shadow-lg">
                    <h2 class="card-title mb-3 text-center text-primary font-weight-bold">Selamat Datang di Lancar!</h2>
                    <p class="text-muted text-center">Masuk mengakses aplikasi</p>

                    <?php if (isset($error)) : ?>
                        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                    <?php elseif (isset($success)) : ?>
                        <div class="alert alert-success text-center"><?php echo $success; ?></div>
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
                        <button type="submit" name="login" class="btn btn-primary btn-block">Login</button>
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
    </script>
</body>
</html>
