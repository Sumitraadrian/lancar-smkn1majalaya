<?php
session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

include 'db.php';
setlocale(LC_TIME, 'id_ID.UTF-8');

// Sertakan file PHPMailer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fungsi untuk mengirim email konfirmasi
function sendApprovalEmail($email, $nama_lengkap, $nis, $jurusan, $kelas, $alasan, $tanggal_pengajuan, $tanggal_akhir) {
    global $conn;

    $query = "SELECT * FROM smtp_config WHERE id = 1";
    $result = $conn->query($query);
    $smtp_config = $result->fetch_assoc();

    $mail = new PHPMailer(true);
    try {
        // Konfigurasi SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_config['smtp_username'];
        $mail->Password = $smtp_config['smtp_password'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Pengaturan penerima
        $mail->setFrom($smtp_config['smtp_username'], 'Admin SMKN 1 Majalaya');
        $mail->addAddress($email);

        // Konten email
        $mail->isHTML(true);
        $mail->Subject = "Konfirmasi Persetujuan Pengajuan Dispensasi";
        
        // Template email dalam HTML
        $mail->Body = "
    <div style='display: flex; justify-content: center; align-items: center; min-height: 100vh;'>
        <div style='border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); padding: 20px; width: 50%; background-color: #f9f9f9;'>
            <p style='font-size: 16px;'>Halo <b>$nama_lengkap</b>,</p>
            <p style='font-size: 16px;'>Berikut merupakan data dispensasi yang telah disetujui:</p>
            <table style='border-collapse: collapse; width: 100%; font-size: 14px;'>
                <tr>
                    <td style='font-weight: bold;'>Nama</td>
                    <td>: $nama_lengkap</td>
                </tr>
                <tr>
                    <td style='font-weight: bold;'>NIS</td>
                    <td>: $nis</td>
                </tr>
                <tr>
                    <td style='font-weight: bold;'>Jurusan</td>
                    <td>: $jurusan</td>
                </tr>
                <tr>
                    <td style='font-weight: bold;'>Kelas</td>
                    <td>: $kelas</td>
                </tr>
                <tr>
                    <td style='font-weight: bold;'>Keperluan</td>
                    <td>: $alasan</td>
                </tr>
                <tr>
                    <td style='font-weight: bold;'>Tanggal Mulai Pengajuan</td>
                    <td>: $tanggal_pengajuan</td>
                </tr>
                <tr>
                    <td style='font-weight: bold;'>Tanggal Akhir Pengajuan</td>
                    <td>: $tanggal_akhir</td>
                </tr>
            </table>
            <br>
            <p style='font-size: 16px;'>Demikian keterangan dispensasi ini dibuat dengan sebenarnya untuk dapat digunakan sebagaimana mestinya.</p>
            <p style='font-size: 16px;'>Terimakasih.</p>
        </div>
    </div>";

        // Kirim email
        $mail->send();
        return true; // Email berhasil dikirim
    } catch (Exception $e) {
        return false; // Email gagal dikirim
    }
}

// Cek apakah tombol "Setuju" atau "Tolak" diklik
if (isset($_POST['approve']) || isset($_POST['reject'])) {
    $status = isset($_POST['approve']) ? 'disetujui' : 'ditolak';
    $id = $_POST['id'];
    $tanggal_disetujui = ($status == 'disetujui') ? date('Y-m-d H:i:s') : null;

    $message = '';
    $messageType = ''; // success or danger

    $conn->begin_transaction();

    try {
        // Update status di database
        $query = "UPDATE pengajuan SET status = ?, tanggal_disetujui = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $status, $tanggal_disetujui, $id);
        $stmt->execute();

        // Jika disetujui, kirim email konfirmasi
        if ($status == 'disetujui') {
            $query = "SELECT * FROM pengajuan WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $pengajuan = $result->fetch_assoc();

            $emailSent = sendApprovalEmail(
                $pengajuan['email'],
                $pengajuan['nama_lengkap'],
                $pengajuan['nis'],
                $pengajuan['jurusan'],
                $pengajuan['kelas'],
                $pengajuan['alasan'],
                $pengajuan['tanggal_pengajuan'],
                $pengajuan['tanggal_akhir']
            );

            if (!$emailSent) {
                $conn->rollback();
                $message = 'Email gagal dikirim. Pengajuan tidak disetujui.';
                $messageType = 'danger';
                echo "<script>
                        sessionStorage.setItem('notification', JSON.stringify({message: '$message', type: '$messageType'}));
                        window.location.href='detail_pengajuan.php?id=$id';
                      </script>";
                exit();
            }
        }

        $message = $status == 'disetujui' ? 'Pengajuan berhasil disetujui.' : 'Pengajuan berhasil ditolak.';
        $messageType = 'success';
        $conn->commit();

        echo "<script>
                sessionStorage.setItem('notification', JSON.stringify({message: '$message', type: '$messageType'}));
                window.location.href='detail_pengajuan.php?id=$id';
              </script>";
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $message = 'Terjadi kesalahan. Pengajuan tidak diproses.';
        $messageType = 'danger';
        echo "<script>
                sessionStorage.setItem('notification', JSON.stringify({message: '$message', type: '$messageType'}));
                window.location.href='detail_pengajuan.php?id=$id';
              </script>";
        exit();
    }
}


// Pastikan ID pengajuan ada di URL dan valid
if (!isset($_GET['id'])) {
    echo "ID pengajuan tidak ditemukan.";
    exit();
}

$id = $_GET['id'];
$query = "SELECT * FROM pengajuan WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pengajuan = $result->fetch_assoc();

if (!$pengajuan) {
    echo "Pengajuan tidak ditemukan.";
    exit();
}

$statusMap = [
    'pending' => 'Menunggu Persetujuan',
    'disetujui' => 'Disetujui',
    'ditolak' => 'Ditolak'
];

$statusClass = '';
$statusText = '';

if ($pengajuan['status'] == 'pending') {
    $statusClass = 'status-belum-diproses';
} elseif ($pengajuan['status'] == 'disetujui') {
    $statusClass = 'status-disetujui';
} elseif ($pengajuan['status'] == 'ditolak') {
    $statusClass = 'status-ditolak';
}

$statusText = $statusMap[$pengajuan['status']];
$tanggal_awal = strftime('%d %B %Y', strtotime($pengajuan['tanggal_pengajuan']));
$tanggal_akhir = strftime('%d %B %Y', strtotime($pengajuan['tanggal_akhir']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LANCAR - Detail Pengajuan Dispensasi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/logowebsite.png">
    <style>
        body {
            background-color: #a3c1e0;
        }
        .sidebar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000; 
            transition: transform 0.3s ease;
            transform: translateX(0);
        }
        .sidebar.hidden {
    transform: translateX(-100%); /* Sidebar tersembunyi */
}
        .navbar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            margin-top: 150px;
            min-height: calc(100vh - 56px); 
        }
        .status.pending {
            color: orange;
        }
        .status.approved {
            color: green;
        }
        .status.rejected {
            color: red;
        }
        .main-content {
            margin-left: -200px;
            padding: 20px;
            margin-top: 150px; /* Adjust for dashboard header */
            min-height: calc(100vh - 56px); 
        }
      
        .card {
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
        }
        .back-button {
            display: block;
            margin-top: 20px;
            text-align: center;
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
        /* Gaya untuk ikon lampiran */
.fas.fa-file-alt {
    color: #343a40; /* Warna ikon dokumen */
    cursor: pointer;
}

.status-badge {
            display: inline-block;  /* Ubah dari inline ke inline-block */
            padding: 5px 8px;  /* Padding lebih besar agar lebih terlihat */
            font-size: 1em;
            border-radius: 20px;
            color: white;
            font-weight: bold;
            margin: 0;
            font-size: 0.9em;
            margin-right: 1900px;
            white-space: nowrap;
            width: auto !important;  /* Menyesuaikan lebar dengan konten status */
            display: inline-block !important;
        }
 
        .status-disetujui {
            background-color: #267739; /* Warna hijau lebih muda */
        }

        .status-ditolak {
            background-color: #b5364f; /* Warna merah lebih cerah */
        }

        .status-belum-diproses {
            background-color: #cc7a00; /* Warna oranye lebih cerah */
        }
       
        .btn.approve {
            background-color: green;
            color: white;
        }
        .btn-success {
            background-color: #28a745;
            border: none;
            border-radius: 20px; /* Tombol melengkung */
            padding: 8px 20px;
            font-size: 0.9em;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
            border-radius: 20px; /* Tombol melengkung */
            padding: 8px 20px;
            font-size: 0.9em;
        }
        .btn.reject {
            background-color: red;
            color: white;
        }
        .card {
            max-width: 560px;
            margin: -40px auto; /* Memberikan lebih banyak ruang di atas dan bawah */
            padding: 30px;
            background: white;
            border-radius: 10px; /* Sudut melengkung */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Bayangan lembut */
            border: 1px solid #ddd; /* Garis halus di sekitar */
            font-family: 'Roboto', sans-serif; /* Font profesional */
        }
        .card-title {
            font-size: 1.5em; /* Ukuran font judul */
            font-weight: bold; /* Cetak tebal untuk judul */
            color: #333; /* Warna teks judul */
            text-align: center;
        }

        .card-body {
            line-height: 1.6; /* Meningkatkan spasi antar baris */
            color: #555; /* Warna teks isi */
        }
        .back-button {
            display: block;
            margin-top: 20px;
            text-align: center;
        }
        .data-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .data-list p {
            display: flex;
            align-items: center; /* Menyelaraskan vertikal */
            justify-content: space-between;
            margin: 0;
            border-bottom: 1px solid #eee; /* Garis pembatas antar item */
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .data-list p:last-child {
            border-bottom: none; /* Menghilangkan garis di item terakhir */
        }

        .data-list p strong {
            flex: 0 0 42%;
        }

        .data-list p span, .data-list p a {
            width: 60%;
        }
        .nav-link.active {
    color: #007bff; /* Menjaga warna teks biru untuk menu yang aktif */
        }
        .chart-container {
            width: 100%; /* Full width of its parent */
            height: 400px; /* Adjust height as necessary */
            display: flex;
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 50px; /* Adjust top margin for spacing */
        }

        canvas {
            width: 100% !important; /* Make the canvas take full width of the container */
            height: 100% !important; /* Make the canvas take full height of the container */
        }
        .nav-link:hover {
    color:  black !important; /* Mengubah warna teks menjadi putih */
    font-weight: bold; 
    background-color: #007bff; /* (Optional) Menambahkan warna latar belakang biru saat hover */
}


.nav-link.active {
    color: #007bff; /* Menjaga warna teks biru untuk menu yang aktif */
}
.alert.hide {
    opacity: 0;
    transition: opacity 0.5s ease-out;
}
#notification-area {
    transition: all 0.5s ease;
}
#notification-area:empty {
    height: 0;
    padding: 0;
}
.modal-dialog-centered {
    transition: transform 0.3s ease-in-out;
}


        
        /* Responsiveness */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
            }
            .main-content {
                margin-left: 0;
            }
            #sidebarToggle {
                display: inline-block;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
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
    <div class="sidebar bg-light p-3 d-flex flex-column" id="sidebar" style="height: 100vh;">
            <h4 class="text-center">LANCAR</h4>
    <small class="text-muted ms-2" style="margin-top: 40px;">MENU</small>
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
                <a class="nav-link d-flex align-items-center <?= ($currentPage == 'list_pengajuan.php' || $currentPage == 'detail_pengajuan.php') ? 'active' : '' ?>" 
                href="list_pengajuan.php" 
                style="color: <?= ($currentPage == 'list_pengajuan.php' || $currentPage == 'detail_pengajuan.php') ? '#007bff' : 'black'; ?>;">
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

    <div class="main-content">
        <div class="container">
            <div class="card shadow-sm border-0">
            <h3 class="card-title text-center mb-3">List Data Dispensasi</h3>
            <div id="notification-area" class="mt-3"></div>

                <div class="card-body">
                    <div class="data-list">
                        <p><strong>Nama</strong> <span><strong>: </strong><?= htmlspecialchars($pengajuan['nama_lengkap']); ?></span></p>
                        <p><strong>NIS</strong> <span><strong>: </strong><?= htmlspecialchars($pengajuan['nis']); ?></span></p>
                        <p><strong>Kelas</strong> <span><strong>: </strong><?= htmlspecialchars($pengajuan['kelas']); ?></span></p>
                        
                        <p><strong>Tanggal Awal Dispensasi</strong> <span><strong>: </strong><?= $tanggal_awal; ?></span></p>
                        <p><strong>Tanggal Akhir Dispensasi</strong> <span><strong>: </strong><?= $tanggal_akhir; ?></span></p>
                        <p><strong>Alasan</strong> <span><strong>: </strong><?= htmlspecialchars($pengajuan['alasan']); ?></span></p>
                        <p><strong>Email</strong> <span><strong>: </strong><?= htmlspecialchars($pengajuan['email']); ?></span></p>
                        <p><strong>Lampiran Dokumen</strong><span><strong>: </strong>
                            <?php if (!empty($pengajuan['dokumen_lampiran'])): ?>
                                <a href="../uploads/<?= $pengajuan['dokumen_lampiran']; ?>" target="_blank" style="color: black;">
                                    <i class="bi bi-file-earmark-text" style="font-size: 1.5rem;"></i>
                                </a>
                            <?php else: ?>
                                Tidak ada
                            <?php endif; ?>
                        </span></p>    
                        
                        <p><strong>Status:</strong><span><strong>: </strong></span> 
                        <span class="status-badge <?= $statusClass; ?>">
                            <?= htmlspecialchars($statusText); ?>
                        </span>
                    </p>
        
    
    </div>

    <!-- Form untuk Setuju / Tolak -->
    <p><strong>Aksi:</strong>
    <?php if ($pengajuan['status'] == 'pending'): ?>
        <form method="post" class="d-inline">
            <input type="hidden" name="id" value="<?= $pengajuan['id']; ?>">
            <input type="hidden" name="email" value="<?= $pengajuan['email']; ?>">
            <input type="hidden" name="nama_lengkap" value="<?= $pengajuan['nama_lengkap']; ?>">
            <button type="button" class="btn btn-success btn-sm mx-1" 
                data-bs-toggle="modal" data-bs-target="#modalSetuju" 
                onclick="setIdModal(<?= $pengajuan['id']; ?>, 'setuju')">
                Setuju
            </button>
            <button type="button" class="btn btn-danger btn-sm" 
                    data-bs-toggle="modal" data-bs-target="#modalTolak" 
                    onclick="setIdModal(<?= $pengajuan['id']; ?>, 'tolak')">
                Tolak
            </button>
        </form>
    <?php elseif ($pengajuan['status'] == 'disetujui'): ?>
        <!-- Tombol Cetak jika status disetujui -->
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPenandatangan">
        <i class="bi bi-printer"></i> Cetak
    </button>

    <?php else: ?>
        <!-- Tampilkan status jika ditolak -->
        <!--<span class="badge bg-danger">Pengajuan Ditolak</span>
        Tombol Setuju dan Tolak jika status pending atau ditolak -->
    <form method="post" class="d-inline">
        <input type="hidden" name="id" value="<?= $pengajuan['id']; ?>">
        <input type="hidden" name="email" value="<?= $pengajuan['email']; ?>">
        <input type="hidden" name="nama_lengkap" value="<?= $pengajuan['nama_lengkap']; ?>">
        <button type="button" class="btn btn-success btn-sm mx-1" 
        data-bs-toggle="modal" 
        data-bs-target="#modalSetuju" 
        onclick="setIdModal(<?= $pengajuan['id']; ?>, 'setuju')">Setuju</button>
