<?php
include "koneksi.php";
session_start();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['HRD','Owner'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: profile_ideal.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pendidikan = $_POST['pendidikan'];
    $pengalaman = $_POST['pengalaman'];
    $keterampilan = $_POST['keterampilan'];
    $kondisi_fisik = $_POST['kondisi_fisik'];

    $update = "UPDATE profile_ideal 
               SET pendidikan='$pendidikan', pengalaman='$pengalaman', 
                   keterampilan='$keterampilan', kondisi_fisik='$kondisi_fisik'
               WHERE id='$id'";
    mysqli_query($koneksi, $update);
    header("Location: profile_ideal.php");
    exit;
}

$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM profile_ideal WHERE id='$id'"));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile Ideal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">
    <h2 class="mb-4">Edit Profile Ideal - <?= $data['posisi'] ?></h2>
    <a href="profile_ideal.php" class="btn btn-secondary mb-3">⬅ Kembali</a>

    <form method="POST">
        <div class="mb-3">
            <label>Pendidikan</label>
            <input type="number" name="pendidikan" value="<?= $data['pendidikan'] ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Pengalaman</label>
            <input type="number" name="pengalaman" value="<?= $data['pengalaman'] ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Keterampilan</label>
            <input type="number" name="keterampilan" value="<?= $data['keterampilan'] ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Kondisi Fisik</label>
            <input type="number" name="kondisi_fisik" value="<?= $data['kondisi_fisik'] ?>" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
    </form>
</body>
</html>
