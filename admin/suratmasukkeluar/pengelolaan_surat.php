<?php
include '../db.php'; // Koneksi ke database

// Periksa apakah user sudah login dan memiliki peran yang sesuai
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Jika tidak login atau bukan admin, arahkan kembali ke halaman login
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Surat</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <!-- Header -->
    <h2 class="text-primary">Manajemen Surat</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Tambah Surat</button>

    <!-- Tabel Surat -->
    <table class="table table-bordered table-hover">
        <thead class="table-primary">
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>No Surat</th>
                <th>Jenis Surat</th>
                <th>Perihal</th>
                <th>Lampiran</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <!-- Isi tabel dinamis dari database -->
            <?php
            include 'db.php'; // Database Connection
            $result = mysqli_query($conn, "SELECT * FROM surat ORDER BY id DESC");
            $no = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>{$no}</td>
                        <td>{$row['tanggal']}</td>
                        <td>{$row['no_surat']}</td>
                        <td>{$row['jenis_surat']}</td>
                        <td>{$row['perihal']}</td>
                        <td><a href='uploads/{$row['lampiran']}' target='_blank' class='btn btn-success btn-sm'>Buka Lampiran</a></td>
                        <td>
                        <a href='edit_surat.php?id={$row['id']}' class='btn btn-warning btn-sm'>Edit</a>
                        <a href='delete_surat.php?id={$row['id']}' onclick='return confirm('Apakah Anda yakin ingin menghapus data ini?');' class='btn btn-danger btn-sm'>Delete</a>
</td>


                    </tr>";
                $no++;
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Modal Tambah Surat -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Tambah Surat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="tambah_surat.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tanggal" class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="no_surat" class="form-label">No Surat</label>
                        <input type="text" name="no_surat" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="jenis_surat" class="form-label">Jenis Surat</label>
                        <select name="jenis_surat" class="form-control" required>
                            <option value="Surat Masuk">Surat Masuk</option>
                            <option value="Surat Keluar">Surat Keluar</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="perihal" class="form-label">Perihal</label>
                        <input type="text" name="perihal" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="lampiran" class="form-label">Lampiran (PDF)</label>
                        <input type="file" name="lampiran" class="form-control" accept=".pdf" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Tambah Surat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Popper.js (untuk Bootstrap modal) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

</body>
</html>
