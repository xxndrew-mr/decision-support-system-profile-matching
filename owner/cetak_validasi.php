<?php
require_once __DIR__ . "/../koneksi.php";
require_once __DIR__ . "/../partials/auth.php";
require_once __DIR__ . "/../hrd/fpdf/fpdf.php"; // Sesuaikan path ke FPDF
require_role('Owner');

// Ambil filter posisi (logika sama dengan validasi.php)
$selected_posisi = isset($_GET['id_posisi']) ? (int)$_GET['id_posisi'] : 0;
$posisi_nama = "Semua Posisi";

// Ambil daftar kriteria
$kriteria_query = mysqli_query($koneksi, "SELECT id_kriteria, nama_kriteria FROM kriteria ORDER BY id_kriteria");
$kriteria_list = [];
while ($k = mysqli_fetch_assoc($kriteria_query)) {
    $kriteria_list[] = $k;
}

// Ambil semua data penilaian yang aktif
$penilaian_query = mysqli_query($koneksi, "SELECT p.id_calon, p.id_posisi, p.id_kriteria, p.nilai
    FROM penilaian p
    JOIN hasil_ranking hr ON p.id_calon = hr.id_calon AND p.id_posisi = hr.id_posisi
    WHERE hr.is_history = 0");
$nilai_per_calon = [];
while ($nilai_row = mysqli_fetch_assoc($penilaian_query)) {
    $nilai_per_calon[$nilai_row['id_calon']][$nilai_row['id_posisi']][$nilai_row['id_kriteria']] = $nilai_row['nilai'];
}

// Query utama untuk mengambil hasil ranking
$sql = "SELECT hr.*, c.nama, p.nama_posisi, r.nama_rekrutmen
        FROM hasil_ranking hr
        JOIN calon_karyawan c ON c.id_calon = hr.id_calon
        JOIN posisi p ON p.id_posisi = hr.id_posisi
        LEFT JOIN rekrutmen r ON r.id_rekrutmen = hr.id_rekrutmen
        WHERE hr.is_history = 0";
if ($selected_posisi) {
    $sql .= " AND hr.id_posisi=$selected_posisi";
    // Ambil nama posisi untuk judul
    $q_posisi = mysqli_query($koneksi, "SELECT nama_posisi FROM posisi WHERE id_posisi=$selected_posisi");
    if ($p_data = mysqli_fetch_assoc($q_posisi)) {
        $posisi_nama = $p_data['nama_posisi'];
    }
}
$sql .= " ORDER BY p.id_posisi, hr.peringkat";
$hasil = mysqli_query($koneksi, $sql);

// Buat Class PDF dengan Header & Footer kustom
class PDF extends FPDF
{
    private $report_title = '';

    function SetReportTitle($title) {
        $this->report_title = $title;
    }
    
    function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 7, 'Laporan Hasil Ranking untuk Validasi', 0, 1, 'C');
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 6, 'Posisi: ' . $this->report_title, 0, 1, 'C');
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 5, 'Tanggal Cetak: ' . date('d F Y'), 0, 1, 'C');
        $this->Ln(7);
    }
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Proses Pembuatan PDF Dimulai
$pdf = new PDF('L', 'mm', 'A4'); // Landscape
$pdf->SetReportTitle($posisi_nama);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

if (mysqli_num_rows($hasil) == 0) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Tidak ada data ranking yang ditemukan untuk filter yang dipilih.', 0, 1, 'C');
} else {
    // Siapkan Header Tabel
    $header = ['Peringkat', 'Nama Calon'];
    foreach ($kriteria_list as $k) $header[] = $k['nama_kriteria'];
    $header[] = 'Total Nilai';
    $header[] = 'Status';
    $header[] = 'Batch';

    // Atur Lebar Kolom
    $widths = [15, 50]; // Peringkat, Nama
    foreach ($kriteria_list as $k) $widths[] = 25; // Kolom kriteria dinamis
    $widths[] = 20; // Total
    $widths[] = 25; // Status
    $widths[] = 30; // Batch

    // Cetak Header
    $pdf->SetFillColor(224, 235, 255);
    $pdf->SetTextColor(0);
    $pdf->SetDrawColor(128, 128, 128);
    $pdf->SetFont('', 'B', 9);
    for ($i = 0; $i < count($header); $i++) {
        $pdf->Cell($widths[$i], 7, $header[$i], 1, 0, 'C', true);
    }
    $pdf->Ln();

    // Cetak Isi Tabel
    $pdf->SetFont('');
    $pdf->SetFillColor(245, 245, 245);
    $fill = false;
    mysqli_data_seek($hasil, 0);
    while ($r = mysqli_fetch_assoc($hasil)) {
        $pdf->Cell($widths[0], 6, $r['peringkat'], 'LR', 0, 'C', $fill);
        $pdf->Cell($widths[1], 6, html_entity_decode($r['nama']), 'LR', 0, 'L', $fill);

        $col_index = 2;
        foreach ($kriteria_list as $k) {
            $nilai = $nilai_per_calon[$r['id_calon']][$r['id_posisi']][$k['id_kriteria']] ?? '-';
            $pdf->Cell($widths[$col_index], 6, $nilai, 'LR', 0, 'C', $fill);
            $col_index++;
        }
        
        $pdf->Cell($widths[$col_index++], 6, number_format($r['total_nilai'], 2), 'LR', 0, 'C', $fill);
        $pdf->Cell($widths[$col_index++], 6, $r['validasi_owner'], 'LR', 0, 'C', $fill);
        $pdf->Cell($widths[$col_index++], 6, $r['nama_rekrutmen'] ?: '-', 'LR', 0, 'C', $fill);
        
        $pdf->Ln();
        $fill = !$fill;
    }
    $pdf->Cell(array_sum($widths), 0, '', 'T'); // Garis penutup tabel
}

$pdf->Output('D', 'Laporan_Validasi_Ranking_'.date('Y-m-d').'.pdf');
?>