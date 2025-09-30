<?php
require_once "koneksi.php"; // Sesuaikan path koneksi Anda
session_start();

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'HRD' && $_SESSION['role'] != 'Owner')) {
    header("Location: login.php");
    exit;
}

// Bagian untuk menangani permintaan AJAX (JS) untuk mengambil data
if(isset($_GET['get_ideal_values'])) {
    $id_posisi = (int)$_GET['id_posisi'];
    $ideal_data = array();
    $query_ideal = mysqli_query($koneksi, "SELECT id_kriteria, nilai_ideal FROM profile_ideal WHERE id_posisi='$id_posisi'");
    while($row = mysqli_fetch_assoc($query_ideal)) {
        $ideal_data[$row['id_kriteria']] = $row['nilai_ideal'];
    }
    header('Content-Type: application/json');
    echo json_encode($ideal_data);
    exit;
}

// Logika untuk menyimpan data ke database
if(isset($_POST['simpan'])){
    $id_posisi = (int)$_POST['id_posisi'];
    if (!empty($id_posisi) && isset($_POST['nilai'])) {
        foreach($_POST['nilai'] as $id_kriteria => $nilai){
            $id_kriteria = (int)$id_kriteria;
            $nilai = (int)$nilai;
            // Cek apakah data sudah ada
            $cek = mysqli_query($koneksi, "SELECT * FROM profile_ideal WHERE id_posisi='$id_posisi' AND id_kriteria='$id_kriteria'");
            if(mysqli_num_rows($cek) > 0){
                // Jika sudah ada, update
                mysqli_query($koneksi, "UPDATE profile_ideal SET nilai_ideal='$nilai' WHERE id_posisi='$id_posisi' AND id_kriteria='$id_kriteria'");
            } else {
                // Jika belum ada, insert baru
                mysqli_query($koneksi, "INSERT INTO profile_ideal(id_posisi,id_kriteria,nilai_ideal) VALUES('$id_posisi','$id_kriteria','$nilai')");
            }
        }
        header("Location: ideal.php?msg=Profil ideal untuk posisi terpilih berhasil disimpan!");
        exit;
    } else {
        $error_msg = "Silakan pilih posisi terlebih dahulu.";
    }
}

// Ambil semua posisi dan kriteria untuk form
$posisi_query = mysqli_query($koneksi, "SELECT * FROM posisi ORDER BY nama_posisi");
$kriteria_query = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRD - Kelola Profil Ideal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
        }
        body { background-color: var(--light-bg); font-family: 'Segoe UI', 'Roboto', sans-serif; }
        .main-card {
            border: none; border-radius: 1.25rem; box-shadow: var(--shadow);
            background-color: var(--card-bg); padding: 2.5rem;
            max-width: 900px; margin: 2rem auto;
        }
        .table-nilai th { width: 50%; }
        .form-select {
            font-size: 1.1rem;
            padding: 0.75rem 1rem;
        }
        .loading-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255, 255, 255, 0.7); display: none;
            justify-content: center; align-items: center; z-index: 10;
            border-radius: 1.25rem;
        }
        .table-nilai { display: none; } /* Sembunyikan tabel by default */
    </style>
</head>
<body>
<div class="main-card position-relative">
    <div class="loading-overlay" id="loading-overlay">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
    </div>

    <div class="text-center mb-4">
        <h2 class="mb-1">Kelola Profil Ideal</h2>
        <p class="text-muted">Pilih posisi untuk mengatur atau memperbarui nilai ideal per kriteria.</p>
    </div>

    <form method="post" id="ideal-form">
        <div class="mb-4">
            <label for="id_posisi" class="form-label fs-5">Pilih Posisi:</label>
            <select name="id_posisi" id="id_posisi" class="form-select form-select-lg" required>
                <option value="">-- Silakan Pilih Posisi --</option>
                <?php while($p = mysqli_fetch_assoc($posisi_query)): ?>
                    <option value="<?= $p['id_posisi'] ?>"><?= htmlspecialchars($p['nama_posisi']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="table-responsive mt-4" id="table-nilai-container">
            <table class="table table-hover align-middle table-nilai" id="table-nilai">
                <thead class="table-light">
                    <tr>
                        <th>Kriteria</th>
                        <th class="text-center">Nilai Ideal (1-5)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($k = mysqli_fetch_assoc($kriteria_query)): ?>
                    <tr>
                        <td><?= htmlspecialchars($k['nama_kriteria']) ?></td>
                        <td class="text-center">
                            <input type="number" name="nilai[<?= $k['id_kriteria'] ?>]" class="form-control text-center"
                                   min="1" max="5" placeholder="1-5" required>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="ideal.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <button type="submit" name="simpan" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const posisiSelect = document.getElementById('id_posisi');
    const tableContainer = document.getElementById('table-nilai-container');
    const tableNilai = document.getElementById('table-nilai');
    const loadingOverlay = document.getElementById('loading-overlay');

    posisiSelect.addEventListener('change', function() {
        const id_posisi = this.value;
        const inputs = tableNilai.querySelectorAll('input[type="number"]');

        // Sembunyikan tabel dan reset input jika tidak ada posisi yang dipilih
        if (!id_posisi) {
            tableNilai.style.display = 'none';
            inputs.forEach(input => input.value = '');
            return;
        }

        // Tampilkan loading & tabel
        loadingOverlay.style.display = 'flex';
        tableNilai.style.display = 'table';

        // Ambil data nilai ideal yang sudah ada via AJAX
        fetch(`?get_ideal_values=1&id_posisi=${id_posisi}`)
            .then(response => response.json())
            .then(data => {
                inputs.forEach(input => {
                    // Ekstrak id_kriteria dari nama input, cth: nilai[3] -> 3
                    const id_kriteria = input.name.match(/\[(\d+)\]/)[1];
                    
                    // Isi value jika ada di data, jika tidak, kosongkan
                    if (data[id_kriteria]) {
                        input.value = data[id_kriteria];
                    } else {
                        input.value = ''; // Kosongkan jika belum pernah di-set
                    }
                });
            })
            .catch(error => {
                console.error('Gagal mengambil data:', error);
                alert('Terjadi kesalahan saat memuat data nilai ideal.');
            })
            .finally(() => {
                // Sembunyikan loading setelah selesai
                loadingOverlay.style.display = 'none';
            });
    });
});
</script>
</body>
</html>