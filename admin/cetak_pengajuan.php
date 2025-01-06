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
$database = "lancarapp";

$conn = new mysqli($servername, $username, $password, $database);

// Cek koneksi database
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Tangkap ID dari URL menggunakan GET
if (isset($_GET['id']) && isset($_GET['penandatangan_id'])) {
    $id = intval($_GET['id']);
    $penandatangan_id = intval($_GET['penandatangan_id']);
    $nomor_surat = htmlspecialchars($_GET['nomor_surat'], ENT_QUOTES);
    
 // Debug: Tampilkan data ID
 echo "ID Pengajuan: $id<br>";
 echo "ID Penandatangan: $penandatangan_id<br>";
    // Ambil data pengajuan berdasarkan ID
    $query = "SELECT * FROM pengajuan WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $pengajuan = $result->fetch_assoc();


        // Ambil data penandatangan sesuai pilihan pengguna
        $query_atasan = "SELECT nama, nip, CONCAT(pangkat, '/', golongan) AS pangkat_golongan, jabatan, instansi 
                         FROM atasan_sekolah WHERE id = ?";
        $stmt_atasan = $conn->prepare($query_atasan);
        $stmt_atasan->bind_param("i", $penandatangan_id);
        $stmt_atasan->execute();
        $result_atasan = $stmt_atasan->get_result();

        if ($result_atasan->num_rows > 0) {
            $atasan = $result_atasan->fetch_assoc();
        } else {
            die("Data penandatangan tidak ditemukan.");
        }

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
                global $atasan;
                function bulanIndonesia($bulanInggris) {
                    $bulan = [
                        'January' => 'Januari',
                        'February' => 'Februari',
                        'March' => 'Maret',
                        'April' => 'April',
                        'May' => 'Mei',
                        'June' => 'Juni',
                        'July' => 'Juli',
                        'August' => 'Agustus',
                        'September' => 'September',
                        'October' => 'Oktober',
                        'November' => 'November',
                        'December' => 'Desember'
                    ];
                    return $bulan[$bulanInggris];
                }
                
                $tanggal = date("d");
                $bulan = bulanIndonesia(date("F"));
                $tahun = date("Y");

                
                // Footer - Tanda Tangan
                $this->Ln(10); // Menambahkan jarak untuk tanda tangan

                // Mengetahui (di atas Dekan,)
                $this->SetX(120); // Set posisi ke kanan
                $this->Cell(0, 6, 'Majalaya, ' . $tanggal . ' ' . $bulan . ' ' . $tahun, 0, 1, 'L');
            

                // Dekan (di bawah Mengetahui)
                $this->SetX(120); // Geser lebih kanan
                $this->Cell(0, 6, strtoupper($atasan['jabatan']), 0, 1, 'L');

            
                // Nama Dekan (di bawah foto)
                $this->Ln(20); // Menambahkan jarak setelah gambar
                $this->SetX(120); // Geser lebih kanan
                // Cetak teks
                $this->SetFont('Times', 'B', 12);
                $this->Cell(0, 6,  strtoupper($atasan['nama']), 0, 1, 'L');

                // Gambar garis bawah
                $x = 120; // Posisi awal garis (margin kiri)
                $y = $this->GetY(); // Posisi vertikal setelah teks
                $this->Line($x, $y, $x + 70, $y); // Panjang garis 80 mm (bisa disesuaikan)


                // NIP Dekan (di bawah nama Dekan)
                
                $this->SetFont('Times', '', 12);
                $this->SetX(120); // Geser lebih kanan
                $this->Cell(0, 6, 'NIP: ' . $atasan['nip'], 0, 1, 'L');

            }
            function NbLines($w, $txt)
            {
                $cw = &$this->CurrentFont['cw'];
                if ($w == 0) {
                    $w = $this->w - $this->rMargin - $this->x;
                }
                $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
                $s = str_replace("\r", '', $txt);
                $nb = strlen($s);
                if ($nb > 0 && $s[$nb - 1] == "\n") {
                    $nb--;
                }
                $sep = -1;
                $i = 0;
                $j = 0;
                $l = 0;
                $nl = 1;
                while ($i < $nb) {
                    $c = $s[$i];
                    if ($c == "\n") {
                        $i++;
                        $sep = -1;
                        $j = $i;
                        $l = 0;
                        $nl++;
                        continue;
                    }
                    if ($c == ' ') {
                        $sep = $i;
                    }
                    $l += $cw[$c];
                    if ($l > $wmax) {
                        if ($sep == -1) {
                            if ($i == $j) {
                                $i++;
                            }
                        } else {
                            $i = $sep + 1;
                        }
                        $sep = -1;
                        $j = $i;
                        $l = 0;
                        $nl++;
                    } else {
                        $i++;
                    }
                }
                return $nl;
            }

        }
        $line_height = 8;
        $data = [
            'Nama'            => ucwords(htmlspecialchars($atasan['nama'])),
            'NIP'             => htmlspecialchars($atasan['nip']),
            'Pangkat/Golongan'=> ucwords(htmlspecialchars($atasan['pangkat_golongan'])),
            'Jabatan'         => ucwords(htmlspecialchars($atasan['jabatan'])),
            'Instansi'        => strtoupper(htmlspecialchars($atasan['instansi'])),

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
        //$tanggal = date('d/m/Y');  // Format: Tanggal-Bulan-Tahun (contoh: 17/12/2024)
        //$kodeSekolah = "SMKN1MLY";  // Kode sekolah atau unit lain yang relevan
        //$kodeDivisi = "TU.309";  // Kode divisi atau bagian terkait
        //$nomorUrut = "XXI";  // Nomor urut yang bisa dikustomisasi atau ditambahkan

        // Format nomor surat
        //$nomorSurat = "Nomor: 1145/$kodeDivisi-$kodeSekolah.$nomorUrut/$tanggal";
        // Menyesuaikan panjang nomor surat agar sama dengan panjang "SURAT KETERANGAN"
        $nomorSuratWidth = $pdf->GetStringWidth($nomor_surat);
        // Set font dan tampilkan nomor surat
        $pdf->SetFont('Times', '', 9); // Ukuran font kecil
        $pdf->SetX((210 - $nomorSuratWidth) / 2); // Posisi tengah sesuai panjang nomor surat
        $pdf->Cell($nomorSuratWidth, 7, 'Nomor: ' . htmlspecialchars($nomor_surat), 0, 1, 'C');

        $pdf->Ln(5); // Spasi setelah nomor surat

        $pdf->Ln(3);

        // Isi Surat
        $pdf->SetFont('Times', '', 12); // Ukuran font kecil
        $pdf->Cell(0, $line_height, 'Yang bertanda tangan di bawah ini:', 0, 1, 'L');

        foreach ($data as $key => $value) {
            // Kolom pertama: Label
            $pdf->SetX(20);
            $pdf->Cell(30, $line_height, $key, 0, 0, 'L');
            
            // Kolom kedua: Titik dua
            $pdf->Cell(10, $line_height, '   :', 0, 0, 'L');
            
            // Kolom ketiga: Nilai
            $pdf->Cell(0, $line_height, $value, 0, 1, 'L');
        }
        $pdf->Ln(2);

        // Ketentuan
        // Header Tabel
        
        $pdf->MultiCell(0, 8, 'Memberikan izin dispensasi kepada:');
        $pdf->Ln(5);
        
      
        $header = ['No', 'Nama', 'NIS', 'Jurusan', 'Kelas', 'Keterangan', 'Lokasi'];
        $widths = [8, 30, 25, 30, 15, 40, 40]; // Lebar default untuk setiap kolom

        // Header
        $pdf->SetFont('Times', 'B', 10);
        foreach ($header as $key => $col) {
            $pdf->Cell($widths[$key], 8, $col, 1, 0, 'C');
        }
        $pdf->Ln(); // Pindah ke baris berikutnya

        // Isi Tabel
        $pdf->SetFont('Times', '', 10); // Font untuk konten tabel
        $data_rows = [
            [
                'no' => 1,
                'nama' => ucwords(htmlspecialchars($pengajuan['nama_lengkap'])),
                'nis' => htmlspecialchars($pengajuan['nis']),
                'jurusan' => ucwords(htmlspecialchars($pengajuan['jurusan'])),
                'kelas' => htmlspecialchars($pengajuan['kelas']),
                'keterangan' => ucwords(htmlspecialchars($pengajuan['alasan'])),
                'lokasi' => ucwords(htmlspecialchars($pengajuan['lokasi'])),
            ],
        ];
                
        // Loop untuk Data
        foreach ($data_rows as $row) {
            $row['nama'] = ucwords($row['nama']);
            $row['jurusan'] = ucwords($row['jurusan']);
            $row['keterangan'] = ucwords($row['keterangan']);
            $row['lokasi'] = ucwords($row['lokasi']);
            $cell_heights = [];
            
            // Menghitung tinggi maksimum dalam baris
            foreach (array_keys($row) as $i => $key) {
                $cell_text = $row[$key];
                $cell_width = $widths[$i];
                
                // Membuat dummy MultiCell untuk menghitung tinggi teks
                $nb = $pdf->NbLines($cell_width, $cell_text); // Fungsi tambahan untuk menghitung jumlah baris
                $cell_heights[] = $nb * 6; // Asumsi tinggi baris 6
            }
            
            $max_height = max($cell_heights); // Tinggi maksimum dalam baris
            
            // Cetak data dengan tinggi seragam
            foreach (array_keys($row) as $i => $key) {
                $cell_text = $row[$key];
                $cell_width = $widths[$i];
                
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                
                // Gambar kotak sel dengan tinggi seragam
                $pdf->Rect($x, $y, $cell_width, $max_height);
                
                // Tampilkan teks di tengah
                $pdf->SetXY($x, $y + ($max_height - ($pdf->NbLines($cell_width, $cell_text) * 6)) / 2); // Vertikal
                $pdf->MultiCell($cell_width, 6, $cell_text, 0, 'C'); // Horizontal: 'C'
                
                // Geser posisi X ke kolom berikutnya
                $pdf->SetXY($x + $cell_width, $y);
            }
            
            // Pindahkan kursor ke baris berikutnya
            $pdf->Ln($max_height);
        }


        function formatTanggalIndonesia($tanggal)
        {
            $bulanInggris = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            $bulanIndonesia = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            $tanggal = date('d F Y', strtotime($tanggal));
            return str_replace($bulanInggris, $bulanIndonesia, $tanggal);
        }

        $pdf->Ln(5);
        $tanggal_pengajuan = $pengajuan['tanggal_pengajuan']; // Ambil tanggal dari database
        $tanggal_format = formatTanggalIndonesia($tanggal_pengajuan);
        
        $tanggal_akhir = $pengajuan['tanggal_akhir']; // Ambil tanggal dari database
        $format_tanggal = formatTanggalIndonesia($tanggal_akhir);
        

        // Tentukan tahun ajaran berdasarkan tanggal pengajuan
        // Tentukan tahun ajaran berdasarkan tanggal pengajuan
$tanggal_pengajuan = strtotime($pengajuan['tanggal_pengajuan']);
$bulan_pengajuan = date('m', $tanggal_pengajuan);

// Jika bulan pengajuan lebih dari atau sama dengan Juli, tahun ajaran dimulai dari tahun pengajuan tersebut
if ($bulan_pengajuan >= 7) {
    $tahun_awal = (int)date('Y', $tanggal_pengajuan);
    $tahun_akhir = $tahun_awal + 1;
} else {
    // Jika bulan pengajuan sebelum Juli, tahun ajaran dimulai dari tahun sebelumnya
    $tahun_awal = (int)date('Y', $tanggal_pengajuan) - 1;
    $tahun_akhir = $tahun_awal + 1;
}

$tahun_ajaran = $tahun_awal . '-' . $tahun_akhir;

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
// Cek apakah file berhasil disimpan
if (file_exists($path_file)) {
    // Cek apakah data sudah ada sebelumnya
    $query_check = "SELECT * FROM surat_dispensasi WHERE pengajuan_id = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Jika data sudah ada, lakukan update
        $query_update = "UPDATE surat_dispensasi SET nama_file = ?, path_file = ? WHERE pengajuan_id = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("ssi", $nama_file, $path_file, $id);
        
        if ($stmt_update->execute()) {
            echo "Surat dispensasi berhasil diupdate di database.";
        } else {
            echo "Terjadi kesalahan saat memperbarui data ke database.";
        }
    } else {
        // Jika data belum ada, lakukan insert
        $query_insert = "INSERT INTO surat_dispensasi (pengajuan_id, nama_file, path_file) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($query_insert);
        $stmt_insert->bind_param("iss", $id, $nama_file, $path_file);
        
        if ($stmt_insert->execute()) {
            echo "Surat dispensasi berhasil disimpan dan data dimasukkan ke database.";
        } else {
            echo "Terjadi kesalahan saat menyimpan data ke database.";
        }
    }

    // Tampilkan file PDF di browser
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $nama_file . '"');
    readfile($path_file);
    exit();
} else {
    echo "File tidak ditemukan.";
}
    }
}