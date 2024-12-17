<?php
include 'db.php';

// Mengambil data kelas dari database
$query = "SELECT * FROM kelas ORDER BY kelas ASC";
$result = $conn->query($query);

// Menampilkan data kelas ke dalam tabel
$no = 1;
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td class='text-center'>{$no}</td>";
    echo "<td>" . htmlspecialchars($row['kelas']) . "</td>";
    echo "<td class='text-center'>
            <a href='#' class='btn btn-warning btn-sm' data-toggle='modal' data-target='#editKelasModal' data-id='{$row['id']}' data-kelas='{$row['kelas']}'>Edit</a>
            <a href='#' class='btn btn-danger btn-sm' data-id='{$row['id']}'>Hapus</a>
          </td>";
    echo "</tr>";
    $no++;
}
?>
