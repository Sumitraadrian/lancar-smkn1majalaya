<?php
session_start();
include 'db.php';
$currentPage = basename($_SERVER['PHP_SELF']);

require('libs/fpdf.php'); // Include the FPDF library

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
   
    // Query to get all the pengajuan data
    $query_pengajuan = "
    SELECT p.*, s.nama_file, s.path_file
    FROM pengajuan p
    LEFT JOIN surat_dispensasi s ON p.id = s.pengajuan_id
    WHERE p.status = 'disetujui'
    ORDER BY p.id ASC
";
$result_pengajuan = mysqli_query($conn, $query_pengajuan);

if (!$result_pengajuan) {
    die("Error pada query pengajuan: " . mysqli_error($conn));
}

} else {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit;
}
function formatTanggalRentang($start_date, $end_date) {
    // Pemetaan bulan dari Inggris ke Indonesia (dengan huruf kapital semua)
    $bulanIndonesia = [
        'January' => 'JANUARI',
        'February' => 'FEBRUARI',
        'March' => 'MARET',
        'April' => 'APRIL',
        'May' => 'MEI',
        'June' => 'JUNI',
        'July' => 'JULI',
        'August' => 'AGUSTUS',
        'September' => 'SEPTEMBER',
        'October' => 'OKTOBER',
        'November' => 'NOVEMBER',
        'December' => 'DESEMBER'
    ];

    // Membuat objek DateTime
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);

    // Mengambil nama bulan dalam bahasa Indonesia
    $startMonth = $bulanIndonesia[$start->format('F')];
    $endMonth = $bulanIndonesia[$end->format('F')];

    // Jika bulan dan tahun sama
    if ($start->format('Y-m') === $end->format('Y-m')) {
        // Format: "Tanggal Awal - Tanggal Akhir Bulan Tahun"
        return $start->format('d') . ' - ' . $end->format('d') . ' ' . $startMonth . ' ' . $start->format('Y');
    } else {
        // Format: "Tanggal Awal Bulan Tahun - Tanggal Akhir Bulan Tahun"
        return $start->format('d') . ' ' . $startMonth . ' ' . $start->format('Y') . ' - ' . $end->format('d') . ' ' . $endMonth . ' ' . $end->format('Y');
    }
}


