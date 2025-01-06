<?php
session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
include 'db.php';

// Redirect jika pengguna tidak login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id']; // Ambil user ID dari sesi
$status = isset($_SESSION['status']) ? $_SESSION['status'] : '';
$status_type = isset($_SESSION['status_type']) ? $_SESSION['status_type'] : '';
unset($_SESSION['status'], $_SESSION['status_type']); // Hapus status dari sesi

//if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Query untuk mengambil data Kajur berdasarkan user_id
    $query = "SELECT users.id, users.username, users.email, users.password
              FROM users
              WHERE users.id = '$user_id'";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $username = $row['username'];
        $email = $row['email'];
    } else {
        echo "Data akun tidak ditemukan!";
        exit;
    }

//}

// Proses jika form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Perubahan profil
    if (isset($_POST['update_profile'])) {
        $new_username = mysqli_real_escape_string($conn, $_POST['username']);
        $new_email = mysqli_real_escape_string($conn, $_POST['email']);
    
        $query_update_profile = "UPDATE users SET username = '$new_username', email = '$new_email' WHERE id = '$user_id'";
        if (mysqli_query($conn, $query_update_profile)) {
            $_SESSION['status'] = "Profil berhasil diperbarui!";
            $_SESSION['status_type'] = 'success';
        } else {
            $_SESSION['status'] = "Gagal memperbarui profil.";
            $_SESSION['status_type'] = 'danger';
        }
        header('Location: pengaturan_admin.php');
        exit;
    }
    

    // Perubahan password
    if (isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        $query_check_password = "SELECT password FROM users WHERE id = '$user_id'";
        $result_check = mysqli_query($conn, $query_check_password);
        $user_data = mysqli_fetch_assoc($result_check);

        if ($user_data && password_verify($current_password, $user_data['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $query_update_password = "UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'";
                if (mysqli_query($conn, $query_update_password)) {
                    $_SESSION['status'] = "Password berhasil diubah.";
                    $_SESSION['status_type'] = 'success';
                } else {
                    $_SESSION['status'] = "Gagal mengubah password.";
                    $_SESSION['status_type'] = 'danger';
                }
            } else {
                $_SESSION['status'] = "Password baru tidak cocok.";
                $_SESSION['status_type'] = 'danger';
            }
        } else {
            $_SESSION['status'] = "Password saat ini salah.";
            $_SESSION['status_type'] = 'danger';
        }
        header('Location: pengaturan_admin.php');
        exit;
    }
}

// Proses jika form dikirim untuk update konfigurasi SMTP
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_smtp'])) {
    $smtp_username = mysqli_real_escape_string($conn, $_POST['smtpusername']);
    $smtp_password = mysqli_real_escape_string($conn, $_POST['smtppassword']);
    
    // Update ke database (sesuaikan dengan struktur tabel Anda)
    $query_update_smtp = "UPDATE smtp_config SET smtp_username = '$smtp_username', smtp_password = '$smtp_password' WHERE id = 1";
    if (mysqli_query($conn, $query_update_smtp)) {
        $_SESSION['status'] = "Konfigurasi SMTP berhasil diperbarui!";
        $_SESSION['status_type'] = 'success';
    } else {
        $_SESSION['status'] = "Gagal memperbarui konfigurasi SMTP.";
        $_SESSION['status_type'] = 'danger';
    }
    header('Location: pengaturan_admin.php');
    exit;
}

// Ambil konfigurasi SMTP untuk ditampilkan di form
$query_smtp = "SELECT smtp_username, smtp_password FROM smtp_config WHERE id = 1";
$result_smtp = mysqli_query($conn, $query_smtp);
if ($result_smtp && mysqli_num_rows($result_smtp) > 0) {
    $smtp_data = mysqli_fetch_assoc($result_smtp);
    $smtp_username = $smtp_data['smtp_username'];
    $smtp_password = $smtp_data['smtp_password'];
} else {
    $smtp_username = '';
    $smtp_password = '';
}
// Ambil status form saat ini
$query_form_status = "SELECT status FROM form_status LIMIT 1";
$result_form_status = mysqli_query($conn, $query_form_status);
$form_status = 'inactive'; // Default value
if ($result_form_status && mysqli_num_rows($result_form_status) > 0) {
    $row_form_status = mysqli_fetch_assoc($result_form_status);
    $form_status = $row_form_status['status'];
}

