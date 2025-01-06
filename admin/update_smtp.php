<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $smtp_host = $_POST['smtp_host'];
    $smtp_port = $_POST['smtp_port'];
    $smtp_username = $_POST['smtp_username'];
    $smtp_password = $_POST['smtp_password'];
    $smtp_secure = $_POST['smtp_secure'];

    $query = "UPDATE smtp_config SET  smtp_username = ?, smtp_password = ? WHERE id = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sisss",  $smtp_username, $smtp_password);
    $stmt->execute();

    header('Location: detail_pengajuan.php?status=success');
    exit();
}
?>
