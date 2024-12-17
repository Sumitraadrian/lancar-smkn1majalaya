<?php
include 'db.php';

if (isset($_POST['id']) && isset($_POST['kelas'])) {
    $id = $_POST['id'];
    $kelas = $_POST['kelas'];

    // Update kelas in the database
    $query = "UPDATE kelas SET kelas = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $kelas, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Kelas updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating kelas.']);
    }
    $stmt->close();
}
?>
