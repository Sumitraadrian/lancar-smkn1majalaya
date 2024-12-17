<?php
session_start();
include 'db.php';

// Fungsi untuk membersihkan input dari spasi ekstra dan karakter khusus
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Fungsi untuk memblokir kode berbahaya dalam input
function blockCodeInjection($input) {
    $disallowed = ['<?php', '?>', '<script', '</script>', '<%', '%>', 'eval(', 'base64_decode(', 'shell_exec(', 'system(', '/<\?php/i',       // Tag PHP
        '/<script\b[^>]*>/i',  // Tag <script>
        '/<\/script>/i',   // Penutup tag </script>
        '/on\w+="[^"]*"/i', // Event handlers
        '/<\?php/i',            // Tag PHP
        '/<script\b[^>]*>/i',   // Tag <script>
        '/<\/script>/i',        // Penutup tag </script>
        '/on\w+="[^"]*"/i',     // Event handlers seperti onclick, onerror, dll.
        '/eval\(/i',            // Fungsi eval
        '/base64_decode\(/i',   // Fungsi base64_decode
        '/shell_exec\(/i',      // Fungsi shell_exec
        '/system\(/i',          // Fungsi system
        '/\bexec\b/i',          // Fungsi exec
        '/drop\s+table/i',      // SQL Injection
        '/--/i',                // SQL comment-style
        '/;\s*--/i',            // SQL Injection via comment
        '/union\s+select/i',    // SQL Union-based Injection
        '/<%/i',                // Tag ASP
        '/%>/i',                // Tag ASP
        '<?php', '?>', '<script', '</script>', '<%', '%>', 'eval(', 
        'base64_decode(', 'shell_exec(', 'system(', 'exec(', 
        'passthru(', 'proc_open(', 'popen(', 'curl_exec('
    ];
    foreach ($disallowed as $bad) {
        if (stripos($input, $bad) !== false) {
            die('<p style="color: red; font-weight: bold;">Error: Dilarang menginputkan script kodingan apapun.</p>');
        }
    }
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

// Validasi nama lengkap: hanya huruf, spasi, titik, koma, atau tanda hubung
function validateName($name) {
    if (!preg_match('/^[a-zA-Z\s.,-]+$/', $name)) {
        die('Error: Invalid name format.');
    }
    return $name;
}

// Validasi NIS: hanya angka
function validateNIS($nis) {
    if (!ctype_digit($nis)) {
        die('Error: Invalid NIS format.');
    }
    return $nis;
}

// Validasi jurusan: hanya huruf, angka, dan spasi
function validateJurusan($jurusan) {
    if (!preg_match('/^[a-zA-Z0-9\s]+$/', $jurusan)) {
        die('Error: Invalid jurusan format.');
    }
    return $jurusan;
}

// Validasi kelas: hanya alfanumerik
function validateKelas($kelas) {
    // Memastikan format seperti 'XI-TKJ 1' atau 'XI TKJ 1'
    if (!preg_match('/^[A-Za-z]{2,3}[- ]?[A-Za-z]{3,4} \d+$/', $kelas)) {
        die('Error: Invalid class format.');
    }
    return $kelas;
}


// Validasi alasan: panjang minimal 10 dan maksimal 500 karakter
function validateAlasan($alasan) {
    $length = strlen($alasan);
    if ($length < 10 || $length > 500) {
        die('Error: Reason must be between 10 and 500 characters.');
    }
    return $alasan;
}

// Validasi nomor HP: hanya angka, tanda plus, dan tanda hubung
function validatePhone($phone) {
    if (!preg_match('/^[0-9\-\+]+$/', $phone)) {
        die('Error: Invalid phone number format.');
    }
    return $phone;
}

// Validasi email: menggunakan filter_var
function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Error: Invalid email format.');
    }
    return $email;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi dan sanitasi input pengguna
    $nama_lengkap = blockCodeInjection(validateName(sanitizeInput($_POST['nama_lengkap'])));
    $nis = blockCodeInjection(validateNIS(sanitizeInput($_POST['nis'])));
    $jurusan = blockCodeInjection(validateJurusan(sanitizeInput($_POST['jurusan'])));
    $kelas = blockCodeInjection(validateKelas(sanitizeInput($_POST['kelas'])));
    $alasan = blockCodeInjection(validateAlasan(sanitizeInput($_POST['alasan'])));
    $tanggal_pengajuan = sanitizeInput($_POST['tanggal_pengajuan']);
    $tanggal_akhir = sanitizeInput($_POST['tanggal_akhir']);
    $email = blockCodeInjection(validateEmail(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)));
    $nohp = blockCodeInjection(validatePhone(sanitizeInput($_POST['nohp'])));

    // Proses upload dokumen lampiran
    $lampiran_nama = null;
    if (isset($_FILES['dokumen_lampiran']) && $_FILES['dokumen_lampiran']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['application/pdf']; // Hanya izinkan file PDF
        $file_type = mime_content_type($_FILES['dokumen_lampiran']['tmp_name']);

        if (in_array($file_type, $allowed_types)) {
            $lampiran_nama = uniqid('lampiran_', true) . '.pdf';
            $upload_dir = 'uploads/' . $lampiran_nama;

            // Pindahkan file ke direktori tujuan
            if (!move_uploaded_file($_FILES['dokumen_lampiran']['tmp_name'], $upload_dir)) {
                die('Error: Failed to upload file.');
            }
        } else {
            die('Error: Only PDF files are allowed.');
        }
    }

    // Siapkan query untuk menyimpan data ke database
    $stmt = $conn->prepare("INSERT INTO pengajuan (user_id, nama_lengkap, nis, jurusan, kelas, alasan, tanggal_pengajuan, tanggal_akhir, email, nohp, dokumen_lampiran) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $user_id = $_SESSION['user_id'] ?? null; // Ambil user_id dari session
    $nama_lengkap = $conn->real_escape_string($nama_lengkap);
    $nis = $conn->real_escape_string($nis);
    $jurusan = $conn->real_escape_string($jurusan);
    $kelas = $conn->real_escape_string($kelas);
    $alasan = $conn->real_escape_string($alasan);
    $stmt->bind_param("sssssssssss", $user_id, $nama_lengkap, $nis, $jurusan, $kelas, $alasan, $tanggal_pengajuan, $tanggal_akhir, $email, $nohp, $lampiran_nama);

    // Eksekusi query dan tangani hasilnya
    if ($stmt->execute()) {
        $_SESSION['status'] = 'Sukses!. Data berhasil disimpan, silakan hubungi admin untuk konfirmasi data.';
        header('Location: form_pengajuan.php');
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengajuan Dispensasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="image/logowebsite.png">
    <style>
        .custom-bg {
            background-color: #5393F3; /* Customize this color as desired */
        }
        body {
            font-family: 'Roboto', sans-serif;
        }
        .content-wrapper {
            margin-left: 0px;
            padding-top: 60px;
            transition: margin-left 0.3s ease;
        }
        .content-wrapper.expanded {
            margin-left: 0;
        }
        .navbar {
            background-color: #ffff;
            color: black;
            z-index: 2;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 30px;
        }

        .form-control, .btn {
            border-radius: 10px;
        }

        .btn-primary {
            background-color: #5393F3;
            border: none;
        }

        .btn-primary:hover {
            background-color: #4779d3;
        }

        .form-label {
            font-weight: 600;
            color: #333;
        }

        .modal-header, .modal-footer {
            border: none;
        }

        .modal-body h5 {
            font-size: 1.25rem;
            font-weight: 500;
        }

        .modal-body p {
            color: #6c757d;
        }

        .modal-content {
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand text-dark" href="#">LANCAR</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                
            </div>
        </div>
    </nav>
    <!-- Main Content -->
    <div class="content-wrapper custom-bg d-flex justify-content-center align-items-center" id="content">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Form Pengajuan Dispensasi</h2>
                    <?php if (isset($_SESSION['status'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successMessage">
                            <?php echo $_SESSION['status']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <script>
                            // Automatically hide the success message after 5 seconds
                            setTimeout(function() {
                                document.getElementById('successMessage').classList.remove('show');
                            }, 3000); // 5000 milliseconds = 5 seconds
                        </script>
                        <?php unset($_SESSION['status']); // Remove the message after displaying ?>
                    <?php endif; ?>

                    <form id="dispensasiForm" method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap Siswa</label>
                            <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap" required>
                        </div>
                        <div class="mb-3">
                            <label for="nis" class="form-label">Nomor Induk Siswa (NIS)</label>
                            <input type="text" name="nis" class="form-control" placeholder="Nomor Induk Siswa (NIS)" required>
                        </div>
                        <div class="mb-3">
                            <label for="jurusan" class="form-label">Jurusan</label>
                            <input type="text" name="jurusan" class="form-control" placeholder="Jurusan" required>
                        </div>
                        <div class="mb-3">
                            <label for="kelas" class="form-label">Kelas</label>
                            <input type="text" name="kelas" class="form-control" placeholder="Kelas" required>
                        </div>
                
                        <div class="mb-3">
                            <label for="alasan" class="form-label">Keperluan Pengajuan</label>
                            <textarea name="alasan" class="form-control" placeholder="Alasan Pengajuan" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="tanggal_pengajuan" class="form-label">Tanggal Mulai Dispensasi</label>
                            <input type="date" name="tanggal_pengajuan" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="tanggal_akhir" class="form-label">Tanggal Akhir Dispensasi</label>
                            <input type="date" name="tanggal_akhir" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Email" required>
                        </div>
                        <div class="mb-3">
                            <label for="nohp" class="form-label">No.HP (No.Whatsapp)</label>
                            <input type="nohp" name="nohp" class="form-control" placeholder="No.Hp (No. Whatsapp)" required>
                        </div>
                        <div class="mb-3">
                            <label for="dokumen_lampiran" class="form-label">Lampiran Dokumen</label>
                            <input type="file" name="dokumen_lampiran" class="form-control" placeholder="Email" required>
                        </div>
                        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#confirmModal">Simpan Data</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Header Modal -->
            <div class="modal-header border-0">
                <h5 class="modal-title text-center w-100" id="confirmModalLabel">
                <i class="bi bi-exclamation-circle text-danger" style="font-size: 6rem;"></i>

                </h5>
            </div>
            <!-- Body Modal -->
            <div class="modal-body text-center">
                <h5 class="fw-bold">Apakah Data yang Anda Isi Sudah Benar?</h5>
                <p class="text-muted">Pastikan semua data yang Anda masukkan telah diverifikasi sebelum melanjutkan.</p>
            </div>
            <!-- Footer Modal -->
            <div class="modal-footer justify-content-center border-0">
                <button type="button" class="btn btn-success px-4" id="confirmButton">
                    <i class="bi bi-check-circle"></i> Ya, Sudah Benar
                </button>
                <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Batal
                </button>
            </div>
        </div>
    </div>
</div>
    
<!-- Modal Error -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h5 id="errorMessage">Perhatian: Dilarang Menginputkan Script Kode Apapun.</h5>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Error -->
<div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h5 id="warningMessage">Tolong Isi Seluruh Data Dengan Benar</h5>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
      document.getElementById("confirmButton").addEventListener("click", function () {
    // Validasi input sebelum mengirim form
    const form = document.getElementById("dispensasiForm");
    const disallowedPatterns = [/<\?php/, /<script\b/, /<\/script>/, /eval\(/, /base64_decode\(/];
    const inputs = Array.from(form.elements);
    
    // Collect form fields
    const namaLengkap = form['nama_lengkap'].value;
    const nis = form['nis'].value;
    const jurusan = form['jurusan'].value;
    const kelas = form['kelas'].value;
    const alasan = form['alasan'].value;
    const tanggalPengajuan = form['tanggal_pengajuan'].value;
    const tanggalAkhir = form['tanggal_akhir'].value;
    const email = form['email'].value;
    const nohp = form['nohp'].value;
    const dokumenLampiran = form['dokumen_lampiran'].files.length > 0;

    let errorFound = false;
    let errorMessage = "Perhatian: Dilarang Menginputkan Script Kode Apapun.";

    // Periksa setiap input dalam form untuk kode berbahaya
    for (let input of inputs) {
        for (let pattern of disallowedPatterns) {
            if (pattern.test(input.value)) {
                errorFound = true;
                break;
            }
        }
        if (errorFound) break;
    }

    // Tutup modal konfirmasi jika ada error atau warning
    const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));

    if (errorFound) {
        // Tutup modal confirm
        confirmModal.hide();
        
        // Tampilkan pesan error dalam modal
        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        document.getElementById('errorMessage').innerText = errorMessage;
        errorModal.show();
    } else if (!namaLengkap || !nis || !jurusan || !kelas || !alasan || !tanggalPengajuan || !tanggalAkhir || !email || !nohp || !dokumenLampiran) {
        // Tutup modal confirm
        confirmModal.hide();
        
        // Tampilkan modal warning jika ada field yang kosong
        document.getElementById('warningMessage').textContent = 'Perhatian: Semua Form Harus Diisi Dengan Benar!';
        const warningModal = new bootstrap.Modal(document.getElementById('warningModal'));
        warningModal.show();
    } else {
        // Kirim form jika tidak ada error
        form.submit();
    }
});




        document.getElementById("sidebarToggle").addEventListener("click", function() {
            document.getElementById("sidebar").classList.toggle("collapsed");
            document.getElementById("content").classList.toggle("expanded");
        });
        
        
    </script>
</body>
</html>
