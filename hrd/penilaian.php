<?php
require_once __DIR__ . "/../koneksi.php";
require_once __DIR__ . "/../partials/auth.php";
require_role('HRD');

// Ambil kriteria
$kriteria = [];
$qk = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria");
while ($row = mysqli_fetch_assoc($qk)) $kriteria[] = $row;

// Simpan penilaian
if (isset($_POST['simpan'])) {
    $id_calon = (int)$_POST['id_calon'];
    $id_posisi = (int)$_POST['id_posisi'];
    $id_user = (int)$_SESSION['id_user']; // HRD yang sedang login

    foreach ($kriteria as $k) {
        $idk = (int)$k['id_kriteria'];
        $nilai = (int)($_POST['nilai'][$idk] ?? 0);

        // Upsert penilaian per calon & posisi
        $cek = mysqli_query($koneksi, "SELECT id_penilaian FROM penilaian 
            WHERE id_calon=$id_calon AND id_kriteria=$idk AND id_posisi=$id_posisi");
        if (mysqli_num_rows($cek)) {
            mysqli_query($koneksi, "UPDATE penilaian 
                SET nilai=$nilai, created_by=$id_user 
                WHERE id_calon=$id_calon AND id_kriteria=$idk AND id_posisi=$id_posisi");
        } else {
            mysqli_query($koneksi, "INSERT INTO penilaian(id_calon,id_kriteria,id_posisi,nilai,created_by) 
                VALUES($id_calon,$idk,$id_posisi,$nilai,$id_user)");
        }
    }
}

// Data
$calon = mysqli_query($koneksi, "SELECT * FROM calon_karyawan ORDER BY nama");
$posisi = mysqli_query($koneksi, "SELECT * FROM posisi ORDER BY id_posisi");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRD - Penilaian</title>
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
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        .container {
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            background-color: var(--card-bg);
            padding: 2.5rem;
            transition: all 0.3s ease-in-out;
        }
        .nav-link.active {
            font-weight: bold;
            color: var(--primary-color) !important;
        }
        .nav-link {
            color: var(--secondary-color);
            transition: color 0.3s;
        }
        .nav-link:hover {
            color: var(--primary-color);
        }
        h2 {
            font-weight: 700;
            color: #333;
            margin-bottom: 1.5rem;
        }
        h3 {
            font-weight: 600;
            color: #555;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .form-select, .form-control {
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .btn {
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        table {
            background-color: var(--card-bg);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        th, td {
            vertical-align: middle;
        }
        footer {
            margin-top: 2rem;
            text-align: center;
            color: #6c757d;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Navigation Menu -->
    <ul class="nav nav-tabs justify-content-center mb-4">
        <li class="nav-item">
            <a class="nav-link" href="calon.php">
                <i class="fas fa-user-tie"></i> Calon Karyawan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="penilaian.php">
                <i class="fas fa-chart-bar"></i> Penilaian
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="ranking.php">
                <i class="fas fa-trophy"></i> Hasil Ranking
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-danger" href="../logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="dashboard.php">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </li>
    </ul>

    <div class="card">
        <h2>Input Penilaian</h2>
        <form method="post">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pilih Calon</label>
                    <select name="id_calon" class="form-select" required>
                        <option value="">- Pilih -</option>
                        <?php while($c = mysqli_fetch_assoc($calon)): ?>
                            <option value="<?php echo $c['id_calon']; ?>"><?php echo htmlspecialchars($c['nama']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pilih Posisi</label>
                    <select name="id_posisi" class="form-select" required>
                        <option value="">- Pilih -</option>
                        <?php mysqli_data_seek($posisi, 0); while($p = mysqli_fetch_assoc($posisi)): ?>
                            <option value="<?php echo $p['id_posisi']; ?>"><?php echo htmlspecialchars($p['nama_posisi']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

           <h3 class="mt-4">Kriteria Penilaian</h3>
            <div class="row">
                <?php foreach ($kriteria as $k): ?>
                    <?php
                        // Ambil subkriteria dari database sesuai id_kriteria
                        $id_k = (int)$k['id_kriteria'];
                        $sql = "SELECT * FROM subkriteria WHERE id_kriteria = $id_k ORDER BY nilai ASC";
                        $result = mysqli_query($koneksi, $sql);
                    ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100 p-3 shadow-sm border-0">
                            <label class="form-label fw-bold mb-2 text-primary"><?php echo htmlspecialchars($k['nama_kriteria']); ?></label>
                            <select name="nilai[<?php echo $k['id_kriteria']; ?>]" class="form-select" required>
                                <option value="">- Pilih Nilai -</option>
                                <?php while($s = mysqli_fetch_assoc($result)): ?>
                                    <option value="<?php echo $s['nilai']; ?>">
                                        <?php echo htmlspecialchars($s['nama_subkriteria']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="d-grid mt-4">
                <button class="btn btn-primary btn-lg" type="submit" name="simpan">
                    <i class="fas fa-save"></i> Simpan Nilai
                </button>
            </div>
        </form>

        <h3 class="mt-5">Nilai Terisi</h3>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Calon</th>
                        <th>Posisi</th>
                        <?php foreach ($kriteria as $k) echo "<th>".htmlspecialchars($k['nama_kriteria'])."</th>"; ?>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Re-fetch data for the table
                $qdata = mysqli_query($koneksi, "SELECT c.nama, p.nama_posisi, c.id_calon, p.id_posisi 
                    FROM calon_karyawan c 
                    JOIN penilaian pn ON pn.id_calon=c.id_calon 
                    JOIN posisi p ON pn.id_posisi=p.id_posisi 
                    GROUP BY c.id_calon, p.id_posisi
                    ORDER BY c.nama");
                while($row = mysqli_fetch_assoc($qdata)):
                    echo "<tr><td>".htmlspecialchars($row['nama'])."</td>";
                    echo "<td>".htmlspecialchars($row['nama_posisi'])."</td>";
                    foreach ($kriteria as $k) {
                        $idk = (int)$k['id_kriteria'];
                        $qv = mysqli_query($koneksi, "SELECT nilai FROM penilaian 
                            WHERE id_calon={$row['id_calon']} 
                            AND id_kriteria=$idk 
                            AND id_posisi={$row['id_posisi']}");
                        $v = mysqli_fetch_assoc($qv) ?: [];
                        echo "<td>".(isset($v['nilai']) ? (int)$v['nilai'] : "-")."</td>";
                    }
                    echo "</tr>";
                endwhile;
                ?>
                </tbody>
            </table>
        </div>
        <footer>
            <p class="text-center text-muted mt-4">Gunakan halaman "Proses Ranking" untuk menghitung peringkat per posisi.</p>
            <p class="text-center text-muted">&copy; HRD Panel • SPK Profile Matching</p>
        </footer>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