// Proses jika form dikirim untuk mengubah status form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_form_status'])) {
    $new_form_status = mysqli_real_escape_string($conn, $_POST['form_status']);

    // Perbarui status di tabel
    $query_update_status = "UPDATE form_status SET status = '$new_form_status' WHERE id = 1";
    if (mysqli_query($conn, $query_update_status)) {
        $_SESSION['status'] = "Status form berhasil diperbarui!";
        $_SESSION['status_type'] = 'success';
    } else {
        $_SESSION['status'] = "Gagal memperbarui status form.";
        $_SESSION['status_type'] = 'danger';
    }

    header('Location: pengaturan_admin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LANCAR - Pengaturan Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/logowebsite.png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    
   
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fa;
        }
        .container {
            max-width: 800px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 90px;
        
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
        .btn-primary {
            margin-top: 30px;
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
        h2 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 30px;
        }
        /* Kustomisasi modal sukses dan gagal */
        .modal-success .modal-content {
            border-color: #28a745;
            background-color: #d4edda;
            color: #155724;
        }
        .modal-danger .modal-content {
            border-color: #dc3545;
            background-color: #f8d7da;
            color: #721c24;
        }
        body {
            background-color: #f8f9fa;
        }
        .modal-backdrop {
    z-index: 1050 !important;
}
.modal {
    z-index: 2000 !important; /* Ensure modal appears above the backdrop */
}
.modal-content {
    z-index: 1060 !important; /* Ensure the modal content stays above navbar */
}


    
    .card {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border: none;
        border-radius: 8px;
    }
   
    .btn-upload {
        width: 100%;
        margin-top: 10px;
    }
    .form-group label {
        font-weight: normal;
    }
    .nav-tabs .nav-link {
        font-weight: bold;
    }
    body {
    background-color: #f8f9fa;
}
/* Perbaikan CSS */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    width: 250px;
    background-color: #fff;
    z-index: 1000;
    transform: translateX(-100%); /* Sidebar tersembunyi secara default */
    transition: transform 0.3s ease-in-out;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
}
.sidebar a {
            color: white;
            display: block;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 16px;
        }
        .sidebar a:hover {
            background-color:rgb(235, 234, 234);
        }
.sidebar.active {
    transform: translateX(0); /* Sidebar terlihat ketika aktif */
}

.navbar{
    z-index: 1050;
    background-color: #fff;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    
}

.content-wrapper {
    transition: all 0.3s ease-in-out;
    margin-left: 0;
}

.nav-link:hover {
    color:  black !important; /* Mengubah warna teks menjadi putih */
    font-weight: bold; 
    background-color: #007bff; /* (Optional) Menambahkan warna latar belakang biru saat hover */
}


.nav-link.active {
    color: #007bff; /* Menjaga warna teks biru untuk menu yang aktif */
}
.header-title {
            font-size: 1.5em;
            color: #007bff;
            font-weight: bold;
            text-align: left;
            margin-bottom: 30px;
        }


@media (max-width: 768px) {
    .sidebar {
        
        width: 100%;
        transform: translateX(-100%);
        transition: transform 0.3s ease-in-out;
        z-index: 1000;
        
    }
    .sidebar.active {
        transform: translateX(0);
    }
    .content-wrapper {
        margin-left: 0;
    }
    .nav-tabs .nav-item {
        flex-shrink: 0;
    }

}





</style>

    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container-fluid">
        <button class="btn me-3" id="sidebarToggle" style="background-color: transparent; border: none;">
            <span class="navbar-toggler-icon"></span>
        </button>

        <a class="navbar-brand text-black" href="#">LANCAR</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <!-- Tambahkan menu lain di sini jika diperlukan -->
            </ul>
        </div>
    </div>
