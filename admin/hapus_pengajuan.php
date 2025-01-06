<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Check if an ID is passed via GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare and execute the delete query
    $query = "DELETE FROM pengajuan WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    // If the query was successful, redirect to the previous page
    if ($stmt->execute()) {
        // Optional: you can add a success message using session or redirect to the list page
        $_SESSION['message'] = 'Data berhasil dihapus';
        header("Location: riwayat_pengajuan.php"); // Redirect back to the list page
        exit;
    } else {
        // If there was an error deleting the data
        $_SESSION['error'] = 'Gagal menghapus data';
        header("Location: riwayat_pengajuan.php"); // Redirect back to the list page
        exit;
    }
} else {
    // Redirect to the list page if no ID is passed
    header("Location: riwayat_pengajuan.php");
    exit;
}
?>
