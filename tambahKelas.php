<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kelas = $conn->real_escape_string($_POST['kelas']);

    if (!empty($kelas)) {
        $query = "INSERT INTO kelas (kelas) VALUES ('$kelas')";
        if ($conn->query($query)) {
            echo json_encode(['status' => 'success', 'message' => 'Kelas berhasil ditambahkan']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menambah kelas']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Nama kelas tidak boleh kosong']);
    }
}
?>