if (isset($_GET['export_pdf']) && $_GET['export_pdf'] == 'true') {
    // Get parameters from the form
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
    $penandatangan_id = isset($_GET['penandatangan_id']) ? (int)$_GET['penandatangan_id'] : 0;

    // Validate input
    if (!$start_date || !$end_date || !$penandatangan_id) {
        die("Semua parameter (start_date, end_date, dan penandatangan_id) harus diisi.");
    }
    $judul_rentang_tanggal = formatTanggalRentang($start_date, $end_date);
    // Fetch pengajuan data
    $query_pengajuan = "SELECT * FROM pengajuan 
                        WHERE tanggal_pengajuan BETWEEN ? AND ?
                        AND status = 'disetujui'
                        ORDER BY tanggal_pengajuan DESC";
    $stmt_pengajuan = $conn->prepare($query_pengajuan);
    $stmt_pengajuan->bind_param("ss", $start_date, $end_date);
    $stmt_pengajuan->execute();
    $result_pengajuan = $stmt_pengajuan->get_result();

    // Fetch penandatangan data
    $query_atasan = "SELECT nama, nip, CONCAT(pangkat, '/', golongan) AS pangkat_golongan, jabatan, instansi 
                     FROM atasan_sekolah WHERE id = ?";
    $stmt_atasan = $conn->prepare($query_atasan);
    $stmt_atasan->bind_param("i", $penandatangan_id);
    $stmt_atasan->execute();
    $result_atasan = $stmt_atasan->get_result();

    if ($result_atasan->num_rows > 0) {
        $atasan = $result_atasan->fetch_assoc();
    } else {
        die("Data penandatangan tidak ditemukan.");
    }
    // PDF generation logic
    class PDF extends FPDF {
        public $atasan;
        function NbLines($w, $txt)
            {
                $cw = &$this->CurrentFont['cw'];
                if ($w == 0) {
                    $w = $this->w - $this->rMargin - $this->x;
                }
                $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
                $s = str_replace("\r", '', $txt);
                $nb = strlen($s);
                if ($nb > 0 && $s[$nb - 1] == "\n") {
                    $nb--;
                }
                $sep = -1;
                $i = 0;
                $j = 0;
                $l = 0;
                $nl = 1;
                while ($i < $nb) {
                    $c = $s[$i];
                    if ($c == "\n") {
                        $i++;
                        $sep = -1;
                        $j = $i;
                        $l = 0;
                        $nl++;
                        continue;
                    }
                    if ($c == ' ') {
                        $sep = $i;
                    }
                    $l += $cw[$c];
                    if ($l > $wmax) {
                        if ($sep == -1) {
                            if ($i == $j) {
                                $i++;
                            }
                        } else {
                            $i = $sep + 1;
                        }
                        $sep = -1;
                        $j = $i;
                        $l = 0;
                        $nl++;
                    } else {
                        $i++;
                    }
                }
                return $nl;
            }

        
        function Footer() {
            
            function bulanIndonesia($bulanInggris) {
                $bulan = [
                    'January' => 'Januari',
                    'February' => 'Februari',
                    'March' => 'Maret',
                    'April' => 'April',
                    'May' => 'Mei',
                    'June' => 'Juni',
                    'July' => 'Juli',
                    'August' => 'Agustus',
                    'September' => 'September',
                    'October' => 'Oktober',
                    'November' => 'November',
                    'December' => 'Desember'
                ];
                return $bulan[$bulanInggris];
            }
                        
            $this->SetY(-50);
            $this->SetFont('Times', '', 12);
            $this->SetX(120);
            $this->Cell(0, 6, 'Majalaya, ' . date("d") . ' ' . bulanIndonesia(date("F")) . ' ' . date("Y"), 0, 1, 'L');
            $this->SetX(120);
            $this->Cell(0, 6, strtoupper($this->atasan['jabatan']), 0, 1, 'L');

            $this->Ln(20);
            $this->SetX(120);
            $this->SetFont('Times', 'B', 12);
            $this->Cell(0, 6, strtoupper($this->atasan['nama']), 0, 1, 'L');

            $this->Line(120, $this->GetY(), 190, $this->GetY());
            $this->SetFont('Times', '', 12);
            $this->SetX(120);
            $this->Cell(0, 6, 'NIP: ' . $this->atasan['nip'], 0, 1, 'L');
        }
        
        
       
        
    }

    // Create PDF object and add page
    $pdf = new PDF('P', 'mm', 'A4'); // Set orientation to 'P' for Portrait (default)
    $pdf->atasan = $atasan;
    $pdf->AddPage();

    // Set document title
    $pdf->SetFont('Times', 'B', 16);
    $pdf->Cell(0, 10, 'REKAPITULASI PENGAJUAN DISPENSASI SISWA', 0, 1, 'C');
    $pdf->Cell(0, 10, 'SMK NEGERI 1 MAJALAYA', 0, 1, 'C');
    $pdf->Cell(0, 10, 'PERIODE: ' . $judul_rentang_tanggal, 0, 1, 'C');
    $pdf->Ln(10);

   // Header Tabel
// Header Tabel
$header = ['No', 'Nama Lengkap', 'NIS', 'Jurusan', 'Kelas', 'Keterangan', 'Tanggal Pengajuan', 'Tanggal Akhir', 'Status'];
$widths = [8, 25, 20, 20, 15, 40, 20, 20, 20]; // Lebar kolom sesuai header

// Menghitung tinggi setiap header kolom
$pdf->SetFont('Times', 'B', 10);
$header_heights = [];
foreach ($header as $key => $col) {
    $header_heights[] = $pdf->NbLines($widths[$key], $col) * 6;
}
$max_header_height = max($header_heights);

// Render Header
foreach ($header as $key => $col) {
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->Rect($x, $y, $widths[$key], $max_header_height);
    $pdf->MultiCell($widths[$key], 6, $col, 0, 'C');
    $pdf->SetXY($x + $widths[$key], $y);
}
$pdf->Ln($max_header_height); // Pindah ke baris berikutnya
// Pindah ke baris berikutnya

// Render Data Tabel
$pdf->SetFont('Times', '', 10);
$no = 1; // Nomor uru
while ($row = mysqli_fetch_assoc($result_pengajuan)) {
    $data = [
        $no++,
        ucwords(htmlspecialchars($row['nama_lengkap'])),
        htmlspecialchars($row['nis']),
        ucwords(htmlspecialchars($row['jurusan'])),
        htmlspecialchars($row['kelas']),
        ucwords(htmlspecialchars($row['alasan'])),
        $row['tanggal_pengajuan'],
        $row['tanggal_akhir'],
        $row['status'] === 'pending' ? 'Belum diproses' : ($row['status'] === 'disetujui' ? 'Diterima' : 'Ditolak'),
    ];

    // Menghitung tinggi sel berdasarkan konten
    $heights = [];
    foreach ($data as $key => $text) {
        $heights[] = $pdf->NbLines($widths[$key], $text) * 6;
    }
    $row_height = max($heights);

    // Cetak baris
    foreach ($data as $key => $text) {
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Rect($x, $y, $widths[$key], $row_height);
        $pdf->MultiCell($widths[$key], 6, $text, 0, 'C');
        $pdf->SetXY($x + $widths[$key], $y);
    }
    $pdf->Ln($row_height);
      // Cek jika tabel mendekati footer
      if ($pdf->GetY() > 260) { // Adjust based on your document size
        $pdf->AddPage(); // Tambahkan halaman baru
    }
}


    // Output the PDF to the browser
    $pdf->Output();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id_to_delete = $_POST['delete_id'];

    // Cari path file surat dispensasi yang terkait dengan pengajuan
    $query_get_file = "SELECT path_file FROM surat_dispensasi WHERE pengajuan_id = ?";
    $stmt_get_file = $conn->prepare($query_get_file);
    $stmt_get_file->bind_param("i", $id_to_delete);
    $stmt_get_file->execute();
    $result_get_file = $stmt_get_file->get_result();

    if ($result_get_file->num_rows > 0) {
        $row = $result_get_file->fetch_assoc();
        $path_file = $row['path_file'];

        // Hapus file jika ada
        if (file_exists($path_file)) {
            unlink($path_file);
        }
    }

    $stmt_get_file->close();

    // Hapus data dari tabel pengajuan
    $delete_query = "DELETE FROM pengajuan WHERE id = ?";
    $stmt_delete = $conn->prepare($delete_query);
    $stmt_delete->bind_param("i", $id_to_delete);

    if ($stmt_delete->execute()) {
        // Hapus data dari tabel surat_dispensasi juga
        $delete_surat_query = "DELETE FROM surat_dispensasi WHERE pengajuan_id = ?";
        $stmt_delete_surat = $conn->prepare($delete_surat_query);
        $stmt_delete_surat->bind_param("i", $id_to_delete);
        $stmt_delete_surat->execute();
        $stmt_delete_surat->close();

        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Data dan file surat berhasil dihapus!'];
    } else {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Gagal menghapus data. Coba lagi!'];
    }

    $stmt_delete->close();
    header("Location: riwayat_pengajuan.php");
    exit();
}




