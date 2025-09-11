<?php
require_once __DIR__ . "/../koneksi.php";
require_once __DIR__ . "/../partials/auth.php";
require_role('HRD');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    mysqli_query($koneksi, "DELETE FROM riwayat_ranking WHERE id_riwayat='$id'");
    header("Location: riwayat.php?msg=Data berhasil dihapus");
    exit;
} else {
    header("Location: riwayat.php?msg=ID tidak ditemukan");
    exit;
}
