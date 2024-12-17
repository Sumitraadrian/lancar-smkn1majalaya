<?php
session_start();
include 'db.php';
$currentPage = basename($_SERVER['PHP_SELF']);

require('libs/fpdf.php'); // Include the FPDF library

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
   
    // Query to get all the pengajuan data
    $query_pengajuan = "SELECT * FROM pengajuan ORDER BY tanggal_pengajuan DESC";
    $result_pengajuan = mysqli_query($conn, $query_pengajuan);
} else {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit;
}

if (isset($_GET['export_pdf']) && $_GET['export_pdf'] == 'true') {
    // Get the date range from the form
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

    // Build the query based on the date range
    $query_pengajuan = "SELECT * FROM pengajuan WHERE tanggal_pengajuan BETWEEN '$start_date' AND '$end_date' ORDER BY tanggal_pengajuan DESC";
    $result_pengajuan = mysqli_query($conn, $query_pengajuan);

    // PDF generation logic
    $pdf = new FPDF('L', 'mm', 'A4'); // Set orientation to 'L' for Landscape
    $pdf->AddPage();

    // Set document title
    $pdf->SetFont('Times', 'B', 16);
    $pdf->Cell(0, 10, 'Riwayat Pengajuan Dispensasi Siswa', 0, 1, 'C');
    $pdf->Cell(0, 10, 'SMK Negeri 1 Majalaya', 0, 1, 'C');
    $pdf->Ln(10); // Line break

    // Table headers with smaller column widths and centered text
    $pdf->SetFont('Times', 'B', 10); // Use a smaller font size for headers
    $pdf->Cell(8, 10, '#', 1, 0, 'C');        // Center the text in the first column
    $pdf->Cell(30, 10, 'Nama Lengkap', 1, 0, 'C'); 
    $pdf->Cell(25, 10, 'NIS', 1, 0, 'C');     
    $pdf->Cell(30, 10, 'Jurusan', 1, 0, 'C');
    $pdf->Cell(30, 10, 'Kelas', 1, 0, 'C');
    $pdf->Cell(50, 10, 'Keterangan', 1, 0, 'C');
    $pdf->Cell(35, 10, 'Tanggal Pengajuan', 1, 0, 'C');
    $pdf->Cell(35, 10, 'Tanggal Akhir', 1, 0, 'C');
    $pdf->Cell(25, 10, 'Status', 1, 0, 'C');
    $pdf->Ln();

    // Fetch the data from the database and populate the table
    $pdf->SetFont('Times', '', 9); // Use a smaller font size for the table content
    $no = 1;
    while ($row = mysqli_fetch_assoc($result_pengajuan)) {
        $pdf->Cell(8, 10, $no++, 1, 0, 'C');
        $pdf->Cell(30, 10, htmlspecialchars($row['nama_lengkap']), 1, 0, 'C');
        $pdf->Cell(25, 10, htmlspecialchars($row['nis']), 1, 0, 'C');
        $pdf->Cell(30, 10, htmlspecialchars($row['jurusan']), 1, 0, 'C');
        $pdf->Cell(30, 10, htmlspecialchars($row['kelas']), 1, 0, 'C');
        $pdf->Cell(50, 10, htmlspecialchars($row['alasan']), 1, 0, 'C');
        $pdf->Cell(35, 10, htmlspecialchars($row['tanggal_pengajuan']), 1, 0, 'C');
        $pdf->Cell(35, 10, htmlspecialchars($row['tanggal_akhir']), 1, 0, 'C');

        // Add status
        $status = '';
        if ($row['status'] == 'pending') {
            $status = 'Belum diproses';
        } elseif ($row['status'] == 'disetujui') {
            $status = 'Diterima';
        } elseif ($row['status'] == 'ditolak') {
            $status = 'Ditolak';
        }
        $pdf->Cell(25, 10, $status, 1, 0, 'C');
        $pdf->Ln();
    }

    // Output the PDF to the browser
    $pdf->Output();
    exit;
}
?>




?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Kajur</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/logoweb.png">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            background-color: #495057;
        }
        .sidebar.visible {
        left: 0; /* Sidebar muncul dari kiri */
    }
        
        .main-content {
            
            margin-left: 250px; /* Beri ruang agar konten tidak tertutup sidebar */
        padding: 20px;
        transition: margin-left 0.3s ease;
        z-index: 1;
        padding-top: 60px; 
        }
        .status-badge {
            padding: 3px 8px;
            font-size: 0.8em;
        }
        .status-belum-diproses {
            background-color: orange;
            color: white;
        }
        .status-diterima {
            background-color: green;
            color: white;
        }
        .status-ditolak {
            background-color: red;
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
            margin-top: 0; /* Pastikan tidak ada margin di atas tabel */
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
    color: #333;  /* Warna teks yang sedikit gelap untuk kontras */
}
.status-badge {
    padding: 3px 8px;
    font-size: 0.8em;
}

.status-belum-diproses {
    background-color: orange;
    color: white;
}

.status-diterima {
    background-color: green;
    color: white;
}

.status-ditolak {
    background-color: red;
    color: white;
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
            <a class="nav-link d-flex align-items-center text-dark" href="riwayat_pengajuan.php" style="color: black;">
                <i class="bi bi-file-earmark-text me-2"></i> Riwayat Pengajuan
            </a>
            <a class="nav-link d-flex align-items-center text-dark" href="logout.php" style="color: black;">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
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
                                <h5 class="modal-title" id="exportModalLabel">Pilih Rentang Tanggal</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                            <form method="GET" action="riwayat_pengajuan.php">
                                <div class="form-group">
                                    <label for="start_date">Tanggal Mulai</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="end_date">Tanggal Selesai</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-success mt-3" name="export_pdf" value="true">Cetak Data</button>
                            </form>

                            </div>
                        </div>
                    </div>
                </div>
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


                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery, Bootstrap JS, DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
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
    </script>
</body>
</html>
