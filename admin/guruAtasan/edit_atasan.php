<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $nip = $_POST['nip'];
    $pangkat = $_POST['pangkat'];
    $golongan = $_POST['golongan'];
    $jabatan = $_POST['jabatan'];
    $instansi = $_POST['instansi'];

    $query = "UPDATE atasan_sekolah SET nama = ?, nip = ?, pangkat = ?, golongan = ?, jabatan = ?, instansi = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssssi', $nama, $nip, $pangkat, $golongan, $jabatan, $instansi, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil diperbarui']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui data']);
    }

    $stmt->close();
    $conn->close();
}
?>