<button type="button" class="btn btn-danger btn-sm" 
        data-bs-toggle="modal" 
        data-bs-target="#modalTolak" 
        onclick="setIdModal(<?= $pengajuan['id']; ?>, 'tolak')">Tolak</button>

    </form>
    <?php endif; ?>
    </p>
</div>

                
            </div>
        </div>
    </div>
    <!-- Modal Konfirmasi Setuju -->
    <div class="modal fade" id="modalSetuju" tabindex="-1" aria-labelledby="modalSetujuLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Header Modal -->
            <div class="modal-header border-0">
                <h5 class="modal-title text-center w-100" id="modalSetujuLabel">
                    <i class="bi bi-check-circle text-success" style="font-size: 6rem;"></i>
                </h5>
            </div>
            <!-- Body Modal -->
            <div class="modal-body text-center">
                <h5 class="fw-bold">Setujui Pengajuan Ini?</h5>
                <p class="text-muted">Setelah disetujui, status tidak bisa diubah, dan sistem akan otomatis mengirim email konfirmasi kepada siswa.</p>
            </div>
            <!-- Footer Modal -->
            <div class="modal-footer justify-content-center border-0">
                <form method="post">
                    <input type="hidden" name="id" id="idSetuju">
                    <button type="submit" name="approve" class="btn btn-success px-4">
                        <i class="bi bi-check-circle"></i> Ya, Setuju
                    </button>
                    <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalTolak" tabindex="-1" aria-labelledby="modalTolakLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Header Modal -->
            <div class="modal-header border-0">
                <h5 class="modal-title text-center w-100" id="modalTolakLabel">
                    <i class="bi bi-exclamation-circle text-danger" style="font-size: 6rem;"></i>
                </h5>
            </div>
            <!-- Body Modal -->
            <div class="modal-body text-center">
                <h5 class="fw-bold">Konfirmasi Penolakan</h5>
                <p class="text-muted">Apakah Anda yakin ingin menolak pengajuan ini?</p>
            </div>
            <!-- Footer Modal -->
            <div class="modal-footer justify-content-center border-0">
                <form method="post">
                    <input type="hidden" name="id" id="idTolak">
                    <button type="submit" name="reject" class="btn btn-danger px-4">
                        <i class="bi bi-x-circle"></i> Ya, Tolak
                    </button>
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal untuk Pilihan Penandatangan -->
<!-- Modal untuk Pilihan Penandatangan -->
<div class="modal fade" id="modalPenandatangan" tabindex="-1" aria-labelledby="modalPenandatanganLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPenandatanganLabel">Pilih Penandatangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formCetak" method="GET" action="cetak_pengajuan.php">
                    <input type="hidden" name="id" value="<?= $pengajuan['id']; ?>">
                    <div class="mb-3">
                        <label for="penandatangan" class="form-label">Penandatangan</label>
                        <select name="penandatangan_id" id="penandatangan" class="form-select" required>
                            <option value="">-- Pilih Penandatangan --</option>
                            <?php
                            $query = "SELECT id, nama FROM atasan_sekolah";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value=\"{$row['id']}\">{$row['nama']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="nomor_surat" class="form-label">Nomor Surat</label>
                        <input type="text" name="nomor_surat" id="nomor_surat" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Cetak</button>
                </form>
            </div>
        </div>
    </div>
</div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('hidden');
});

        document.getElementById('formCetak').addEventListener('submit', function (e) {
    const penandatangan = document.getElementById('penandatangan').value;
    if (!penandatangan) {
        e.preventDefault();
        alert('Harap pilih penandatangan terlebih dahulu.');
    }
});
function setIdModal(id, action) {
        if (action === 'setuju') {
            document.getElementById('idSetuju').value = id;
        } else if (action === 'tolak') {
            document.getElementById('idTolak').value = id;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
    const notificationArea = document.getElementById('notification-area');
    const notificationData = sessionStorage.getItem('notification');
    
    if (notificationData) {
        const { message, type } = JSON.parse(notificationData);
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        notificationArea.appendChild(alertDiv);

        // Hapus notifikasi setelah beberapa detik
        setTimeout(() => {
            alertDiv.classList.remove('show');
            alertDiv.classList.add('hide');
            setTimeout(() => {
                notificationArea.removeChild(alertDiv);
            }, 400); // Tunggu sampai animasi selesai sebelum menghapus elemen
            sessionStorage.removeItem('notification');
        }, 4000); // 5 detik
    }
});


    </script>
</body>
</html>
