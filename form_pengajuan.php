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
// Fungsi validasi
function validateName($name) {
    if (!preg_match('/^[a-zA-Z\s.,\-\'"]+$/', $name)) {
        return 'Nama hanya boleh berisi huruf, spasi, titik, tanda kutip, atau tanda hubung.';
    }
    
    return '';
}

function validateNIS($nis) {
    if (!ctype_digit($nis)) {
        return 'NIS harus berupa angka.';
    }
    return '';
}

function validateJurusan($jurusan) {
    if (!preg_match('/^[a-zA-Z0-9\s]+$/', $jurusan)) {
        return 'Jurusan hanya boleh berisi huruf, angka, dan spasi.';

    }
    return '';
}

function validateKelas($kelas) {
    if (!preg_match('/^[A-Za-z0-9\s\-]+$/', $kelas)) {
        return 'Format kelas tidak valid. Gunakan format seperti: X-TKJ 2.';
    }    
    return '';
}

function validateAlasan($alasan) {
    $length = strlen($alasan);
    if ($length < 5 || $length > 500) {
        return 'Alasan harus antara 5 hingga 500 karakter.';
    }
    
    return '';
}

function validateLokasi($lokasi) {
    $length = strlen($lokasi);
    if ($length < 5 || $length > 500) {
        return 'Lokasi harus antara 5 hingga 500 karakter.';
    }
    return '';
}

function validatePhone($phone) {
    if (!preg_match('/^[0-9\-\+]+$/', $phone)) {
        return 'Nomor telepon hanya boleh berisi angka';
    }
    
    return '';
}

function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Format email tidak valid.';
    }
    return '';
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi dan sanitasi input pengguna
    $nama_lengkap_clean = isset($_POST['nama_lengkap']) ? blockCodeInjection(sanitizeInput($_POST['nama_lengkap'])) : '';
$errors['nama_lengkap'] = validateName($nama_lengkap_clean);

$nis_clean = isset($_POST['nis']) ? blockCodeInjection(sanitizeInput($_POST['nis'])) : '';
$errors['nis'] = validateNIS($nis_clean);

$jurusan_clean = isset($_POST['jurusan']) ? blockCodeInjection(sanitizeInput($_POST['jurusan'])) : '';
$errors['jurusan'] = validateJurusan($jurusan_clean);

$kelas_clean = isset($_POST['kelas']) ? blockCodeInjection(sanitizeInput($_POST['kelas'])) : '';
$errors['kelas'] = validateKelas($kelas_clean);

$alasan_clean = isset($_POST['alasan']) ? blockCodeInjection(sanitizeInput($_POST['alasan'])) : '';
$errors['alasan'] = validateAlasan($alasan_clean);

