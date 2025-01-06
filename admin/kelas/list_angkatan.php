<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$query = "SELECT * FROM kelas ORDER BY kelas ASC";
$result = $conn->query($query);



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LANCAR - Kelas</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
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
            left: 0;
            width: 250px;
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
            background-color: #495057;
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
            padding: 3px 5px; /* Mengecilkan ukuran tombol aksi */
            font-size: 0.85em;
            border-radius: 3px;
        }
        .table-container {
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .table thead th {
            background-color: #a3c1e0;
            color: black !important;;
            font-size: 0.9em; /* Menyesuaikan ukuran font header tabel */
        }
        .table th, .table td {
            font-size: 0.85em; /* Mengecilkan ukuran font */
            padding: 8px; /* Mengurangi padding untuk membuat tabel lebih ringkas */
            text-align: center;
        }
        .table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
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
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <!-- Tambahkan menu lain di sini jika diperlukan -->
            </ul>
        </div>
    </div>
</nav>

    <!-- Sidebar -->
    <div class="sidebar bg-light p-3" id="sidebar">
        <h4 class="text-center">LANCAR</h4>
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

    <div class="main-content" id="content">
        <div class="container mt-5">
            <div class="table-container">
                <div class="header-title">
                    List Data Kelas
                    <a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#tambahKelasModal" style="float: right; margin-bottom: 10px;">Tambah Kelas</a>

                </div>
                <div class="table-responsive">
                <table id="kelasTable" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kelas</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['kelas']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
<!-- Modal Tambah Kelas -->
<div class="modal fade" id="tambahKelasModal" tabindex="-1" aria-labelledby="tambahKelasModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahKelasModalLabel">Tambah Kelas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="tambahKelasForm">
                    <div class="form-group">
                        <label for="kelas">Nama Kelas</label>
                        <input type="text" id="kelas" name="kelas" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
                <div id="error-message" class="text-danger mt-2" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>
<!-- Modal Edit Kelas -->
<!-- Modal Edit Kelas -->
<div class="modal fade" id="editKelasModal" tabindex="-1" aria-labelledby="editKelasModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editKelasModalLabel">Edit Kelas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editKelasForm">
                    <div class="form-group">
                        <label for="editKelas">Nama Kelas</label>
                        <input type="text" id="editKelas" name="kelas" class="form-control" required>
                        <input type="hidden" id="editKelasId" name="id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
                <div id="edit-error-message" class="text-danger mt-2" style="display: none;"></div>
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
<script>
    $(document).ready(function() {
        $('#kelasTable').DataTable({
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
            }
        });
    });

    document.getElementById("sidebarToggle").addEventListener("click", function() {
        document.getElementById("sidebar").classList.toggle("collapsed");
        document.getElementById("content").classList.toggle("expanded");
    });


function openModal() {
    document.getElementById('tambahKelasModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('tambahKelasModal').style.display = 'none';
}
$(document).ready(function() {
    // Fungsi untuk memuat ulang data kelas dari server setelah kelas ditambahkan
    function loadKelasTable() {
        $.ajax({
            url: 'loadkelas.php', // PHP file untuk memuat data dari database
            type: 'GET',
            success: function(response) {
                $('#kelasTable tbody').html(response); // Update isi tabel dengan data baru
            }
        });
    }

    // Mengirim data form untuk ditambahkan ke database
    $('#tambahKelasForm').on('submit', function(event) {
        event.preventDefault(); // Menghentikan pengiriman form biasa

        var kelas = $('#kelas').val(); // Ambil nilai kelas

        $.ajax({
            url: 'tambahKelas.php', // PHP file untuk menangani penambahan data
            type: 'POST',
            data: { kelas: kelas },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#error-message').hide();
                    // Tutup modal
                    $('#tambahKelasModal').modal('hide');
                    // Bersihkan input
                    $('#kelas').val('');
                    // Tampilkan pesan sukses (opsional)
                    alert(response.message);
                    // Muat ulang data kelas
                    loadKelasTable();
                } else {
                    $('#error-message').text(response.message).show();
                }
            },
            error: function() {
                $('#error-message').text('Terjadi kesalahan, coba lagi').show();
            }
        });
    });

    // Memuat data kelas pada saat pertama kali halaman dimuat
    loadKelasTable();
});
$(document).ready(function() {
    // Open the Edit Modal and populate with data
    $('#kelasTable').on('click', '.btn-warning', function() {
        var id = $(this).data('id');
        var kelas = $(this).data('kelas');
        
        // Populate modal fields with data
        $('#editKelasId').val(id);
        $('#editKelas').val(kelas);
    });

    // Handle the edit form submission
    $('#editKelasForm').on('submit', function(event) {
        event.preventDefault();

        var id = $('#editKelasId').val();
        var kelas = $('#editKelas').val();

        $.ajax({
            url: 'editKelas.php', // PHP file to handle updating data
            type: 'POST',
            data: { id: id, kelas: kelas },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#error-message').hide();
                    // Close the modal
                    $('#editKelasModal').modal('hide');
                    // Update the table with new data
                    loadKelasTable();
                    alert(response.message);
                } else {
                    $('#error-message').text(response.message).show();
                }
            },
            error: function() {
                $('#error-message').text('Terjadi kesalahan, coba lagi').show();
            }
        });
    });
     // Memuat data kelas pada saat pertama kali halaman dimuat
     loadKelasTable();
});


</script>

</body>
</html>
