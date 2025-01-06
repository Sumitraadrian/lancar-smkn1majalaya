<?php
include '../db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "DELETE FROM surat WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data berhasil dihapus'); window.location = 'surat.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
