<?php
require_once __DIR__ . "/../koneksi.php";
require_once __DIR__ . "/../partials/auth.php";
require_role('HRD');

// Kondisi 1: Jika parameter 'all' ada, hapus semua riwayat
if (isset($_GET['all']) && $_GET['all'] == '1') {
    $query = "DELETE FROM hasil_ranking WHERE is_history = 1";
    $pesan = "Semua data riwayat berhasil dihapus.";
}
// Kondisi 2: Jika parameter 'id' ada, hapus satu riwayat saja
elseif (isset($_GET['id'])) {
    $id_hasil = (int)$_GET['id'];
    // Validasi tambahan untuk memastikan hanya riwayat yang dihapus
    $query = "DELETE FROM hasil_ranking WHERE id_hasil = $id_hasil AND is_history = 1";
    $pesan = "Riwayat terpilih berhasil dihapus.";
}
// Kondisi 3: Jika tidak ada parameter yang sesuai
else {
    // Redirect kembali dengan pesan error jika tidak ada aksi yang jelas
    header("Location: riwayat.php?msg=Error: Aksi tidak valid.");
    exit;
}

// Eksekusi query dan redirect kembali ke halaman riwayat
if (mysqli_query($koneksi, $query)) {
    header("Location: riwayat.php?msg=" . urlencode($pesan));
} else {
    header("Location: riwayat.php?msg=Error: Gagal menghapus data.");
}
exit;

?>