?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LANCAR - Riwayat Pengajuan</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/logowebsite.png">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            position: fixed;
            top: 0;
            left: -250px; /* Sidebar tersembunyi di kiri */
            width: 250px; /* Lebar sidebar */
            transition: left 0.3s ease; /* Animasi ketika sidebar muncul dari kiri */
            z-index: 1000;
        }
        .navbar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .sidebar h4 {
            text-align: center;
            color: white;
            margin-bottom: 20px;
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
        .sidebar.visible {
        left: 0; /* Sidebar muncul dari kiri */
    }
        
        .main-content {
            
            margin-left: 100px; /* Beri ruang agar konten tidak tertutup sidebar */
        padding: 20px;
        transition: margin-left 0.3s ease;
        z-index: 1;
        padding-top: 60px; 
        overflow: hidden;
        }
        .status-badge {
            padding: 3px 8px;
            font-size: 0.8em;
            border-radius: 20px;
            font-weight: bold;
        }
       
        .status-belum-diproses {
            background-color: #cc7a00;
            color: white;
        }
        .status-diterima {
            background-color: #267739;
            color: white;
        }
        .status-ditolak {
            background-color: #b5364f;
            color: white;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-custom {
            padding: 3px 5px;
            font-size: 0.85em;
            border-radius: 3px;
        }
        .table-container {
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 0;
            width: 100%; /* Pastikan lebar tabel menyesuaikan layar */
            overflow-x: auto;   
        }
        
        .table thead th {
            background-color: #a3c1e0;
            color: black;
            font-size: 0.9em;
        }
        .table th, .table td {
            font-size: 0.85em;
            padding: 8px;
            text-align: center;
        }
        .table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        .surat-column {
        min-width: 55px;
    }
        .nav-link:hover {
    color:  black !important; /* Mengubah warna teks menjadi putih */
    font-weight: bold; 
    background-color: #007bff; /* (Optional) Menambahkan warna latar belakang biru saat hover */
}

.nav-link.active {
    color: #007bff; /* Menjaga warna teks biru untuk menu yang aktif */
}
        /* Responsiveness */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                left: -100%; /* Sidebar tersembunyi di luar layar */
                top: 0;
            }
            .sidebar.visible {
            left: 0; /* Sidebar muncul dari atas pada layar kecil */
            }
            .main-content {
                margin-left: 0;
                padding-top: 60px; /* Beri ruang di atas untuk navbar */
            }
            .sidebar a {
                font-size: 14px;
                padding: 8px 16px;
            }
            .table th, .table td {
                font-size: 0.75em;
                padding: 6px;
            }
            .table-container {
                padding: 10px;
                margin-top: 0;
            }
        }
        @media (max-width: 576px) {
            .sidebar {
                position: fixed;
            width: 100%;
            height: 100%;
            left: -100%;
            top: 0;
            }
            .main-content {
                padding-top: 60px; /* Pastikan ada ruang untuk navbar */
                margin-left: 0; 
            }
            .table th, .table td {
                font-size: 0.7em;
                padding: 5px;
            }
            .table-container {
                margin-top: 0;
                padding: 10px; /* Sesuaikan padding untuk layar kecil */
            }
        }
        .header-title {
            font-size: 1.5em;  /* Menambah ukuran font judul */
            font-weight: bold;  /* Membuat teks lebih tebal */
            margin-bottom: 20px;  /* Memberi ruang di bawah judul */
            text-align: left;  /* Menjaga teks tetap di tengah */
            color: #007bff;  /* Warna teks yang sedikit gelap untuk kontras */
        }
       
        

