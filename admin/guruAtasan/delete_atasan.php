<?php
include '../db.php';
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    $id = $data['id'];

    $query = "DELETE FROM atasan_sekolah WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan saat menghapus data.']);
    }

    $stmt->close();
}
?>
