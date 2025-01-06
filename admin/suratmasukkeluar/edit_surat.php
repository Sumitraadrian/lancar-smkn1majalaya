<?php
include '../db.php';
$currentPage = basename($_SERVER['PHP_SELF']);
// Ambil data berdasarkan ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM surat WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
}

// Proses Update Data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $tanggal = $_POST['tanggal'];
    $no_surat = $_POST['no_surat'];
    $jenis_surat = $_POST['jenis_surat'];
    $perihal = $_POST['perihal'];

    $query = "UPDATE surat SET 
                tanggal = '$tanggal',
                no_surat = '$no_surat',
                jenis_surat = '$jenis_surat',
                perihal = '$perihal'
              WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data berhasil diperbarui'); window.location = 'surat.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lancar - Edit Surat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../image/logowebsite.png">
</head>
<style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 100vh;
            background-color: #fff;
            color: white;
            padding-top: 20px;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1;
            transition: transform 0.3s ease;
        }
        .navbar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .sidebar h5 {
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
        
        .main-content {
    margin-left: 200px;
    padding: 20px;
    margin-top: 30px; /* Tambahkan ruang untuk navbar */
    min-height: calc(100vh - 56px); /* Adjust dengan tinggi navbar */
}

.status-badge {
    padding: 3px 8px; /* Mengurangi padding badge status */
    font-size: 0.8em; /* Mengecilkan ukuran font badge */
}

.status-belum-diproses {
    background-color:rgb(255, 150, 69);
    color: black;
    border-radius: 12px; /* Membuat sisi lebih bulat */
    padding: 3px 6px; /* Menyesuaikan padding untuk badge */
    font-weight: bold;
}

.status-diterima {
    background-color:rgb(89, 210, 105);
    color: black;
    border-radius: 12px; /* Membuat sisi lebih bulat */
    padding: 3px 8px; /* Menyesuaikan padding untuk badge */
    font-weight: bold;
}

.status-ditolak {
    background-color:rgb(255, 83, 83);
    color: black;
    border-radius: 12px; /* Membuat sisi lebih bulat */
    padding: 3px 8px; /* Menyesuaikan padding untuk badge */
    font-weight: bold;
}


.action-buttons {
    display: flex;
    gap: 5px;
}

.btn-custom {
    padding: 3px 5px; /* Mengecilkan ukuran tombol aksi */
    font-size: 0.85em; /* Mengecilkan ukuran font tombol */
    border-radius: 3px;
}

.table-container {
    padding: 15px; /* Mengurangi padding untuk tabel */
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    margin-top: 100px;
}

.table thead th {
    background-color: #a3c1e0;
    color: black !important;
    font-size: 0.9em; /* Menyesuaikan ukuran font header tabel */
}

.table th, .table td {
    font-size: 0.85em; /* Mengecilkan ukuran font tabel */
    padding: 6px; /* Mengurangi padding untuk membuat tabel lebih ringkas */
    text-align: center;
}

.table tbody tr:nth-child(odd) {
    background-color: #f9f9f9;
}

        .sidebar.collapsed {
            transform: translateX(-100%);
        }
        .content-wrapper {
            margin-left: 250px;
            padding-top: 60px;
            transition: margin-left 0.3s ease;
        }
        .content-wrapper.expanded {
            margin-left: 0;
        }

        .header-title {
            font-size: 1.5em;
            color: #007bff;
            font-weight: bold;
            text-align: left;
            margin-bottom: 15px;
        }
        .nav-link:hover {
    color:  black !important; /* Mengubah warna teks menjadi putih */
    font-weight: bold; 
    background-color: #007bff; /* (Optional) Menambahkan warna latar belakang biru saat hover */
}

.nav-link.active {
    color: #007bff; /* Menjaga warna teks biru untuk menu yang aktif */
}
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
                <a class="nav-link d-flex align-items-center <?= $currentPage == '../dashboard_admin.php' ? 'active' : '' ?>" href="../dashboard_admin.php" style="color: <?= $currentPage == '../dashboard_admin.php' ? '#007bff' : 'black'; ?>;">
                    <i class="bi bi-activity" style="margin-right: 15px;"></i> Dashboard
                </a>
                <a class="nav-link d-flex align-items-center <?= $currentPage == '../list_pengajuan.php' ? 'active' : '' ?>" href="../list_pengajuan.php" style="color: <?= $currentPage == '../list_pengajuan.php' ? '#007bff' : 'black'; ?>;">
                    <i class="bi bi-file-earmark-plus" style="margin-right: 15px;"></i> Daftar Pengajuan
                </a>
                <a class="nav-link d-flex align-items-center <?= $currentPage == '../list_tolakPengajuan.php' ? 'active' : '' ?>" href="../list_tolakPengajuan.php" style="color: <?= $currentPage == '../list_tolakPengajuan.php' ? '#007bff' : 'black'; ?>;">
                    <i class="bi bi-x-circle" style="margin-right: 15px;"></i> Daftar Pengajuan Ditolak
                </a>
                <a class="nav-link d-flex align-items-center <?= $currentPage == '../riwayat_pengajuan.php' ? 'active' : '' ?>" href="../riwayat_pengajuan.php" style="color: <?= $currentPage == '../riwayat_pengajuan.php' ? '#007bff' : 'black'; ?>;">
                    <i class="bi bi-archive" style="margin-right: 15px;"></i> Riwayat Pengajuan
                </a>
                
                <a class="nav-link d-flex align-items-center <?= $currentPage == '../guruAtasan/list_atasan.php' ? 'active' : '' ?>" href="../guruAtasan/list_atasan.php" style="color: <?= $currentPage == '../guruAtasan/list_atasan.php' ? '#007bff' : 'black'; ?>;">
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
                <a class="nav-link d-flex align-items-center <?= ($currentPage == 'surat.php' || $currentPage == 'edit_surat.php') ? 'active' : '' ?>" 
                href="surat.php" 
                style="color: <?= ($currentPage == 'surat.php' || $currentPage == 'edit_surat.php') ? '#007bff' : 'black'; ?>;">
                    <i class="bi bi-envelope" style="margin-right: 15px;"></i> Surat Masuk/Keluar
                </a>
            </nav>
        </div>

    </div>

    <!-- Pengaturan dan Logout -->
    <small class="text-muted ms-2 mt-4">Pengaturan</small>
    <nav class="nav flex-column mt-2">
        <a class="nav-link d-flex align-items-center <?= $currentPage == '../pengaturan_admin.php' ? 'active' : '' ?>" href="../pengaturan_admin.php" style="color: <?= $currentPage == '../pengaturan_admin.php' ? '#007bff' : 'black'; ?>;">
            <i class="bi bi-gear" style="margin-right: 15px;"></i> Pengaturan Akun
        </a>
        <a class="nav-link d-flex align-items-center <?= $currentPage == '../logout.php' ? 'active' : '' ?>" href="../logout.php" style="color: <?= $currentPage == '../logout.php' ? '#007bff' : 'black'; ?>;">
            <i class="bi bi-box-arrow-right" style="margin-right: 15px;"></i> Logout
        </a>
    </nav>
</div>
<div class="main-content">
<div class="container mt-4">
<div class="table-container">
    <h2 class="text-primary">Edit Surat</h2>
    <form action="edit_surat.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <div class="mb-3">
            <label for="tanggal" class="form-label">Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="<?php echo $row['tanggal']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="no_surat" class="form-label">No Surat</label>
            <input type="text" name="no_surat" class="form-control" value="<?php echo $row['no_surat']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="jenis_surat" class="form-label">Jenis Surat</label>
            <select name="jenis_surat" class="form-control" required>
                <option value="Surat Masuk" <?php if ($row['jenis_surat'] == 'Surat Masuk') echo 'selected'; ?>>Surat Masuk</option>
                <option value="Surat Keluar" <?php if ($row['jenis_surat'] == 'Surat Keluar') echo 'selected'; ?>>Surat Keluar</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="perihal" class="form-label">Perihal</label>
            <input type="text" name="perihal" class="form-control" value="<?php echo $row['perihal']; ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="surat.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>
</div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- DataTables JS -->
        <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        <!-- Bootstrap JS -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

<script>
   

    function handleDelete(id) {
        if (confirm("Apakah Anda yakin ingin menghapus surat ini?")) {
            window.location.href = "hapus_surat.php?id=" + id;
        }
    }
    document.getElementById("sidebarToggle").addEventListener("click", function() {
        document.getElementById("sidebar").classList.toggle("collapsed");
        document.getElementById("content").classList.toggle("expanded");
    });
</script>
</body>
</html>
