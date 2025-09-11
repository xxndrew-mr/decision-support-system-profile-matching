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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRD - Hasil Ranking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --light-bg: #f0f2f5;
            --dark-bg: #e9ecef;
            --card-bg: #fff;
            --shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        body { background-color: var(--light-bg); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
        .container { margin-top: 2rem; margin-bottom: 2rem; }
        .card { border: none; border-radius: 1rem; box-shadow: var(--shadow); background-color: var(--card-bg); padding: 2.5rem; transition: all 0.3s ease-in-out; }
        .nav-link.active { font-weight: bold; color: var(--primary-color) !important; }
        .nav-link { color: var(--secondary-color); transition: color 0.3s; }
        .nav-link:hover { color: var(--primary-color); }
        h2 { font-weight: 700; color: #333; margin-bottom: 1.5rem; }
        h3 { font-weight: 600; color: #555; margin-top: 2rem; margin-bottom: 1rem; }
        .ranking-card { margin-bottom: 2rem; }
        .btn { border-radius: 0.5rem; padding: 0.75rem 1.5rem; transition: transform 0.2s, box-shadow 0.2s; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); }
        .btn-danger { background-color: var(--danger-color); border-color: var(--danger-color); }
        table { background-color: var(--card-bg); border-radius: 1rem; overflow: hidden; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); }
        th, td { vertical-align: middle; }
        footer { margin-top: 2rem; text-align: center; color: #6c757d; }
        .alert { border-radius: 0.75rem; }
    </style>
</head>
<body>
<div class="container">
    <!-- Navigation Menu -->
    <ul class="nav nav-tabs justify-content-center mb-4">
        <li class="nav-item"><a class="nav-link" href="calon.php"><i class="fas fa-user-tie"></i> Calon Karyawan</a></li>
        <li class="nav-item"><a class="nav-link" href="penilaian.php"><i class="fas fa-chart-bar"></i> Penilaian</a></li>
        <li class="nav-item"><a class="nav-link active" href="ranking.php"><i class="fas fa-trophy"></i> Hasil Ranking</a></li>
        <li class="nav-item"><a class="nav-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a></li>
    </ul>

    <div class="card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Hasil Ranking (Profile Matching)</h2>
            <div>
                <form action="proses.php" method="POST" style="display:inline;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Proses Ranking
                    </button>
                </form>
                <form action="../save_all.php" method="POST" style="display:inline;">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Save All
                    </button>
                </form>
            </div>
        </div>

        <?php if(!empty($msg)): ?>
            <div class="alert alert-success mt-3" role="alert">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <?php
        // tampilkan hasil per posisi (hanya data terbaru, bukan history)
        $posisi_hasil = mysqli_query($koneksi, "SELECT DISTINCT p.id_posisi, p.nama_posisi 
            FROM posisi p 
            JOIN hasil_ranking hr ON hr.id_posisi=p.id_posisi 
            WHERE hr.is_history=0
            ORDER BY p.nama_posisi");

        while($pos = mysqli_fetch_assoc($posisi_hasil)):
            echo "<div class='ranking-card'>";
            echo "<h3>Posisi: ".htmlspecialchars($pos['nama_posisi'])."</h3>";
            
            // tombol hapus
            echo "<form method='post' onsubmit=\"return confirm('Hapus semua hasil ranking terbaru untuk posisi ini?');\" class='mb-3'>";
            echo "<input type='hidden' name='hapus_posisi' value='{$pos['id_posisi']}'>";
            echo "<button type='submit' class='btn btn-danger'><i class='fas fa-trash-alt'></i> Hapus Ranking Posisi Ini</button>";
            echo "</form>";

            $hasil_rank = mysqli_query($koneksi, "SELECT hr.*, c.nama 
                FROM hasil_ranking hr 
                JOIN calon_karyawan c ON c.id_calon=hr.id_calon 
                WHERE hr.id_posisi={$pos['id_posisi']} 
                AND hr.is_history=0
                ORDER BY peringkat ASC");

            echo "<div class='table-responsive'>";
            echo "<table class='table table-striped table-hover'><thead><tr>
                <th>Peringkat</th><th>Calon</th><th>Total Nilai</th><th>Status Validasi Owner</th>
                </tr></thead><tbody>";
            while($r = mysqli_fetch_assoc($hasil_rank)) {
                $badge_class = 'bg-warning text-dark';
                if ($r['validasi_owner'] === 'Disetujui') {
                    $badge_class = 'bg-success';
                } else if ($r['validasi_owner'] === 'Ditolak') {
                    $badge_class = 'bg-danger';
                }
                echo "<tr>
                    <td>{$r['peringkat']}</td>
                    <td>".htmlspecialchars($r['nama'])."</td>
                    <td>".number_format($r['total_nilai'],2)."</td>
                    <td><span class='badge {$badge_class}'>{$r['validasi_owner']}</span></td>
                </tr>";
            }
            echo "</tbody></table></div>";
            echo "</div>"; // end ranking-card
        endwhile;
        ?>

        <footer>
            <p class="text-center text-muted mt-4">Owner dapat memvalidasi hasil di panel Owner.</p>
            <p class="text-center text-muted">&copy; HRD Panel • SPK Profile Matching</p>
        </footer>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
