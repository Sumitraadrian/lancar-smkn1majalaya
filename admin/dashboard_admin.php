<?php
session_start();
include 'db.php';
$currentPage = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$query = "SELECT * FROM pengajuan";
$result = $conn->query($query);

$queryDispenHariIni = "SELECT COUNT(*) AS total FROM pengajuan WHERE DATE(tanggal_pengajuan) = CURDATE()";
$resultDispenHariIni = $conn->query($queryDispenHariIni);
$dispenHariIni = $resultDispenHariIni->fetch_assoc()['total'];

$queryDataTerbaru = "SELECT COUNT(*) AS total FROM pengajuan WHERE status = 'pending'";
$resultDataTerbaru = $conn->query($queryDataTerbaru);
$dataTerbaru = $resultDataTerbaru->fetch_assoc()['total'];

$queryMonthlyData = "SELECT DATE(tanggal_pengajuan) AS date, COUNT(*) AS total
                     FROM pengajuan 
                     WHERE tanggal_pengajuan BETWEEN CURDATE() - INTERVAL 1 MONTH AND CURDATE()
                     GROUP BY DATE(tanggal_pengajuan)
                     ORDER BY DATE(tanggal_pengajuan)";
$resultMonthlyData = $conn->query($queryMonthlyData);

$dates = [];
$totals = [];
while ($row = $resultMonthlyData->fetch_assoc()) {
    $dates[] = $row['date'];
    $totals[] = $row['total'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lancar - Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://kit.fontawesome.com/YOUR_KIT_CODE.js" crossorigin="anonymous"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" type="image/png" href="image/logoweb.png">
    <link rel="icon" type="image/png" href="image/logowebsite.png">
    <script src="https://kit.fontawesome.com/YOUR_KIT_CODE.js" crossorigin="anonymous"></script>
    <style>
                body {
    background-color: #f8f9fa;
}

.sidebar {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    position: fixed;
        top: 0;
        left: 0;
        width: 250px;
        height: 100vh;
        background-color: #343a40;
        color: white;
        padding-top: 80px;
        transition: transform 0.3s ease;
}

.sidebar h5 {
    text-align: center;
    color: white;
    margin-bottom: 20px;
    margin-top: 40px;
}

.sidebar a {
    color: white;
    display: block;
    padding: 10px 20px;
    text-decoration: none;
    font-size: 16px;
}

.sidebar a:hover {
    background-color:rgb(235, 234, 234);
}

.sidebar.collapsed {
    transform: translateX(-100%);
}

.sidebar.collapsed ~ .dashboard-header {
    margin-left: 0;
    width: 100%;
}

.sidebar.collapsed ~ .main-content {
    margin-left: 0;
    width: 100%;
}

.content-wrapper {
    margin-left: 250px;
    padding-top: 60px;
    transition: margin-left 0.3s ease;
}

.content-wrapper.expanded {
    margin-left: 0;
}

.dashboard-header {
    width: calc(100% - 250px);
    padding: 120px;
    border-radius: 0;
    background: linear-gradient(to right, #63baef, #1e4b8a);
    margin-left: 0px;
    color: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    position: relative;
    z-index: 1;
    margin-left: 250px;
    justify-content: space-between;
    display: flex;
}

.main-content {
    margin-left: 250px;
    padding: 20px;
    margin-top: 150px;
    min-height: calc(100vh - 56px);
}

.welcome-card {
    background-color: #ffffff;
    padding: 0px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-top: -240px;
    display: flex;
    margin-right: 90px;
    justify-content: space-between;
    position: relative;
    left: 0;
    z-index: 1;
    width: calc(100% - 12px);
    transition: all 0.3s ease;
}

.welcome-card div {
    display: flex;
    flex-direction: column;
}

.welcome-card h4 {
    margin: 0;
    margin-top: 40px;
    margin-left: 40px;
    font-size: 30px;
    font-weight: bold;
}

.welcome-card p {
    margin: 5px 0 0 0;
    font-size: 20px;
    margin-left: 40px;
    color: #555;
}

.welcome-card img {
    width: 150px; /* Set the desired width */
    height: 150px; /* Set the desired height */
     /* Optional: make the image circular */
    object-fit: cover; /* Maintain aspect ratio and crop if needed */
    margin-right: 40px;
}

.info-card {
    color: white;
    padding: 20px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-grow: 1;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.info-card:hover {
    transform: scale(1.05); /* Slightly enlarge the card */
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); /* Stronger shadow for emphasis */
}

.info-card-primary {
    background: linear-gradient(to right,  #428148,rgb(38, 83, 43));
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.info-card-warning {
    background-color: #f5a623;
    background: linear-gradient(to right,  #f5a623,rgb(199, 123, 0));
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}


.navbar {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

#current-date {
    width: 250px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding-left: 10px;
    background-color: white;
    color: black;
    border: none;
    border-radius: 5px;
    gap: 8px;
}

#current-date i {
    font-size: 18px;
    color: black;
}

.dashboard-header h3 {
    margin: 0;
    font-size: 40px;
    font-weight: bold;
}

.dashboard-header small {
    display: block;
    font-size: 17px;
    color: #f8f9fa;
}
/* CSS untuk mengubah warna menu saat pointer diarahkan */
.nav-link:hover {
    color:  black !important; /* Mengubah warna teks menjadi putih */
    font-weight: bold; 
    background-color: #007bff; /* (Optional) Menambahkan warna latar belakang biru saat hover */
}

.nav-link.active {
    color: #007bff; /* Menjaga warna teks biru untuk menu yang aktif */
}
.chart-container {
    width: 100%; /* Full width of its parent */
    height: 400px; /* Adjust height as necessary */
    display: flex;
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-top: 50px; /* Adjust top margin for spacing */
}

canvas {
    width: 100% !important; /* Make the canvas take full width of the container */
    height: 100% !important; /* Make the canvas take full height of the container */
}



@media (max-width: 768px) {
    .sidebar {
        width: 100%;
            height: auto;
            top: 0;
            left: 0;
            transform: translateY(-100%);
            position: absolute;
            z-index: 1000; /* Agar berada di atas header */
            transition: transform 0.3s ease;
    }

    .sidebar.visible {
            transform: translateY(0);
        }

    .dashboard-header {
        
        margin-left: 0; /* Reset margin di mode responsif */
        width: 100%; /* Pastikan header memenuhi lebar layar */
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
        position: relative;
        padding: 80px;
        z-index: 1;
    }
    .sidebar ~ .dashboard-header {
            z-index: 999;
        }
    .dashboard-header .current-date-container {
        margin-top: 10px; /* Adds space between the small and the current date */
    }
    
    /* Keep other styles for responsiveness as they are */
    .dashboard-header h3 {
        font-size: 30px;
    }
    .dashboard-header h3, .dashboard-header small {
        text-align: left;
    }
    .dashboard-header small {
        font-size: 14px; /* Reduce font size for small text */
    }
    #current-date {
        font-size: 11px; /* Smaller font size for the current date */
    }
    .main-content {
        margin-left: 0;
        padding: 10px;
        margin-top: 100px;
    }

    .welcome-card {
        flex-direction: column;
        margin-top: -120px;
        width: 100%;
    }

    .welcome-card h4 {
        font-size: 24px;
        margin-left: 20px;
    }
    .welcome-card div {
    display: flex;
    flex-direction: column;
    align-items: center; /* Menyelaraskan elemen secara horizontal ke tengah */
    justify-content: center; /* Menyelaraskan elemen secara vertikal ke tengah */
    text-align: center; /* Menyelaraskan teks ke tengah */
}


    /* Move the "Di Website Aplikasi Surat Izin Dispensasi" text to the left */
    .welcome-card h4, .welcome-card p {
        margin-left: 0; /* Remove the left margin to align it to the left */
        text-align: left; /* Ensure the text is left-aligned */
    }

    .welcome-card img {
    display: block; /* Mengubah elemen menjadi block untuk mendukung margin auto */
    margin: 20px auto 0; /* Mengatur margin atas 20px, margin kiri-kanan otomatis, margin bawah 0 */
    width: 180px;
    height: 180px;
    object-fit: cover;
}


    .info-card {
        flex-direction: column;
        padding: 18px;
        margin-bottom: 20px;
    }

    #current-date {
        width: 100%;
        margin-bottom: 10px;
    }
    .welcome-card p{
        padding: 10px;
        font-size: 15px;
        text-align: left
    }
    #dispen-hari-ini {
        text-align: center;  /* Align the text to the center */
        width: 100%;          /* Make sure it takes the full width */
        margin: 0 auto;
        margin-bottom: 10px;       /* Center it horizontally */
    }

    /* Optionally, you can also adjust the parent container for better alignment */
    .info-card-primary {
        justify-content: center; /* Center the contents of the info card */
    }
    #data-terbaru {
        text-align: center;  /* Align the text to the center */
        width: 100%;          /* Make sure it takes the full width */
        margin: 0 auto;
        margin-bottom: 10px;       /* Center it horizontally */
    }

    /* Optionally, you can also adjust the parent container for better alignment */
    .info-card-primary {
        justify-content: center; /* Center the contents of the info card */
    }
}

@media (max-width: 480px) {
    .sidebar {
        width: 100%;
            height: auto;
            top: 0;
            left: 0;
            transform: translateY(-100%);
            position: absolute;
            z-index: 1000; /* Agar berada di atas header */
            transition: transform 0.3s ease;
    }

    .sidebar a {
        font-size: 14px;
    }

    .dashboard-header h3 {
        font-size: 20px;
        margin-left:automatic;
    }

    .welcome-card h4 {
        font-size: 20px;
        margin-left: 10px;
    }

    .info-card {
        font-size: 14px;
    }

    .navbar {
        padding: 5px;
    }
}




    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container-fluid">
        <button class="btn me-3" id="sidebarToggle" style="background-color: transparent; border: none;">
            <span class="navbar-toggler-icon"></span>
        </button>

        <a class="navbar-brand text-black" href="#">LANCAR</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <!-- Tambahkan menu lain di sini jika diperlukan -->
            </ul>
        </div>
    </div>
</nav>
<div class="sidebar bg-light p-3 d-flex flex-column" id="sidebar" style="height: 100vh;">
    <h4 class="text-center">LANCAR</h4>
    <small class="text-muted ms-2" style="margin-top: 40px;">MENU</small>
    <!-- Kategori Dispensasi Siswa -->
    <div class="mt-2">
        <a class="d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#dispensasiMenu" role="button" aria-expanded="false" aria-controls="dispensasiMenu">
            <small class="text-muted">Dispensasi Siswa</small>
            <i class="bi bi-chevron-down"></i>
        </a>
        <div class="collapse show" id="dispensasiMenu">
            <nav class="nav flex-column">
                <a class="nav-link d-flex align-items-center <?= $currentPage == 'dashboard_admin.php' ? 'active' : '' ?>" href="dashboard_admin.php" style="color: <?= $currentPage == 'dashboard_admin.php' ? '#007bff' : 'black'; ?>;">
                    <i class="bi bi-activity" style="margin-right: 15px;"></i> Dashboard
                </a>
                <a class="nav-link d-flex align-items-center <?= $currentPage == 'list_pengajuan.php' ? 'active' : '' ?>" href="list_pengajuan.php" style="color: <?= $currentPage == 'list_pengajuan.php' ? '#007bff' : 'black'; ?>;">
                    <i class="bi bi-file-earmark-plus" style="margin-right: 15px;"></i> Daftar Pengajuan
                </a>
                <a class="nav-link d-flex align-items-center <?= $currentPage == 'list_tolakPengajuan.php' ? 'active' : '' ?>" href="list_tolakPengajuan.php" style="color: <?= $currentPage == 'list_tolakPengajuan.php' ? '#007bff' : 'black'; ?>;">
                    <i class="bi bi-x-circle" style="margin-right: 15px;"></i> Daftar Pengajuan Ditolak
                </a>
                <a class="nav-link d-flex align-items-center <?= $currentPage == 'riwayat_pengajuan.php' ? 'active' : '' ?>" href="riwayat_pengajuan.php" style="color: <?= $currentPage == 'riwayat_pengajuan.php' ? '#007bff' : 'black'; ?>;">
                    <i class="bi bi-archive" style="margin-right: 15px;"></i> Riwayat Pengajuan
                </a>
                
                <a class="nav-link d-flex align-items-center <?= $currentPage == 'guruAtasan/list_atasan.php' ? 'active' : '' ?>" href="guruAtasan/list_atasan.php" style="color: <?= $currentPage == 'guruAtasan/list_atasan.php' ? '#007bff' : 'black'; ?>;">
                    <i class="bi bi-person-check" style="margin-right: 15px;"></i> Data Guru Atasan
                </a>
            </nav>
        </div>
    </div>

    <!-- Kategori Manajemen Surat -->
    <div class="mt-4">
        <a class="d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#suratMenu" role="button" aria-expanded="false" aria-controls="suratMenu">
            <small class="text-muted">Manajemen Surat Masuk/Keluar</small>
            <i class="bi bi-chevron-down"></i>
        </a>
        <div class="collapse show" id="suratMenu">
            <nav class="nav flex-column">
                <a class="nav-link d-flex align-items-center <?= $currentPage == 'suratmasukkeluar/surat.php' ? 'active' : '' ?>" href="suratmasukkeluar/surat.php" style="color: <?= $currentPage == 'suratmasukkeluar/surat.php' ? '#007bff' : 'black'; ?>;">
                    <i class="bi bi-envelope" style="margin-right: 15px;"></i> Surat Masuk/Keluar
                </a>
            </nav>
        </div>
    </div>

    <!-- Pengaturan dan Logout -->
    <small class="text-muted ms-2 mt-4">Pengaturan</small>
    <nav class="nav flex-column mt-2">
        <a class="nav-link d-flex align-items-center <?= $currentPage == 'pengaturan_admin.php' ? 'active' : '' ?>" href="pengaturan_admin.php" style="color: <?= $currentPage == 'pengaturan_admin.php' ? '#007bff' : 'black'; ?>;">
            <i class="bi bi-gear" style="margin-right: 15px;"></i> Pengaturan Akun
        </a>
        <a class="nav-link d-flex align-items-center <?= $currentPage == 'logout.php' ? 'active' : '' ?>" href="logout.php" style="color: <?= $currentPage == 'logout.php' ? '#007bff' : 'black'; ?>;">
            <i class="bi bi-box-arrow-right" style="margin-right: 15px;"></i> Logout
        </a>
    </nav>
</div>


    <!-- Sidebar 
    <div class="sidebar">
        <h5>SUDISMA</h5>
        <a href="#" onclick="openDashboard()">Dashboard</a>
        <a href="#" onclick="openDispensasi()">Dispensasi</a>
        <a href="#" onclick="openAngkatan()">Angkatan</a>
        <a href="#" onclick="openPenyetujuIzin()">Penyetuju Izin</a>
        <a href="#" onclick="openTanggalPengajuan()">Tanggal Pengajuan</a>
    </div> -->

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div>
            <h3>Dashboard</h3>
            <small>Administrator Panel</small>
        </div>
        <button class="btn btn-light ml-auto" id="current-date">
            <i class="fas fa-calendar-alt"></i> <span id="date-text"></span>
        </button>



    </div>


        
    <!-- Main content -->
    <div class="main-content" id="content">
        <!-- Welcome Card -->
        <div class="welcome-card" id="card">
            <div>
                <h4>Selamat Datang Admin</h4>
                <p>Di Website Aplikasi Manajemen Surat Dispensasi, Surat Masuk dan Keluar</p>
            </div>
            <img src="image/logowebsite.png" alt="User Image">
        </div>

        <!-- Information Cards -->
        <!-- Information Cards -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="info-card info-card-primary"onclick="lihatDispenHariIni()" style="cursor: pointer;">
            <div>
                <h5>Dispen Hari ini</h5>
                <h2 id="dispen-hari-ini"><?php echo $dispenHariIni; ?></h2>
            </div>
            <!-- Ikon tanpa tombol -->
            <i class="fas fa-envelope fa-2x" onclick="lihatDispenHariIni()" style="cursor: pointer;"></i>
        </div>
    </div>

    <div class="col-md-6">
        <div class="info-card info-card-warning"onclick="lihatDataTerbaru()" style="cursor: pointer;">
            <div>
                <h5>Data Terbaru</h5>
                <h2 id="data-terbaru"><?php echo $dataTerbaru; ?></h2>
            </div>
            <!-- Ikon tanpa tombol -->
            <i class="fas fa-envelope fa-2x" onclick="lihatDataTerbaru()" style="cursor: pointer;"></i>
        </div>
    </div>
    <!--
    <div class="chart-container">
        <canvas id="monthlyChart"></canvas>
    </div> -->



</div>


    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>


    <!-- Action Scripts -->
    <script>
        // Function to display current date in the format "MMM DD, YYYY"
        document.addEventListener('DOMContentLoaded', (event) => {
    const dateTextElement = document.getElementById("date-text");
    const today = new Date();
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    
    // Format date as "Month Day, Year - Month Day, Year"
    const formattedDate = today.toLocaleDateString("en-US", options);
    dateTextElement.textContent = `${formattedDate} - ${formattedDate}`;
});



        // Sidebar navigation functions
        function openDashboard() {
            alert("Navigating to Dashboard...");
        }

        function openDispensasi() {
            alert("Navigating to Dispensasi...");
        }

        function openAngkatan() {
            alert("Navigating to Angkatan...");
        }

        function openPenyetujuIzin() {
            alert("Navigating to Penyetuju Izin...");
        }

        function openTanggalPengajuan() {
            alert("Navigating to Tanggal Pengajuan...");
        }

        // Info card button functions
        function lihatDispenHariIni() {
            window.location.href = 'list_pengajuan.php';
        }

        function lihatDataTerbaru() {
            window.location.href = 'list_pengajuan.php';
        }
        document.getElementById("sidebarToggle").addEventListener("click", function() {
            const sidebar = document.getElementById("sidebar");
            
            // Toggle class "visible" untuk menampilkan sidebar dari atas pada layar kecil
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle("visible");
            } else {
                // Mode biasa tetap gunakan toggle class "collapsed"
                sidebar.classList.toggle("collapsed");
            }
        });
        const ctx = document.getElementById('monthlyChart').getContext('2d');
const myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($dates) ?>,
        datasets: [{
            label: 'Jumlah Pengajuan',
            data: <?= json_encode($totals) ?>,
            borderColor: 'rgba(75, 192, 192, 1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,  // This will allow the chart size to be controlled by the container
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Tanggal'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Jumlah Pengajuan'
                }
            }
        }
    }
});

    </script>
</body>
</html>