</nav>


    <!-- Sidebar -->
   <!-- Sidebar -->
   <div class="sidebar bg-light p-3 d-flex flex-column" id="sidebar" style="height: 100vh;">
   <h4 class="text-center">LANCAR</h4>
    <small class="text-muted ms-4" style="margin-top: 40px;">MENU</small>
    <!-- Kategori Dispensasi Siswa -->
    <div class="mt-2">
        <a class="d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#dispensasiMenu" role="button" aria-expanded="false" aria-controls="dispensasiMenu">
            <small class="text-muted">Dispensasi Siswa</small>
            <i class="bi bi-chevron-down"></i>
        </a>
        <div class="collapse show" id="dispensasiMenu">
            <nav class="nav flex-column">
                <a class="nav-link d-flex align-items-center <?= $currentPage == 'dashboard_admin.php' ? 'active' : '' ?>" href="dashboard_admin.php" style="color: <?= $currentPage == 'dashboard_admin.php' ? '#007bff' : 'black'; ?>;">
                    <i class="bi bi-activity" style="margin-right: 15px;"></i> Dashboard
                </a>
                <a class="nav-link d-flex align-items-center <?= $currentPage == 'list_pengajuan.php' ? 'active' : '' ?>" href="list_pengajuan.php" style="color: <?= $currentPage == 'list_pengajuan.php' ? '#007bff' : 'black'; ?>;">
                    <i class="bi bi-file-earmark-plus" style="margin-right: 15px;"></i> Daftar Pengajuan
                </a>
                <a class="nav-link d-flex align-items-center <?= $currentPage == 'list_tolakPengajuan.php' ? 'active' : '' ?>" href="list_tolakPengajuan.php" style="color: <?= $currentPage == 'list_tolakPengajuan.php' ? '#007bff' : 'black'; ?>;">
                    <i class="bi bi-x-circle" style="margin-right: 15px;"></i> Daftar Pengajuan Ditolak
                </a>
                <a class="nav-link d-flex align-items-center <?= $currentPage == 'riwayat_pengajuan.php' ? 'active' : '' ?>" href="riwayat_pengajuan.php" style="color: <?= $currentPage == 'riwayat_pengajuan.php' ? '#007bff' : 'black'; ?>;">
                    <i class="bi bi-archive" style="margin-right: 15px;"></i> Riwayat Pengajuan
                </a>
                
                <a class="nav-link d-flex align-items-center <?= $currentPage == 'guruAtasan/list_atasan.php' ? 'active' : '' ?>" href="guruAtasan/list_atasan.php" style="color: <?= $currentPage == 'guruAtasan/list_atasan.php' ? '#007bff' : 'black'; ?>;">
                    <i class="bi bi-person-check" style="margin-right: 15px;"></i> Data Guru Atasan
                </a>
            </nav>
        </div>
    </div>

    <!-- Kategori Manajemen Surat -->
    <div class="mt-4">
        <a class="d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#suratMenu" role="button" aria-expanded="false" aria-controls="suratMenu">
            <small class="text-muted">Manajemen Surat Masuk/Keluar</small>
            <i class="bi bi-chevron-down"></i>
        </a>
        <div class="collapse show" id="suratMenu">
            <nav class="nav flex-column">
                <a class="nav-link d-flex align-items-center <?= $currentPage == 'suratmasukkeluar/surat.php' ? 'active' : '' ?>" href="suratmasukkeluar/surat.php" style="color: <?= $currentPage == 'suratmasukkeluar/surat.php' ? '#007bff' : 'black'; ?>;">
                    <i class="bi bi-envelope" style="margin-right: 15px;"></i> Surat Masuk/Keluar
                </a>
            </nav>
        </div>
    </div>

    <!-- Pengaturan dan Logout -->
    <small class="text-muted ms-2 mt-4">Pengaturan</small>
    <nav class="nav flex-column mt-2">
        <a class="nav-link d-flex align-items-center <?= $currentPage == 'pengaturan_admin.php' ? 'active' : '' ?>" href="pengaturan_admin.php" style="color: <?= $currentPage == 'pengaturan_admin.php' ? '#007bff' : 'black'; ?>;">
            <i class="bi bi-gear" style="margin-right: 15px;"></i> Pengaturan Akun
        </a>
        <a class="nav-link d-flex align-items-center <?= $currentPage == 'logout.php' ? 'active' : '' ?>" href="logout.php" style="color: <?= $currentPage == 'logout.php' ? '#007bff' : 'black'; ?>;">
            <i class="bi bi-box-arrow-right" style="margin-right: 15px;"></i> Logout
        </a>
    </nav>
