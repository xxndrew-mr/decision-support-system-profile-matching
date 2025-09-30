<?php
require_once __DIR__ . "/../koneksi.php";
require_once __DIR__ . "/../partials/auth.php";
require_role('HRD');

$msg = "";
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
}

// hapus hasil ranking (khusus hasil terbaru)
if (isset($_POST['hapus_posisi'])) {
    $id_posisi = (int)$_POST['hapus_posisi'];
    mysqli_query($koneksi, "DELETE FROM hasil_ranking WHERE id_posisi=$id_posisi AND is_history=0");
    $msg = "Hasil ranking terbaru untuk posisi terpilih sudah dihapus.";
}

// Ambil daftar kriteria untuk header tabel dinamis
$kriteria_query = mysqli_query($koneksi, "SELECT id_kriteria, nama_kriteria FROM kriteria ORDER BY id_kriteria");
$kriteria_list = [];
while ($k = mysqli_fetch_assoc($kriteria_query)) {
    $kriteria_list[] = $k;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRD - Hasil Ranking Rinci</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
            --text-dark: #212529;
            --text-light: #6c757d;
        }
        body { background-color: var(--light-bg); font-family: 'Segoe UI', 'Roboto', sans-serif; }
        .main-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: var(--shadow);
            background-color: var(--card-bg);
            padding: 2.5rem;
        }
        .nav-pills .nav-link {
            border-radius: 0.75rem;
            font-weight: 500;
        }
        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.4);
        }
        .ranking-card {
            border: 1px solid #e9ecef;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .ranking-card h3 {
            font-weight: 600;
            color: var(--primary-color);
        }
        .table {
            border-collapse: separate;
            border-spacing: 0 0.5rem; /* Memberi jarak antar baris */
        }
        .table th, .table td {
            border: none;
            vertical-align: middle;
            text-align: center;
        }
        .table thead th {
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 1rem;
        }
        .table tbody tr {
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.04);
            border-radius: 0.5rem; /* Radius di setiap baris */
        }
        .table tbody td:first-child { border-top-left-radius: 0.5rem; border-bottom-left-radius: 0.5rem; }
        .table tbody td:last-child { border-top-right-radius: 0.5rem; border-bottom-right-radius: 0.5rem; }
        .table .rank-badge {
            display: inline-block;
            width: 35px;
            height: 35px;
            line-height: 35px;
            border-radius: 50%;
            font-weight: 700;
            color: #fff;
            background-color: var(--secondary-color);
        }
        .table .rank-badge.rank-1 { background-color: #ffc107; color: var(--text-dark); }
        .table .rank-badge.rank-2 { background-color: #ced4da; color: var(--text-dark); }
        .table .rank-badge.rank-3 { background-color: #cd7f32; }
        .table .nama-calon {
            text-align: left;
            font-weight: 500;
        }
        .footer { margin-top:3rem; text-align:center; color: var(--text-light); }
    </style>
</head>
<body>
<div class="container my-4">
    <ul class="nav nav-pills mb-4 justify-content-center">
        <li class="nav-item"><a class="nav-link" href="calon.php"><i class="fas fa-user-tie me-2"></i>Calon Karyawan</a></li>
        <li class="nav-item"><a class="nav-link active" href="ranking.php"><i class="fas fa-trophy me-2"></i>Hasil Ranking</a></li>
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
    </ul>

    <div class="main-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Hasil Ranking Kandidat</h2>
            <div class="d-flex gap-2">
                <form action="proses.php" method="POST" style="display:inline;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync-alt me-2"></i>Proses Ulang Ranking
                    </button>
                </form>
                <form action="../save_all.php" method="POST" style="display:inline;">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-archive me-2"></i>Arsipkan Hasil
                    </button>
                </form>
            </div>
        </div>

        <?php if(!empty($msg)): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php
        $posisi_hasil = mysqli_query($koneksi, "SELECT DISTINCT p.id_posisi, p.nama_posisi 
            FROM posisi p 
            JOIN hasil_ranking hr ON hr.id_posisi=p.id_posisi 
            WHERE hr.is_history=0
            ORDER BY p.nama_posisi");
        
        if (mysqli_num_rows($posisi_hasil) == 0) {
            echo "<div class='alert alert-warning text-center'>Belum ada hasil ranking yang diproses. Silakan lakukan 'Proses Ranking' terlebih dahulu.</div>";
        }

        while($pos = mysqli_fetch_assoc($posisi_hasil)):
        ?>
            <div class='ranking-card'>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>Posisi: <?= htmlspecialchars($pos['nama_posisi']) ?></h3>
                    <form method='post' onsubmit="return confirm('Hapus semua hasil ranking terbaru untuk posisi ini?');">
                        <input type='hidden' name='hapus_posisi' value='<?= $pos['id_posisi'] ?>'>
                        <button type='submit' class='btn btn-outline-danger btn-sm'><i class='fas fa-trash-alt me-2'></i>Hapus Hasil Posisi Ini</button>
                    </form>
                </div>
            
                <?php
                $penilaian_query = mysqli_query($koneksi, "SELECT p.id_calon, p.id_kriteria, p.nilai
                    FROM penilaian p
                    JOIN hasil_ranking hr ON p.id_calon = hr.id_calon AND p.id_posisi = hr.id_posisi
                    WHERE hr.id_posisi={$pos['id_posisi']} AND hr.is_history=0");

                $nilai_per_calon = [];
                while ($nilai_row = mysqli_fetch_assoc($penilaian_query)) {
                    $nilai_per_calon[$nilai_row['id_calon']][$nilai_row['id_kriteria']] = $nilai_row['nilai'];
                }

                $hasil_rank = mysqli_query($koneksi, "SELECT hr.*, c.nama, c.id_calon
                    FROM hasil_ranking hr 
                    JOIN calon_karyawan c ON c.id_calon=hr.id_calon 
                    WHERE hr.id_posisi={$pos['id_posisi']} AND hr.is_history=0
                    ORDER BY peringkat ASC");
                ?>
                <div class='table-responsive'>
                    <table class='table'>
                        <thead>
                            <tr>
                                <th>Peringkat</th>
                                <th class="text-start">Nama Calon</th>
                                <?php foreach ($kriteria_list as $k): ?>
                                    <th><?= htmlspecialchars($k['nama_kriteria']) ?></th>
                                <?php endforeach; ?>
                                <th>Total Nilai</th>
                                <th>Status Validasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($r = mysqli_fetch_assoc($hasil_rank)): ?>
                                <tr>
                                    <td>
                                        <span class="rank-badge rank-<?= $r['peringkat'] <= 3 ? $r['peringkat'] : 'default' ?>">
                                            <?= $r['peringkat'] ?>
                                        </span>
                                    </td>
                                    <td class="nama-calon"><?= htmlspecialchars($r['nama']) ?></td>
                                    <?php foreach ($kriteria_list as $k):
                                        $nilai = $nilai_per_calon[$r['id_calon']][$k['id_kriteria']] ?? '-';
                                    ?>
                                        <td><?= $nilai ?></td>
                                    <?php endforeach; ?>
                                    <td><strong><?= number_format($r['total_nilai'], 2) ?></strong></td>
                                    <td>
                                        <?php
                                        $badge_class = 'bg-secondary';
                                        if ($r['validasi_owner'] === 'Disetujui') {
                                            $badge_class = 'bg-success';
                                        } else if ($r['validasi_owner'] === 'Ditolak') {
                                            $badge_class = 'bg-danger';
                                        } else if ($r['validasi_owner'] === 'Pending') {
                                            $badge_class = 'bg-warning text-dark';
                                        }
                                        ?>
                                        <span class='badge <?= $badge_class ?>'><?= htmlspecialchars($r['validasi_owner']) ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endwhile; ?>

        <footer class="footer">
            <p class="text-center text-muted mt-4">Hasil ranking akan diarsipkan setelah menekan tombol "Arsipkan Hasil". Owner dapat memvalidasi hasil di panel Owner.</p>
        </footer>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>