<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

include 'db.php';

// Sertakan file PHPMailer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fungsi untuk mengirim email konfirmasi
function sendApprovalEmail($email, $nama_lengkap, $nis, $jurusan, $kelas, $alasan, $tanggal_pengajuan, $tanggal_akhir) {
    $mail = new PHPMailer(true);
    try {
        // Konfigurasi SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'adriansyahsumitra@gmail.com';
        $mail->Password = 'kivu njcw rcam nkwl';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Pengaturan penerima
        $mail->setFrom('adriansyahsumitra@gmail.com', 'Admin');
        $mail->addAddress($email);

        // Konten email
        $mail->isHTML(true);
        $mail->Subject = "Konfirmasi Persetujuan Pengajuan Dispensasi";
        
        // Template email dalam HTML
        $mail->Body = "
        <p>Halo <b>$nama_lengkap</b>,</p>
        <p>Berikut merupakan data dispensasi yang telah disetujui:</p>
        <table style='border-collapse: collapse; width: 40%;'>
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
        <p>Demikian keterangan dispensasi ini dibuat dengan sebenarnya untuk dapat digunakan sebagaimana mestinya.</p>
        <p>Terimakasih.</p>";

        $mail->send();
        echo "Email berhasil dikirim.";
    } catch (Exception $e) {
        echo "Email gagal dikirim. Error: {$mail->ErrorInfo}";
    }
}


// Cek apakah tombol "Setuju" atau "Tolak" diklik
if (isset($_POST['approve']) || isset($_POST['reject'])) {
    $status = isset($_POST['approve']) ? 'disetujui' : 'ditolak';
    $id = $_POST['id'];
    
    // Update status di database
    $query = "UPDATE pengajuan SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    // Jika disetujui, kirimkan email konfirmasi
    if ($status == 'disetujui') {
        // Ambil data terbaru dari database
        $query = "SELECT * FROM pengajuan WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pengajuan = $result->fetch_assoc();

        // Kirim email
        sendApprovalEmail(
            $pengajuan['email'],
            $pengajuan['nama_lengkap'],
            $pengajuan['nis'],
            $pengajuan['jurusan'],
            $pengajuan['kelas'],
            $pengajuan['alasan'],
            $pengajuan['tanggal_pengajuan'],
            $pengajuan['tanggal_akhir']
        );
    }

    

    // Redirect ulang halaman untuk menghindari pengiriman ulang form
    header("Location: detail_pengajuan.php?id=$id");
    exit();
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUDISMA - Dispensasi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
            transition: transform 0.3s ease;
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
        .btn.approve {
            background-color: green;
            color: white;
        }
        .btn.reject {
            background-color: red;
            color: white;
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
        /* Gaya untuk ikon lampiran */
.fas.fa-file-alt {
    color: #343a40; /* Warna ikon dokumen */
    cursor: pointer;
}

.status-badge {
            display: inline-block;  /* Ubah dari inline ke inline-block */
            padding: 3px 8px;  /* Padding lebih besar agar lebih terlihat */
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
            margin: 40px auto; /* Memberikan lebih banyak ruang di atas dan bawah */
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

        <a class="navbar-brand text-black" href="#">SUDISMA</a>
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
    <div class="sidebar bg-light p-3" id="sidebar">
        <h4 class="text-center">SUDISMA</h4>
        <div style="height: 40px;"></div>
        <small class="text-muted ms-2" style="margin-top: 80px;">Menu</small>
        <nav class="nav flex-column mt-2">
            <a class="nav-link active d-flex align-items-center text-dark" href="dashboard_admin.php" style="color: black;">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
            <a class="nav-link d-flex align-items-center text-dark" href="list_pengajuan.php" style="color: black;">
                <i class="bi bi-file-earmark-text me-2"></i> Dispensasi
            </a>
            <a class="nav-link d-flex align-items-center text-dark" href="list_angkatan.php" style="color: black;">
                <i class="bi bi-file-earmark-text me-2"></i> Kelas
            </a>
            <a class="nav-link d-flex align-items-center text-dark" href="list_dosen.php" style="color: black;">
                <i class="bi bi-file-earmark-text me-2"></i> Guru Piket
            </a>
            <a class="nav-link d-flex align-items-center text-dark" href="list_tanggal.php" style="color: black;">
                <i class="bi bi-file-earmark-text me-2"></i> Tanggal Pengajuan
            </a>
            <a class="nav-link d-flex align-items-center text-dark" href="logout.php" style="color: black;">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </nav>
    </div>

    <div class="main-content">
        <div class="container">
            <div class="card shadow-sm border-0">
            <h3 class="card-title text-center mb-3">List Data Dispensasi</h3>
                <div class="card-body">
                    <div class="data-list">
                        <p><strong>Nama</strong> <span><strong>: </strong><?= htmlspecialchars($pengajuan['nama_lengkap']); ?></span></p>
                        <p><strong>NIS</strong> <span><strong>: </strong><?= htmlspecialchars($pengajuan['nis']); ?></span></p>
                        <p><strong>Kelas</strong> <span><strong>: </strong><?= htmlspecialchars($pengajuan['kelas']); ?></span></p>
                        <p><strong>Tanggal Awal Dispensasi</strong> <span><strong>: </strong><?= htmlspecialchars($pengajuan['tanggal_pengajuan']); ?></span></p>
                        <p><strong>Tanggal Akhir Dispensasi</strong> <span><strong>: </strong><?= htmlspecialchars($pengajuan['tanggal_akhir']); ?></span></p>
                        <p><strong>Alasan</strong> <span><strong>: </strong><?= htmlspecialchars($pengajuan['alasan']); ?></span></p>
                        <p><strong>Email</strong> <span><strong>: </strong><?= htmlspecialchars($pengajuan['email']); ?></span></p>
                        <p><strong>Lampiran Dokumen</strong><span><strong>: </strong>
                            <?php if (!empty($pengajuan['dokumen_lampiran'])): ?>
                                <a href="uploads/<?= $pengajuan['dokumen_lampiran']; ?>" target="_blank" style="color: black;">
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
            <button type="submit" name="approve" class="btn btn-success btn-sm mx-1">Setuju</button>
            <button type="submit" name="reject" class="btn btn-danger btn-sm">Tidak</button>
        </form>
    <?php elseif ($pengajuan['status'] == 'disetujui'): ?>
        <!-- Tombol Cetak jika status disetujui -->
        <a href="cetak_pengajuan.php?id=<?= $pengajuan['id']; ?>" class="btn btn-primary">Cetak</a>
    <?php else: ?>
        <!-- Tampilkan status jika ditolak -->
        <span class="badge bg-danger">Pengajuan Ditolak</span>
    <?php endif; ?>
    </p>
</div>

                
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('d-none');
        });
    </script>
</body>
</html>