</div>
<div class="content-wrapper"id="contentWrapper">
<div class="container  padding-top 40px">
    <div class="header-title">
        Pengaturan Administrator
    </div>
        <ul class="nav nav-tabs" id="pengaturanTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="profil-tab" data-toggle="tab" href="#profil" role="tab" aria-controls="profil" aria-selected="true">Profil Akun</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="password-tab" data-toggle="tab" href="#password" role="tab" aria-controls="password" aria-selected="false">Ubah Password Akun</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="email-tab" data-toggle="tab" href="#emailaplikasi" role="tab" aria-controls="emailaplikasi" aria-selected="false">Email Aplikasi</a>
            </li>
            
        </ul>

        <div class="tab-content mt-4" id="pengaturanTabsContent">
            <!-- Tab Profil -->
            <div class="tab-pane fade show active" id="profil" role="tabpanel" aria-labelledby="profil-tab">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card p-3">
                            <h5>Informasi Akun</h5>
                            <form method="POST">
                            <div class="form-group">
                                <label for="nama">Username</label>
                                <input type="text" class="form-control" id="nama" name="username" value="<?= htmlspecialchars($username); ?>" required>
                            </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email); ?>" required>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary">Perbarui Profil</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Tab Ubah Password -->
            <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                <div class="card p-3 mt-3">
                    <h5>Ubah Password</h5>
                    <form method="post">
                        <div class="form-group">
                            <label for="current_password">Password Saat Ini</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#current_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="new_password">Password Baru</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#new_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#confirm_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Ubah Password</button>
                    </form>
                </div>
            </div>

            <!-- Tab Email Aplikasi -->
            <!-- Tab Email Aplikasi -->
<<div class="tab-pane fade" id="emailaplikasi" role="tabpanel" aria-labelledby="email-tab">
    <div class="row">
        <div class="col-md-8">
            <div class="card p-3">
                <h5>Pengaturan Email Aplikasi</h5>
                <form method="POST">
                    <div class="form-group">
                        <label for="smtpusername">Email Aplikasi</label>
                        <input type="email" class="form-control" id="smtpusername" name="smtpusername" value="<?= htmlspecialchars($smtp_username); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="smtppassword">Password Aplikasi</label>
                        <input type="text" class="form-control" id="smtppassword" name="smtppassword" value="<?= htmlspecialchars($smtp_password); ?>" required>
                    </div>
                    <button type="submit" name="update_smtp" class="btn btn-primary">Perbarui Email</button>
                </form>
                <div class="mt-3">
                    <button class="btn btn-info" data-toggle="modal" data-target="#applicationPasswordModal">Cara Mendapatkan Password Aplikasi</button>
                </div>
            </div>
        </div>
        <!-- Card untuk mengatur status form pengajuan -->
        <div class="col-md-4">
            <div class="card p-3">
                <h5>Status Form Pengajuan</h5>
                <form method="POST">
                    <div class="form-group">
                        <label for="form_status">Form Pengajuan</label>
                        <select class="form-control" id="form_status" name="form_status">
                            <option value="active" <?= ($form_status === 'active') ? 'selected' : ''; ?>>Aktif</option>
                            <option value="inactive" <?= ($form_status === 'inactive') ? 'selected' : ''; ?>>Nonaktif</option>
                        </select>
                    </div>
                    <button type="submit" name="update_form_status" class="btn btn-primary mt-2">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal for Application Password Instructions -->
