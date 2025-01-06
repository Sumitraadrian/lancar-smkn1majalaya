<?php
session_start(); // Start the session

include '../db.php'; // Koneksi ke database

// Periksa apakah user sudah login dan memiliki peran yang sesuai
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Jika tidak login atau bukan admin, arahkan kembali ke halaman login
    header('Location: ../index.php');
    exit();
}

// Cek apakah form dikirimkan menggunakan metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $tanggal     = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $no_surat    = mysqli_real_escape_string($conn, $_POST['no_surat']);
    $jenis_surat = mysqli_real_escape_string($conn, $_POST['jenis_surat']);
    $perihal     = mysqli_real_escape_string($conn, $_POST['perihal']);

    // Cek apakah file lampiran diunggah
    if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] === UPLOAD_ERR_OK) {
        $file_tmp   = $_FILES['lampiran']['tmp_name'];
        $file_name  = basename($_FILES['lampiran']['name']);
        $file_ext   = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validasi file PDF
        if ($file_ext === 'pdf') {
            $upload_dir = 'uploads/';
            // Pastikan folder uploads ada, jika tidak buat
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Beri nama unik pada file
            $new_file_name = uniqid('lampiran_', true) . '.' . $file_ext;
            $target_file   = $upload_dir . $new_file_name;

            // Pindahkan file ke direktori uploads
            if (move_uploaded_file($file_tmp, $target_file)) {
                // Query untuk menyimpan data ke database
                $query = "INSERT INTO surat (tanggal, no_surat, jenis_surat, perihal, lampiran)
                          VALUES ('$tanggal', '$no_surat', '$jenis_surat', '$perihal', '$new_file_name')";

                if (mysqli_query($conn, $query)) {
                    // Redirect kembali ke halaman utama dengan pesan sukses
                    header("Location: surat.php?status=success");
                    exit();
                } else {
                    echo "Error: " . mysqli_error($conn);
                }
            } else {
                echo "Error: Gagal mengunggah file lampiran.";
            }
        } else {
            echo "Error: Hanya file PDF yang diizinkan.";
        }
    } else {
        echo "Error: Lampiran tidak diunggah atau terjadi kesalahan.";
    }
} else {
    echo "Error: Akses tidak diizinkan.";
}
?>
