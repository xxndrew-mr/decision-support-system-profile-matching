<?php
require_once __DIR__ . "/koneksi.php";
require_once __DIR__ . "/partials/auth.php";
require_role('HRD');

// Ambil batch (rekrutmen) aktif berdasarkan tanggal hari ini
$rekrutmen = mysqli_query($koneksi, "SELECT id_rekrutmen 
    FROM rekrutmen 
    WHERE CURDATE() BETWEEN tanggal_mulai AND tanggal_selesai
    ORDER BY id_rekrutmen DESC LIMIT 1");
$r = mysqli_fetch_assoc($rekrutmen);
$id_rekrutmen = $r ? (int)$r['id_rekrutmen'] : "NULL";

// Ambil semua hasil ranking terbaru (is_history=0)
$q = mysqli_query($koneksi, "SELECT * FROM hasil_ranking WHERE is_history=0");

// Simpan tanggal hari ini
$tanggal = date("Y-m-d");

while($row = mysqli_fetch_assoc($q)) {
    $id_posisi = (int)$row['id_posisi'];
    $id_calon = (int)$row['id_calon'];
    $total = (float)$row['total_nilai'];
    $peringkat = (int)$row['peringkat'];
    $validasi = mysqli_real_escape_string($koneksi, $row['validasi_owner']);

    // Cek apakah sudah ada arsip untuk hari ini, posisi & calon yang sama
    $cek = mysqli_query($koneksi, "SELECT 1 FROM hasil_ranking 
        WHERE id_posisi=$id_posisi 
        AND id_calon=$id_calon 
        AND is_history=1
        AND DATE(created_at)='$tanggal'");

    if (mysqli_num_rows($cek) == 0) {
        mysqli_query($koneksi, "INSERT INTO hasil_ranking
            (id_calon, id_posisi, id_rekrutmen, total_nilai, peringkat, validasi_owner, created_at, is_history)
            VALUES ($id_calon, $id_posisi, $id_rekrutmen, $total, $peringkat, '$validasi', NOW(), 1)");
    }
}

header("Location: hrd/riwayat.php?msg=Hasil ranking berhasil disimpan ke riwayat.");
exit;
