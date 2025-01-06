<?php
session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
include '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$query = "SELECT id, nama, nip, pangkat, golongan, jabatan, instansi FROM atasan_sekolah";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LANCAR - Atasan Sekolah</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://kit.fontawesome.com/YOUR_KIT_CODE.js" crossorigin="anonymous"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <link rel="icon" type="image/png" href="../image/logowebsite.png">
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
            left: 0;
            width: 250px;
            z-index: 100;
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
        .dashboard-header {
            width: calc(100% - 250px); /* Set to adjust with sidebar width */
            padding: 120px;
            border-radius: 0;
            background-color: #4472c4;
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
            margin-left: 250px; /* Offset by sidebar width */
            justify-content: space-between;
            display: flex;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            margin-top: 150px; /* Adjust for dashboard header */
            min-height: calc(100vh - 56px); 
        }
        .status-badge {
            padding: 3px 8px; /* Mengurangi padding badge status */
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
            padding: 6px 12px;
        font-size: 0.9rem;
        
        transition: all 0.3s ease;

        }
        .btn, .btn-sm{
            border-radius: 10px;
        }
        .btn-custom:hover {
        transform: scale(1.05);
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
    }
        .table-container {
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        }
        .table thead th {
            background: #a3c1e0;
            color: black;
            font-size: 1rem;
            text-transform: uppercase;
        }
        .table th, .table td {
            font-size: 0.85em; /* Mengecilkan ukuran font */
            padding: 8px; /* Mengurangi padding untuk membuat tabel lebih ringkas */
            text-align: center;
        }
        .table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        .table tbody tr:hover {
        background-color: #f1f1f1;
        cursor: pointer;
    }

        .main-content {
            margin-left: 20px;
            padding: 20px;
            margin-top: 20px; /* Adjust for dashboard header */
            min-height: calc(100vh - 56px); 
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
        .modal-content {
        border-radius: 15px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        background: linear-gradient(135deg, #007bff, #a3c1e0);
        color: white;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }

    .modal-footer .btn {
        border-radius: 10px;
    }

    .modal-footer .btn-primary {
        background-color: #28a745;
        border-radius: 15px; 
        border: none;
    }

    .modal-footer .btn-primary:hover {
        background-color: #218838;
    }
    .btn-secondary{
    border-radius: 10px; 
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
                
                <a class="nav-link d-flex align-items-center <?= $currentPage == 'list_atasan.php' ? 'active' : '' ?>" href="list_atasan.php" style="color: <?= $currentPage == 'list_atasan.php' ? '#007bff' : 'black'; ?>;">
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
                <a class="nav-link d-flex align-items-center <?= $currentPage == '../suratmasukkeluar/surat.php' ? 'active' : '' ?>" href="../suratmasukkeluar/surat.php" style="color: <?= $currentPage == '../suratmasukkeluar/surat.php' ? '#007bff' : 'black'; ?>;">
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

    <div class="main-content" id="content">
        <div class="container mt-5">
            <div class="table-container">
               
                <div class="header-title">
                    List Data Atasan Sekolah
                    <a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#tambahGuruModal" style="float: right; margin-bottom: 10px;">Tambah Atasan</a>
                    <div id="alert-container" class="mt-3"></div>

                </div>
                <div class="table-responsive">
                    <table id="dosenTable" class="table table-bordered table-hover">
                    <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Atasan</th>
                                <th>NIP</th>
                                <th>Pangkat</th>
                                <th>Golongan</th>
                                <th>Jabatan</th>
                                <th>Instansi</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['nama']); ?></td>
                                <td><?= htmlspecialchars($row['nip']); ?></td>
                                <td><?= htmlspecialchars($row['pangkat']); ?></td>
                                <td><?= htmlspecialchars($row['golongan']); ?></td>
                                <td><?= htmlspecialchars($row['jabatan']); ?></td>
                                <td><?= htmlspecialchars($row['instansi']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-primary btn-edit" 
                                                data-id="<?= $row['id']; ?>" 
                                                data-nama="<?= htmlspecialchars($row['nama']); ?>" 
                                                data-nip="<?= htmlspecialchars($row['nip']); ?>" 
                                                data-pangkat="<?= htmlspecialchars($row['pangkat']); ?>" 
                                                data-golongan="<?= htmlspecialchars($row['golongan']); ?>" 
                                                data-jabatan="<?= htmlspecialchars($row['jabatan']); ?>" 
                                                data-instansi="<?= htmlspecialchars($row['instansi']); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm btn-custom delete-button" 
                                                data-id="<?= $row['id']; ?>" 
                                                data-toggle="modal" 
                                                data-target="#modalHapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="tambahGuruModal" tabindex="-1" aria-labelledby="tambahGuruModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tambahGuruModalLabel">Tambah Guru Piket</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="tambahKelasForm">
                            <div class="form-group">
                                <label for="guru">Nama Atasan</label>
                                <input type="text" id="guru" name="guru" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="nip">NIP</label>
                                <input type="text" id="nip" name="nip" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="pangkat">Pangkat</label>
                                <input type="text" id="pangkat" name="pangkat" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="golongan">Golongan</label>
                                <input type="text" id="golongan" name="golongan" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="jabatan">Jabatan</label>
                                <input type="text" id="jabatan" name="jabatan" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="instansi">Instansi</label>
                                <input type="text" id="instansi" name="instansi" class="form-control" required>
                            </div>
                            <div id="error-message" class="text-danger mt-2" style="display: none;"></div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-success">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="editGuruModal" tabindex="-1" aria-labelledby="editGuruModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editGuruModalLabel">Edit Atasan Sekolah</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editGuruForm">
                            <input type="hidden" id="editId" name="id">
                            <div class="form-group">
                                <label for="editNama">Nama Atasan</label>
                                <input type="text" id="editNama" name="nama" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="editNip">NIP</label>
                                <input type="text" id="editNip" name="nip" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="editPangkat">Pangkat</label>
                                <input type="text" id="editPangkat" name="pangkat" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="editGolongan">Golongan</label>
                                <input type="text" id="editGolongan" name="golongan" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="editJabatan">Jabatan</label>
                                <input type="text" id="editJabatan" name="jabatan" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="editInstansi">Instansi</label>
                                <input type="text" id="editInstansi" name="instansi" class="form-control" required>
                            </div>
                            <div id="error-message" class="text-danger mt-2" style="display: none;"></div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-success">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal Konfirmasi Hapus -->
        <div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalHapusLabel">Konfirmasi Hapus</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Apakah Anda yakin ingin menghapus data ini?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" id="confirmDelete" class="btn btn-danger">Hapus</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- DataTables JS -->
        <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        <!-- Bootstrap JS -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
       
        <script>
            $(document).ready(function() {
                $('#dosenTable').DataTable({
                    "pagingType": "simple_numbers",
                    "lengthMenu": [10, 25, 50, 100],
                    "language": {
                        "search": "Search:",
                        "lengthMenu": "Show _MENU_ entries",
                        "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                        "paginate": {
                            "previous": "Previous",
                            "next": "Next"
                        }
                    },
                    "columnDefs": [
                        { "orderable": false, "targets": 0 } // Disable sorting for the first column (no.)
                    ]
                });
            });

            document.getElementById("sidebarToggle").addEventListener("click", function() {
                document.getElementById("sidebar").classList.toggle("collapsed");
                document.getElementById("content").classList.toggle("expanded");
            });

            document.getElementById('tambahKelasForm').addEventListener('submit', function(event) {
                event.preventDefault(); // Mencegah form submit default

                const formData = new FormData(this);
                fetch('tambah_atasan.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    const alertContainer = document.getElementById('alert-container');
                    alertContainer.innerHTML = ''; // Hapus pesan lama

                    const alertDiv = document.createElement('div');
                    alertDiv.className = `alert ${data.status === 'success' ? 'alert-success' : 'alert-danger'}`;
                    alertDiv.textContent = data.message;

                    alertContainer.appendChild(alertDiv);

                    // Bersihkan form jika berhasil
                    if (data.status === 'success') {
                        document.getElementById('tambahKelasForm').reset();
                        $('#tambahGuruModal').modal('hide');
                        setTimeout(() => location.reload(), 2000); // Reload halaman setelah 2 detik
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
           
                // Tangkap tombol edit
                $('.btn-edit').on('click', function () {
                    const id = $(this).data('id');
                    const nama = $(this).data('nama');
                    const nip = $(this).data('nip');
                    const pangkat = $(this).data('pangkat');
                    const golongan = $(this).data('golongan');
                    const jabatan = $(this).data('jabatan');
                    const instansi = $(this).data('instansi');

                    $('#editId').val(id);
                    $('#editNama').val(nama);
                    $('#editNip').val(nip);
                    $('#editPangkat').val(pangkat);
                    $('#editGolongan').val(golongan);
                    $('#editJabatan').val(jabatan);
                    $('#editInstansi').val(instansi);

                    $('#editGuruModal').modal('show');
                });
        
            // Tangani submit form edit
            document.getElementById('editGuruForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch('edit_atasan.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const alertContainer = document.getElementById('alert-container');
                    alertContainer.innerHTML = ''; // Clear previous alerts

                    const alertDiv = document.createElement('div');
                    alertDiv.className = `alert ${data.status === 'success' ? 'alert-success' : 'alert-danger'}`;
                    alertDiv.textContent = data.message;

                    alertContainer.appendChild(alertDiv);

                    // Reload page after successful update
                    if (data.status === 'success') {
                        $('#editGuruModal').modal('hide');
                        setTimeout(() => location.reload(), 2000); // Reload page after 2 seconds
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memperbarui data.');
                });
            });

            $(document).ready(function() {
    let deleteId;

    // Saat tombol delete diklik
    $('.delete-button').on('click', function() {
        deleteId = $(this).data('id'); // Menyimpan ID yang akan dihapus
        $('#modalHapus').modal('show'); // Menampilkan modal konfirmasi
    });

    // Saat tombol hapus diklik di modal konfirmasi
    $('#confirmDelete').on('click', function() {
        // Kirim permintaan ke server untuk menghapus data
        fetch('hapus_atasan.php', {
            method: 'POST',
            body: JSON.stringify({ id: deleteId }),
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const alertContainer = document.getElementById('alert-container');
            alertContainer.innerHTML = ''; // Hapus pesan lama

            // Menampilkan pesan pemberitahuan
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert ${data.status === 'success' ? 'alert-success' : 'alert-danger'}`;
            alertDiv.textContent = data.message;
            alertContainer.appendChild(alertDiv);

            // Menutup modal dan me-refresh halaman setelah 2 detik jika sukses
            if (data.status === 'success') {
                $('#modalHapus').modal('hide');
                setTimeout(() => location.reload(), 2000); // Reload halaman setelah 2 detik
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus data.');
        });
    });
});

        </script>
    </body>
</html>