/* Menambahkan padding pada modal dan memperbaiki tampilan form */
.modal-content {
    padding: 20px;
}

.form-group {
    margin-bottom: 1.5rem;
}

/* Ubah warna latar belakang header modal menjadi biru pastel */
.modal-header {
    background-color: #a3c1e0; /* Warna biru pastel */
    color: white;
}

/* Ubah warna tombol close untuk menyesuaikan dengan tema */
.modal-header .close span {
    color: black;
}

.modal-body {
    color: black; /* Warna teks body modal menjadi hitam */
}
.modal-footer {
    display: flex;
    justify-content: space-between;
}

/* Menambahkan margin pada tombol Export Excel */
.btn-export {
    margin-bottom: 20px;  /* Atur spasi atas tombol */
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



    <!-- Main Content -->
    <div class="main-content" id="content">
        <div class="container mt-5">
            <div class="table-container">
            <div class="header-title">Riwayat Pengajuan Dispensasi</div>
            <!-- Tombol untuk membuka Modal -->
            <!-- Tombol untuk membuka Modal dengan kelas btn-export -->
            <button type="button" class="btn btn-primary btn-export" data-toggle="modal" data-target="#exportModal">
                Export Data
            </button>
            
                <!-- Modal untuk memilih rentang tanggal -->
                <div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exportModalLabel">Pilih Rentang Tanggal dan Penandatangan</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                                    <div class="modal-body">
                                        <form method="GET" action="riwayat_pengajuan.php">
                                    <div class="form-group mt-3">
                                        <label for="start_date">Tanggal Mulai</label>
                                        <input type="date" name="start_date" id="start_date" class="form-control" required>
                                    </div>
                                    <div class="form-group mt-3">
                                        <label for="end_date">Tanggal Selesai</label>
                                        <input type="date" name="end_date" id="end_date" class="form-control" required>
                                    </div>
                                    <div class="form-group mt-3">
                                        <label for="penandatangan" class="mb-2">Penandatangan</label>
                                        <select name="penandatangan_id" id="penandatangan" class="form-select form-control custom-select shadow-sm" required>
                                            <option value="" disabled selected>-- Pilih Penandatangan --</option>
                                            <?php
                                            $query = "SELECT id, nama FROM atasan_sekolah";
                                            $result = $conn->query($query);
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<option value=\"{$row['id']}\">{$row['nama']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="text-left mt-3">
                                        <button type="submit" class="btn btn-success btn-lg" name="export_pdf" value="true">
                                            <i class="fas fa-print"></i> Cetak Data
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Tampilkan alert jika ada -->
                <?php if (isset($_SESSION['alert'])): ?>
                    <div id="alert-box" class="alert alert-<?= $_SESSION['alert']['type']; ?> alert-dismissible fade show" role="alert">
                        <?= $_SESSION['alert']['message']; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['alert']); ?>
                <?php endif; ?>

                <div class="table-responsive">
                <table id="dispensasiTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Lengkap</th>
                                <th>NIS</th>
                                <th>Jurusan</th>
                                <th>Kelas</th>
                                <th>Keterangan</th>
                                <th>Tanggal Awal Pengajuan</th>
                                <th>Tanggal Akhir Pengajuan</th>
                                <th>Status</th>
                                <th>Action</th>
                                <th scope="col" class="surat-column">Surat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($result_pengajuan)): ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?= htmlspecialchars($row['nis']); ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['jurusan']); ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['kelas']); ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['alasan']); ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['tanggal_pengajuan']); ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['tanggal_akhir']); ?></td>
                                <td class="text-center">
                                    
                                    <?php if ($row['status'] == 'pending'): ?>
                                        <span class="status-badge status-belum-diproses">Belum diproses</span>
                                    <?php elseif ($row['status'] == 'disetujui'): ?>
                                        <span class="status-badge status-diterima">Diterima</span>
                                    <?php elseif ($row['status'] == 'ditolak'): ?>
                                        <span class="status-badge status-ditolak">Ditolak</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-buttons text-center">
                                <a href="detail_pengajuan.php?id=<?= urlencode($row['id']); ?>" class="btn btn-info btn-custom" style="text-decoration: none;">
                                    <i class="fas fa-eye"></i>
                                </a>
        <!-- Contoh tombol hapus -->
        <button class="delete-btn btn btn-danger" data-id="<?= $row['id']; ?>" data-toggle="modal" data-target="#confirmDeleteModal" title="Hapus"><i class="bi bi-trash"></i></button>





                                </td>
                                <td>
                                    <div class="text-center">
                                        <?php if (!empty($row['path_file'])): ?>
                                            <a href="<?= htmlspecialchars($row['path_file']); ?>" target="_blank" class="text-dark" style="font-size: 1.5rem; text-decoration: none;">
                                                <i class="bi bi-file-earmark-text"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge" style="background-color: orange; color: white; padding: 2px 5px; font-size: 0.8em;">Belum Ada</span>
                                        <?php endif; ?>

                                    </div>
                                </td>

                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
   <!-- Modal -->
<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Konfirmasi Penghapusan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus data ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form action="riwayat_pengajuan.php" method="POST">
                    <input type="hidden" name="delete_id" id="delete_id">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>



    <!-- jQuery, Bootstrap JS, DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script>
       document.getElementById("sidebarToggle").addEventListener("click", function() {
        const sidebar = document.getElementById("sidebar");

        if (window.innerWidth <= 768) {
            // Mode mobile: toggle dari atas
            sidebar.classList.toggle("visible");
        } else {
            // Mode desktop: toggle dari kiri
            sidebar.classList.toggle("visible");
        }
    });
    $(document).ready(function() {
            $('#dispensasiTable').DataTable();
        });
      
 // Example for setting the ID of the item to delete
// Menangani pengambilan ID untuk dihapus
$('#confirmDeleteModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Tombol yang memicu modal
    var deleteId = button.data('id'); // Ambil ID dari data-id tombol
    var modal = $(this);
    modal.find('#delete_id').val(deleteId); // Set ID yang akan dihapus ke input hidden di dalam form
});



        $(document).ready(function () {
        // Otomatis hilangkan alert setelah 5 detik
        setTimeout(function () {
            $('#alert-box').fadeOut('slow');
        }, 5000);
    });
    </script>
</body>
</html>
