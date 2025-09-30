<?php
require_once __DIR__ . "/../koneksi.php";
require_once __DIR__ . "/../partials/auth.php";
require_once __DIR__ . "/fpdf/fpdf.php"; // Panggil library FPDF
require_role('HRD');

// Ambil filter (logika yang sama persis dengan riwayat.php)
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : "";
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : "";
$filter_tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : "";
$filter_nama = isset($_GET['nama']) ? mysqli_real_escape_string($koneksi, $_GET['nama']) : "";

// Buat query filter (logika yang sama persis dengan riwayat.php)
$where = [];
if (!empty($filter_tahun)) $where[] = "YEAR(hr.created_at) = '" . intval($filter_tahun) . "'";
if (!empty($filter_bulan)) $where[] = "MONTH(hr.created_at) = '" . intval($filter_bulan) . "'";
if (!empty($filter_tanggal)) $where[] = "DAY(hr.created_at) = '" . intval($filter_tanggal) . "'";
if (!empty($filter_nama)) $where[] = "c.nama LIKE '%$filter_nama%'";
$where_sql = !empty($where) ? " AND " . implode(" AND ", $where) : "";

// =================================================================
// PERBAIKAN 1: Ambil ID dan Nama Kriteria
// =================================================================
$kriteria_query = mysqli_query($koneksi, "SELECT id_kriteria, nama_kriteria FROM kriteria ORDER BY id_kriteria");
$kriteria_list = [];
while ($k = mysqli_fetch_assoc($kriteria_query)) {
    $kriteria_list[] = $k;
}

// =========================================================================================
// PERBAIKAN 2: Ambil semua data penilaian dalam satu query (JAUH LEBIH EFISIEN)
// =========================================================================================
$penilaian_query = mysqli_query($koneksi, "SELECT p.id_calon, p.id_posisi, p.id_kriteria, p.nilai
    FROM penilaian p
    JOIN hasil_ranking hr ON p.id_calon = hr.id_calon AND p.id_posisi = hr.id_posisi
    JOIN calon_karyawan c ON p.id_calon = c.id_calon
    WHERE hr.is_history = 1 $where_sql");
$nilai_per_calon = [];
while ($nilai_row = mysqli_fetch_assoc($penilaian_query)) {
    $nilai_per_calon[$nilai_row['id_calon']][$nilai_row['id_posisi']][$nilai_row['id_kriteria']] = $nilai_row['nilai'];
}

// Ambil data riwayat yang akan dicetak
$q = mysqli_query($koneksi, "SELECT hr.*, c.nama, p.nama_posisi, r.nama_rekrutmen
    FROM hasil_ranking hr
    JOIN calon_karyawan c ON hr.id_calon=c.id_calon
    JOIN posisi p ON hr.id_posisi=p.id_posisi
    LEFT JOIN rekrutmen r ON c.id_rekrutmen = r.id_rekrutmen
    WHERE hr.is_history=1 $where_sql
    ORDER BY hr.created_at DESC, p.nama_posisi, hr.peringkat");

// Kelompokkan data sama seperti di halaman riwayat
$riwayat = [];
while ($r = mysqli_fetch_assoc($q)) {
    $posisi = $r['nama_posisi'];
    $tanggal = date("d F Y", strtotime($r['created_at']));
    if (!isset($riwayat[$posisi])) $riwayat[$posisi] = [];
    if (!isset($riwayat[$posisi][$tanggal])) $riwayat[$posisi][$tanggal] = [];
    $riwayat[$posisi][$tanggal][] = $r;
}

// Buat Class PDF dengan Header & Footer kustom
class PDF extends FPDF
{
    // Page header
    function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 7, 'Laporan Riwayat Hasil Ranking Karyawan', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'Sistem Pendukung Keputusan - Metode Profile Matching', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 5, 'Tanggal Cetak: ' . date('d F Y'), 0, 1, 'C');
        $this->Ln(7);
    }

    // Page footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Fungsi untuk tabel yang lebih rapi
    function FancyTable($header, $data, $widths)
    {
        // Header
        $this->SetFillColor(224, 235, 255); // Warna biru muda
        $this->SetTextColor(0);
        $this->SetDrawColor(128, 128, 128);
        $this->SetFont('', 'B');
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($widths[$i], 7, $header[$i], 1, 0, 'C', true);
        }
        $this->Ln();

        // Data
        $this->SetFont('');
        $this->SetFillColor(245, 245, 245);
        $fill = false;
        $no = 1;
        foreach ($data as $row) {
            $this->Cell($widths[0], 6, $no++, 'LR', 0, 'C', $fill);
            for ($i = 1; $i < count($widths); $i++) {
                 $align = ($i == 1) ? 'L' : 'C'; // Kolom nama rata kiri
                 $this->Cell($widths[$i], 6, $row[$i-1], 'LR', 0, $align, $fill);
            }
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($widths), 0, '', 'T'); // Garis penutup tabel
    }
}


// Proses Pembuatan PDF Dimulai
$pdf = new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

if (empty($riwayat)) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Tidak ada data riwayat yang ditemukan untuk filter yang dipilih.', 0, 1, 'C');
} else {
    foreach ($riwayat as $posisi => $data_posisi) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(23, 33, 83);
        $pdf->Cell(0, 10, 'Posisi: ' . $posisi, 0, 1, 'L');
        $pdf->SetTextColor(0);

        foreach ($data_posisi as $tanggal => $data_tanggal) {
            $pdf->SetFont('Arial', 'BI', 10);
            $pdf->Cell(0, 8, 'Tanggal Proses: ' . $tanggal, 0, 1, 'L');

            // Siapkan data untuk tabel
            $header = ['No.', 'Nama Calon', 'Peringkat'];
            foreach ($kriteria_list as $k) $header[] = $k['nama_kriteria'];
            $header[] = 'Total Nilai';
            $header[] = 'Status';

            $tableData = [];
            foreach ($data_tanggal as $r) {
                $rowData = [];
                $rowData[] = html_entity_decode($r['nama']);
                $rowData[] = $r['peringkat'];
                foreach ($kriteria_list as $k) {
                    $rowData[] = $nilai_per_calon[$r['id_calon']][$r['id_posisi']][$k['id_kriteria']] ?? '-';
                }
                $rowData[] = number_format($r['total_nilai'], 2);
                $rowData[] = $r['validasi_owner'];
                $tableData[] = $rowData;
            }

            // Atur lebar kolom
            $widths = [10, 50, 20]; // No, Nama, Peringkat
            foreach ($kriteria_list as $k) $widths[] = 25; // Kolom kriteria
            $widths[] = 20; // Total
            $widths[] = 30; // Status

            $pdf->FancyTable($header, $tableData, $widths);
            $pdf->Ln(5);
        }
    }
}

$pdf->Output('D', 'Laporan_Riwayat_Ranking_'.date('Y-m-d').'.pdf');
?>