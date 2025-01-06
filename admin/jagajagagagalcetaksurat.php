<?php
// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sertakan FPDF
require('libs/fpdf.php');

// Koneksi database
$servername = "localhost";
$username = "root";
$password = "";
$database = "dispensasikp";

$conn = new mysqli($servername, $username, $password, $database);

// Cek koneksi database
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Tangkap ID dari URL menggunakan GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Ambil data pengajuan berdasarkan ID
    $query = "SELECT * FROM pengajuan WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $pengajuan = $result->fetch_assoc();

        // Fungsi untuk generate surat PDF
        class PDF extends FPDF
        {
            function Header()
            {
                 // Header
                $this->Image('image/logosurat.png', 10, 10, 25); // Menambahkan logo di kiri atas (sesuaikan path dan ukuran)
                $this->SetFont('Times', '', 14);
                $this->Cell(0, 7, 'PEMERINTAH PROVINSI JAWA BARAT', 0, 1, 'C');
                $this->Cell(0, 7, 'DINAS PENDIDIKAN', 0, 1, 'C');
                $this->Cell(0, 7, 'CABANG DINAS PENDIDIKAN WILAYAH VIII', 0, 1, 'C');
                $this->SetFont('Times', 'B', 14);
                $this->Cell(0, 7, 'SEKOLAH MENENGAH KEJURUAN NEGERI 1 MAJALAYA', 0, 1, 'C');
                $this->SetFont('Times', '', 10);
                $this->Cell(0, 5, 'Jl. Idris No.99 Ranca Jigang. No. Telp.(022)595 2443 Majalaya Kab. bandung 40382', 0, 1, 'C');
                $this->Cell(0, 5, 'E-mail: smkn1majalaya@gmail.com', 0, 1, 'C');
                $this->Ln(8);

                // Garis horizontal
                $this->SetLineWidth(1); // Ketebalan garis
                $this->Line(10, 50, 200, 50); // Koordinat garis (x1, y1, x2, y2) - sedikit diturunkan
                $this->Ln(3); // Jarak setelah garis horizontal
            }

            function Footer()
            {
                            // Footer - Tanda Tangan
                $this->Ln(10); // Menambahkan jarak untuk tanda tangan

                // Mengetahui (di atas Dekan,)
                $this->SetX(120); // Set posisi ke kanan
                $this->Cell(0, 6, 'Majalaya, ' . date("d F Y"), 0, 1, 'L');
            

                // Dekan (di bawah Mengetahui)
                $this->SetX(120); // Geser lebih kanan
                $this->Cell(0, 6, 'KEPALA SEKOLAH', 0, 1, 'L');

            
                // Nama Dekan (di bawah foto)
                $this->Ln(20); // Menambahkan jarak setelah gambar
                $this->SetX(120); // Geser lebih kanan
                // Cetak teks
                $this->SetFont('Times', 'B', 12);
                $this->Cell(0, 6, 'UPIE INDRAKUSUMA, S.Pd., MM.', 0, 1, 'L');

                // Gambar garis bawah
                $x = 120; // Posisi awal garis (margin kiri)
                $y = $this->GetY(); // Posisi vertikal setelah teks
                $this->Line($x, $y, $x + 70, $y); // Panjang garis 80 mm (bisa disesuaikan)


                // NIP Dekan (di bawah nama Dekan)
                
                $this->SetFont('Times', '', 12);
                $this->SetX(120); // Geser lebih kanan
                $this->Cell(0, 6, 'NIP: 199605131992011001', 0, 1, 'L');

            }
        }
        $line_height = 8;
        $data = [
            'Nama'            => 'Upie Indrakusuma, S.Pd., MM.',
            'NIP'             => '196805131992011001',
            'Pangkat/Golongan'=> 'Pembina/IVa',
            'Jabatan'         => 'Kepala Sekolah',
            'Instansi'        => 'SMKN 1 MAJALAYA',
        ];
        // Inisialisasi PDF
        $pdf = new PDF();
        $pdf->AddPage();
        $pdf->SetFont('Times', 'B', 14); // Teks bold
        $title = 'S U R A T  K E T E R A N G A N';
       // Hitung lebar teks
        $titleWidth = $pdf->GetStringWidth($title);

        // Posisi X untuk teks (agar di tengah)
        $startX = (210 - $titleWidth) / 2; // Lebar halaman A4 adalah 210 mm

        // Tampilkan teks judul
        $pdf->SetX($startX);
        $pdf->Cell($titleWidth, 7, $title, 0, 1, 'C');

        // Gambar garis bawah sepanjang teks
        $pdf->SetLineWidth(0.5); // Lebar garis
        $pdf->Line($startX, $pdf->GetY(), $startX + $titleWidth, $pdf->GetY()); // Menggambar garis bawah

        $pdf->SetFont('Times', '', 11);
        // Mendapatkan nomor surat berdasarkan tanggal
        $tanggal = date('d/m/Y');  // Format: Tanggal-Bulan-Tahun (contoh: 17/12/2024)
        $kodeSekolah = "SMKN1MLY";  // Kode sekolah atau unit lain yang relevan
        $kodeDivisi = "TU.309";  // Kode divisi atau bagian terkait
        $nomorUrut = "XXI";  // Nomor urut yang bisa dikustomisasi atau ditambahkan

        // Format nomor surat
        $nomorSurat = "Nomor: 1145/$kodeDivisi-$kodeSekolah.$nomorUrut/$tanggal";
        // Menyesuaikan panjang nomor surat agar sama dengan panjang "SURAT KETERANGAN"
        $nomorSuratWidth = $pdf->GetStringWidth($nomorSurat);
        // Set font dan tampilkan nomor surat
        $pdf->SetFont('Times', '', 9); // Ukuran font kecil
        $pdf->SetX((210 - $nomorSuratWidth) / 2); // Posisi tengah sesuai panjang nomor surat
        $pdf->Cell($nomorSuratWidth, 7, $nomorSurat, 0, 1, 'C');
        $pdf->Ln(5); // Spasi setelah nomor surat

        $pdf->Ln(5);

        // Isi Surat
        $pdf->SetFont('Times', '', 12); // Ukuran font kecil
        $pdf->Cell(0, $line_height, 'Yang bertanda tangan di bawah ini:', 0, 1, 'L');

        foreach ($data as $key => $value) {
            // Kolom pertama: Label
            $pdf->SetX(20);
            $pdf->Cell(30, $line_height, $key, 0, 0, 'L');
            
            // Kolom kedua: Titik dua
            $pdf->Cell(10, $line_height, ':', 0, 0, 'L');
            
            // Kolom ketiga: Nilai
            $pdf->Cell(0, $line_height, $value, 0, 1, 'L');
        }
        $pdf->Ln(2);

        // Ketentuan
        // Header Tabel
        
        $pdf->MultiCell(0, 8, 'Memberikan izin dispensasi kepada:');
        $pdf->Ln(5);
        $pdf->SetFont('Times', '', 10); // Ukuran font kecil
        $pdf->Cell(8, 8, 'No', 1, 0, 'C'); // Lebar dikurangi dari 9 ke 8
        $pdf->Cell(30, 8, 'Nama', 1, 0, 'C'); // Lebar dikurangi dari 38 ke 30
        $pdf->Cell(25, 8, 'NIS', 1, 0, 'C'); // Lebar dikurangi dari 29 ke 25
        $pdf->Cell(30, 8, 'Jurusan', 1, 0, 'C'); // Lebar dikurangi dari 38 ke 30
        $pdf->Cell(15, 8, 'Kelas', 1, 0, 'C'); // Lebar dikurangi dari 18 ke 15
        $pdf->Cell(40, 8, 'Keterangan', 1, 0, 'C'); // Lebar dikurangi dari 48 ke 40
        $pdf->Cell(40, 8, 'Lokasi', 1, 1, 'C'); // Lebar dikurangi dari 50 ke 40



        // Isi Tabel
        $pdf->Cell(8, 8, '1', 1, 0, 'C'); // Lebar sesuai header
        $pdf->Cell(30, 8, htmlspecialchars($pengajuan['nama_lengkap']), 1, 0, 'C');
        $pdf->Cell(25, 8, htmlspecialchars($pengajuan['nis']), 1, 0, 'C');
        $pdf->Cell(30, 8, htmlspecialchars($pengajuan['jurusan']), 1, 0, 'C');
        $pdf->Cell(15, 8, htmlspecialchars($pengajuan['kelas']), 1, 0, 'C');
        $pdf->Cell(40, 8, htmlspecialchars($pengajuan['alasan']), 1, 0, 'C');
        $pdf->Cell(40, 8, htmlspecialchars($pengajuan['lokasi']), 1, 1, 'C');


        $pdf->Ln(5);
        $tanggal_pengajuan = $pengajuan['tanggal_pengajuan']; // Ambil tanggal dari database
        $tanggal_format = date('d F Y', strtotime($tanggal_pengajuan)); 
        $tanggal_akhir = $pengajuan['tanggal_akhir']; // Ambil tanggal dari database
        $format_tanggal = date('d F Y', strtotime($tanggal_akhir)); 

        // Tentukan tahun ajaran
        $tahun_sekarang = date('Y');
        $tahun_ajaran = $tahun_sekarang . '-' . ($tahun_sekarang + 1);

        // Buat folder berdasarkan tahun ajaran jika belum ada
        $folder_path = 'arsip/' . $tahun_ajaran;
        if (!is_dir($folder_path)) {
            mkdir($folder_path, 0777, true);
        }

        // Tentukan nama file
        $nama_file = 'surat_dispensasi_' . $id . '.pdf';

        // Path lengkap untuk menyimpan file
        $path_file = $folder_path . '/' . $nama_file;

        // Tanggal Berlaku
        $pdf->SetFont('Times', '', 12); // Ukuran font kecil
        $pdf->MultiCell(0, 8, 'Dengan ini, siswa tersebut diberi dispensasi agar tidak mengikuti pembelajaran pada waktu yang bersamaan dengan kegiatan tersebut. Surat ini berlaku mulai tanggal ' . $tanggal_format . ' sampai ' . $format_tanggal . '.');
        $pdf->Ln(5);

        $pdf->MultiCell(0, 8, 'Demikian surat ini dibuat untuk dapat digunakan sebagaimana mestinya. Dukungan dan kerjasamanya diucapkan terima kasih.');

        // Output PDF
        $pdf->Output('F', $path_file); // 'I' untuk tampilkan langsung di browser
        // Tampilkan file di browser
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $nama_file . '"');
readfile($path_file);
        exit();
    } else {
        echo "Data pengajuan tidak ditemukan.";
    }
} else {
    echo "ID tidak ditemukan.";
}


?>