<div class="modal fade" id="applicationPasswordModal" tabindex="-1" role="dialog" aria-labelledby="applicationPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="applicationPasswordModalLabel">Cara Mendapatkan Password Aplikasi dari Google</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h6>Ikuti langkah-langkah berikut untuk mendapatkan password aplikasi dari akun Google:</h6>
                <ol>
                    <li>Buka <a href="https://myaccount.google.com/" target="_blank">halaman Pengaturan Akun Google</a>.</li>
                    <li>Pada menu sebelah kiri, pilih <strong>Keamanan</strong>.</li>
                    <li>Lakukan verifikasi 2 langkah terlebih dahulu jika belum.</li>
                    <li>Di bagian <strong>pencarian</strong>, ketikkan <strong>App Password</strong>.</li>
                    <li>Lalu pilih seperti pada gambar berikut:</li>
                    <li><img src="image/apppassword.png" alt="Petunjuk App Password" class="img-fluid"></li>
                    <li>Masukkan kata sandi Google Anda jika diminta.</li>
                    <li>Inputkan nama aplikasi, kemudian klik <strong>Buat</strong>.</li>
                    <li>Password aplikasi akan muncul</li>
                    <li>Salin password aplikasi yang dihasilkan dan masukkan ke dalam kolom Password Aplikasi di halaman ini.</li>
                </ol>
                <p><strong>Pastikan untuk menyimpan password aplikasi dengan aman, karena ini hanya akan muncul satu kali.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>



            
        </div>
    </div>

</div>
    
<!-- Modal for Upload Status (Success or Error) -->
<div class="modal fade" id="uploadStatusModal" tabindex="-1" aria-labelledby="uploadStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadStatusModalLabel">Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalMessage">
                <?php 
                    // Display status message from session
                    if ($status != '') {
                        echo "<p class='text-" . ($status_type == 'success' ? 'success' : 'danger') . "'>" . $status . "</p>";
                    }
                ?>
            </div>
        </div>
    </div>
</div>
<!-- Modal untuk menampilkan gambar profil -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="profileModalLabel">Profile Picture</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <img id="modal-profile-img" src="<?= $current_image ? 'image/' . $current_image : 'default_profile.png' ?>" alt="Profile Picture" class="img-fluid">
      </div>
    </div>
  </div>
</div>
<script src="lib/script.js"></script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>

<!-- Include JS and Bootstrap for modal functionality -->

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>



<script>
    document.getElementById("sidebarToggle").addEventListener("click", function() {
    const sidebar = document.getElementById("sidebar");
    
    // Toggle class "visible" untuk menampilkan sidebar dari atas pada layar kecil
    if (window.innerWidth <= 768) {
        sidebar.classList.toggle("visible");
    } else {
        // Mode biasa tetap gunakan toggle class "collapsed"
        sidebar.classList.toggle("collapsed");
    }

});
    // Menangani form konfirmasi modal
    $('#confirmButton').on('click', function () {
        $('#form-password').submit();
        $('#confirmModal').modal('hide');
    });

    // Show the modal after the page loads, if the session status is set
    <?php if (isset($_SESSION['status'])): ?>
        $(document).ready(function() {
            $('#statusModal').modal('show');
        });
    <?php endif; ?>

    function previewProfileImage(event) {
        const file = event.target.files[0];
        const reader = new FileReader();

        reader.onload = function(e) {
            // Menampilkan gambar yang diupload di dalam lingkaran
            document.getElementById('profile-img').src = e.target.result;
        }

        if (file) {
            reader.readAsDataURL(file); // Membaca file gambar sebagai URL
        }
    }
    //Check if session status and type exist and show modal with corresponding message
    <?php if ($status != ''): ?>
        var myModal = new bootstrap.Modal(document.getElementById('uploadStatusModal'), {
            keyboard: false
        });
        myModal.show();

        // Hide the modal after 3 seconds
        setTimeout(function() {
            myModal.hide();
        }, 3000);
    <?php endif; ?>
    

   
   

document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const target = document.querySelector(this.dataset.target);
            const icon = this.querySelector('i');

            if (target.type === 'password') {
                target.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                target.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
   // Seleksi elemen
   const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const contentWrapper = document.getElementById('contentWrapper');

    // Tambahkan event listener untuk tombol toggle sidebar
    sidebarToggle.addEventListener('click', () => {
        // Toggle kelas 'active' pada sidebar
        sidebar.classList.toggle('active');
    });

    // Tutup sidebar saat area konten diklik (opsional, jika ingin menutup sidebar secara otomatis)
    contentWrapper.addEventListener('click', () => {
        if (sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
        }
    });
</script>

</body>
</html>
