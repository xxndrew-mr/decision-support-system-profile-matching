<?php
require_once __DIR__ . "/../koneksi.php";

if (!isset($_GET['id'])) {
    echo "ID calon tidak valid.";
    exit;
}

$id = (int) $_GET['id'];
$q = mysqli_query($koneksi, "SELECT * FROM calon_karyawan WHERE id_calon=$id");
if (!$q || mysqli_num_rows($q) == 0) {
    echo "Data tidak ditemukan.";
    exit;
}

$data = mysqli_fetch_assoc($q);
?>

<table class="table table-bordered">
    <tr><th>Nama</th><td><?= htmlspecialchars($data['nama']) ?></td></tr>
    <tr><th>Alamat</th><td><?= htmlspecialchars($data['alamat']) ?></td></tr>
    <tr><th>No. HP</th><td><?= htmlspecialchars($data['no_hp']) ?></td></tr>
    <!-- tambah field lain sesuai tabel calon_karyawan -->
</table>
