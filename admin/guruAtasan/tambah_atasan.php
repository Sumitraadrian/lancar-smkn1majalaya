<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['guru'];
    $nip = $_POST['nip'];
    $pangkat = $_POST['pangkat'];
    $golongan = $_POST['golongan'];
    $jabatan = $_POST['jabatan'];
    $instansi = $_POST['instansi'];

    // Validasi input
    if (empty($nama) || empty($nip) || empty($pangkat) || empty($golongan) || empty($jabatan) || empty($instansi)) {
        echo json_encode(['status' => 'error', 'message' => 'Semua kolom wajib diisi.']);
        exit();
    }

    // Insert data ke tabel atasan_sekolah
    $stmt = $conn->prepare("INSERT INTO atasan_sekolah (nama, nip, pangkat, golongan, jabatan, instansi) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $nama, $nip, $pangkat, $golongan, $jabatan, $instansi);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil ditambahkan.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan data.']);
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>