$lokasi_clean = isset($_POST['lokasi']) ? blockCodeInjection(sanitizeInput($_POST['lokasi'])) : '';
$errors['lokasi'] = validateLokasi($lokasi_clean);

    $tanggal_pengajuan = sanitizeInput($_POST['tanggal_pengajuan']);
    $tanggal_akhir = sanitizeInput($_POST['tanggal_akhir']);
    $email_clean = isset($_POST['email']) ? blockCodeInjection(sanitizeInput($_POST['email'])) : '';
    $errors['email'] = validateEmail($email_clean);
    
    $nohp_clean = isset($_POST['nohp']) ? blockCodeInjection(sanitizeInput($_POST['nohp'])) : '';
    $errors['nohp'] = validatePhone($nohp_clean);
    
 
      // Hapus pesan error yang kosong
    $errors = array_filter($errors);

    if (empty($errors)) {
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
    $stmt = $conn->prepare("INSERT INTO pengajuan (nama_lengkap, nis, jurusan, kelas, alasan, lokasi, tanggal_pengajuan, tanggal_akhir, email, nohp, dokumen_lampiran) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    
    $nama_lengkap = $conn->real_escape_string($nama_lengkap);
    $nis = $conn->real_escape_string($nis);
    $jurusan = $conn->real_escape_string($jurusan);
    $kelas = $conn->real_escape_string($kelas);
    $alasan = $conn->real_escape_string($alasan);
    $lokasi = $conn->real_escape_string($lokasi);
    $stmt->bind_param("sssssssssss", $nama_lengkap_clean, $nis_clean, $jurusan_clean, $kelas_clean, $alasan_clean, $lokasi_clean, $tanggal_pengajuan, $tanggal_akhir, $email_clean, $nohp_clean, $lampiran_nama);

    // Eksekusi query dan tangani hasilnya
    if ($stmt->execute()) {
        $_SESSION['status'] = 'Sukses!. Data berhasil disimpan, silakan hubungi admin untuk konfirmasi data.';
        header('Location: form_pengajuan.php');
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
}
// Cek status form dari tabel `form_status`
$formStatusQuery = "SELECT status FROM form_status WHERE id = 1"; // Asumsikan ID 1 untuk form pengajuan
$formStatusResult = $conn->query($formStatusQuery);

if ($formStatusResult && $formStatusResult->num_rows > 0) {
    $formStatusRow = $formStatusResult->fetch_assoc();
    $formStatus = $formStatusRow['status'];
} else {
    $formStatus = 'inactive'; // Default jika tidak ada data
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
    <link rel="icon" type="image/png" href="admin/image/logowebsite.png">
    <style>
.custom-bg {
    background: linear-gradient(135deg, rgb(164, 201, 255), rgb(110, 150, 210));
    min-height: 100vh; /* Pastikan form tetap di tengah layar */
    display: flex;
    align-items: center;
    justify-content: center;
}
.alert {
    position: flex;
    top: 20px;
    left: 50%;
    bottom: 40px;
    transform: translateX(-50%);
    z-index: 1050;
    width: auto;
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
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 30px;
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.1);
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
        
        .bg-inactive {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: linear-gradient(135deg, rgb(164, 201, 255), rgb(110, 150, 210));
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 800;
}
.text-inactive {
    color: #fff;
    font-size: 24px;
    font-weight: bold;
    text-align: center;
}
#formExplanation {
    font-size: 0.70rem; /* Ukuran font kecil */
    margin-top: -10px; /* Atur jarak jika perlu */
    margin-bottom: -30px;
}


    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand text-dark d-flex align-items-center" href="#">
            <img src="admin/image/logowebsite.png" alt="Logo" style="height: 30px; margin-right: 10px;">
            LANCAR
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            
        </div>
    </div>
</nav>
<?php if ($formStatus === 'inactive'): ?>
    <div class="bg-inactive">
    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="card text-white bg-danger" style="max-width: 30rem;">
        <div class="card-body">
            <h5 class="card-title">Form Pengajuan Tidak Aktif</h5>
            <p class="card-text">Form pengajuan saat ini tidak aktif.</p>
            <p class="card-text">Silakan kembali nanti atau hubungi admin untuk informasi lebih lanjut.</p>
        </div>
    </div>
</div>

    </div>
<?php else: ?>

    <!-- Main Content -->
    <div class="content-wrapper custom-bg d-flex justify-content-center align-items-center" id="content">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                
                <div class="header-form" style="border: 1px solid #ddd; border-radius: 10px 10px 0 0; background-color: #f8f9fa; padding: 30px; margin: -30px -30px 0 -30px;">
    <h2 class="card-title text-center mb-0" style="font-size: 1.8rem; color: #333;">Form Pengajuan Dispensasi</h2>
</div>
                <div class="text-left p-1 mb-4" id="formExplanationWrapper" style="border: 0px solid #ccc; border-radius: 5px; margin-top: 15px; background-color:#fff;">
    <small id="formExplanation"  style="color: rgba(92, 92, 92, 0.6); font-size: 0.9rem;">
        Untuk keperluan Ananda Ketika akan ada kegiatan keluar sekolah sehingga harus meninggalkan kegiatan belajar di kelas.
    </small>
</div>


<?php if (isset($_SESSION['status'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" id="successMessage">
        <?php echo $_SESSION['status']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        // Sembunyikan elemen formExplanation saat notifikasi muncul
        document.getElementById('formExplanationWrapper').style.display = 'none';

        // Hapus notifikasi setelah 5 detik dan tampilkan kembali formExplanation
        setTimeout(function() {
            let successMessage = document.getElementById('successMessage');
            if (successMessage) {
                successMessage.classList.remove('show');
                setTimeout(() => {
                    successMessage.remove();
                    document.getElementById('formExplanationWrapper').style.display = 'block';
                }, 500); // Hapus elemen dari DOM
            }
        }, 5000);
    </script>
    <?php unset($_SESSION['status']); ?>
<?php endif; ?>

                    <form id="dispensasiForm" method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap Siswa</label>
                            <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Lengkap" required>
                            <?php if (isset($errors['nama_lengkap'])): ?>
                                <small class="text-danger"><?php echo $errors['nama_lengkap']; ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="nis" class="form-label">Nomor Induk Siswa (NIS)</label>
                            <input type="text" name="nis" class="form-control" placeholder="Nomor Induk Siswa (NIS)" required>
                            <?php if (isset($errors['nis'])): ?>
                                <small class="text-danger"><?php echo $errors['nis']; ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="jurusan" class="form-label">Jurusan</label>
                            <input type="text" name="jurusan" class="form-control" placeholder="Jurusan" required>
                            <?php if (isset($errors['jurusan'])): ?>
                                <small class="text-danger"><?php echo $errors['jurusan']; ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="kelas" class="form-label">Kelas</label>
                            <input type="text" name="kelas" class="form-control" placeholder="Kelas (Misalkan: X-TKJ-1)" required>
                            <?php if (isset($errors['kelas'])): ?>
                            <small class="text-danger"><?php echo $errors['kelas']; ?></small>
                        <?php endif; ?>
                        </div>
                
                        <div class="mb-3">
                            <label for="alasan" class="form-label">Keperluan Pengajuan</label>
                            <textarea name="alasan" class="form-control" placeholder="Alasan Pengajuan" required></textarea>
                            <?php if (isset($errors['alasan'])): ?>
                                <small class="text-danger"><?php echo $errors['alasan']; ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="lokasi" class="form-label">Lokasi/Tempat Kegiatan</label>
                            <textarea name="lokasi" class="form-control" placeholder="Lokasi/Tempat Kegiatan" required></textarea>
                            <?php if (isset($errors['lokasi'])): ?>
                                <small class="text-danger"><?php echo $errors['lokasi']; ?></small>
                            <?php endif; ?>
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
                            <?php if (isset($errors['email'])): ?>
                                <small class="text-danger"><?php echo $errors['email']; ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="nohp" class="form-label">No.HP (No.Whatsapp)</label>
                            <input type="nohp" name="nohp" class="form-control" placeholder="No.Hp (No. Whatsapp)" required>
                            <?php if (isset($errors['nohp'])): ?>
                                <small class="text-danger"><?php echo $errors['nohp']; ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="dokumen_lampiran" class="form-label">Lampiran Dokumen</label>
                            <div class="form-text">Hanya Format PDF maksimal 2 MB</div>
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
<?php endif; ?>
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
        
        document.addEventListener('DOMContentLoaded', function () {
        const successMessage = document.getElementById('successMessage');
        const formExplanation = document.getElementById('formExplanation');

        if (successMessage) {
            // Sembunyikan teks penjelasan jika alert muncul
            formExplanation.style.display = 'none';

            // Hapus alert dan tampilkan kembali teks penjelasan setelah 5 detik
            setTimeout(function () {
                successMessage.classList.remove('show');
                setTimeout(() => {
                    successMessage.remove();
                    formExplanation.style.display = 'block';
                }, 500); // Tunggu animasi fade out
            }, 5000);
        }
    });
    </script>
</body>
</html>
