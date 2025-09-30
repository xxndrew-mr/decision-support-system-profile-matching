<?php
require_once __DIR__ . "/../koneksi.php";
require_once __DIR__ . "/../partials/auth.php";
require_role('Owner');

// Ambil daftar posisi untuk filter
$posisi = mysqli_query($koneksi, "SELECT * FROM posisi ORDER BY id_posisi");
$selected = isset($_GET['id_posisi']) ? (int)$_GET['id_posisi'] : 0;

// =================================================================
// PERUBAHAN 1: Ambil daftar kriteria untuk header tabel dinamis
// =================================================================
$kriteria_query = mysqli_query($koneksi, "SELECT id_kriteria, nama_kriteria FROM kriteria ORDER BY id_kriteria");
$kriteria_list = [];
while ($k = mysqli_fetch_assoc($kriteria_query)) {
    $kriteria_list[] = $k;
}

// Query hasil ranking (hanya data aktif, tidak termasuk history)
$sql = "SELECT
            hr.id_hasil, hr.id_posisi, hr.id_calon, hr.peringkat,
            hr.total_nilai, hr.validasi_owner, c.nama, p.nama_posisi, r.nama_rekrutmen
        FROM hasil_ranking hr
        JOIN calon_karyawan c ON c.id_calon = hr.id_calon
        JOIN posisi p ON p.id_posisi = hr.id_posisi
        LEFT JOIN rekrutmen r ON r.id_rekrutmen = hr.id_rekrutmen
        WHERE hr.is_history = 0";

if ($selected) $sql .= " AND hr.id_posisi=$selected";

$sql .= " ORDER BY p.id_posisi, hr.peringkat";
$hasil = mysqli_query($koneksi, $sql);

// Proses validasi
if (isset($_POST['aksi']) && isset($_POST['id_hasil'])) {
    $id_hasil = (int)$_POST['id_hasil'];
    $status = $_POST['aksi'] === 'setujui' ? 'Disetujui' : 'Ditolak';
    $id_owner = $_SESSION['id_user'];
    mysqli_query($koneksi, "UPDATE hasil_ranking SET validasi_owner='$status', validated_by=$id_owner WHERE id_hasil=$id_hasil");
    header("Location: validasi.php?id_posisi=" . $selected);
    exit;
}

// =========================================================================================
// PERUBAHAN 2: Ambil semua data penilaian untuk posisi ini dalam satu query (lebih efisien)
// =========================================================================================
$penilaian_query = mysqli_query($koneksi, "SELECT p.id_calon, p.id_posisi, p.id_kriteria, p.nilai
    FROM penilaian p
    JOIN hasil_ranking hr ON p.id_calon = hr.id_calon AND p.id_posisi = hr.id_posisi
    WHERE hr.is_history = 0");

$nilai_per_calon = [];
while ($nilai_row = mysqli_fetch_assoc($penilaian_query)) {
    // Struktur data: $nilai_per_calon[id_calon][id_posisi][id_kriteria] = nilai
    $nilai_per_calon[$nilai_row['id_calon']][$nilai_row['id_posisi']][$nilai_row['id_kriteria']] = $nilai_row['nilai'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Owner - Validasi Hasil Ranking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
            --gradient-primary: linear-gradient(135deg, #0d6efd 0%, #4dabf7 100%);
        }
        
        body { background-color: var(--light-bg); font-family: 'Segoe UI', 'Roboto', sans-serif; }

        .header {
            background: var(--gradient-primary);
            color: white;
            padding: 2.5rem 1rem 4rem 1rem;
            text-align: center;
            border-bottom-left-radius: 2rem;
            border-bottom-right-radius: 2rem;
        }

        .header h1 { font-weight: 700; margin-bottom: 0.5rem; }
        .header h4 { font-weight: 300; opacity: 0.9; }

        .container { max-width: 1600px; margin-top: -2.5rem; position: relative; z-index: 10;}
        
        .main-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: var(--shadow);
            background-color: var(--card-bg);
            padding: 2rem;
        }

        .filter-bar {
             background-color: #f1f3f5;
             padding: 1rem 1.5rem;
             border-radius: 1rem;
        }

        .table {
            border-collapse: separate;
            border-spacing: 0 0.75rem;
        }
        .table th, .table td {
            border: none;
            vertical-align: middle;
            text-align: center;
            padding: 1rem;
        }
        .table thead th {
            border-bottom: 2px solid #dee2e6;
        }
        .table tbody tr {
            background-color: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .table tbody tr:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }
        .table td:first-child { border-top-left-radius: 0.75rem; border-bottom-left-radius: 0.75rem; }
        .table td:last-child { border-top-right-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }

        .rank-badge {
            display: inline-block;
            width: 35px;
            height: 35px;
            line-height: 35px;
            border-radius: 50%;
            font-weight: 700;
            color: #fff;
            background-color: #6c757d;
        }
        .rank-badge.rank-1 { background-color: #ffc107; color: #212529; }
        .rank-badge.rank-2 { background-color: #ced4da; color: #212529; }
        .rank-badge.rank-3 { background-color: #cd7f32; }

        .footer { text-align: center; padding: 2rem; color: #6c757d; font-size: 0.9rem; }
    </style>
</head>
<body>
    <header class="header">
        <h1>Dashboard Owner</h1>
        <h4>Validasi Hasil Ranking</h4>
    </header>

    <div class="container">
        <div class="main-card">
            <div class="filter-bar mb-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <form method="get" class="d-flex align-items-center flex-grow-1">
                        <label class="form-label me-2 mb-0 fw-bold text-nowrap">Filter Posisi:</label>
                        <select name="id_posisi" class="form-select" onchange="this.form.submit()">
                            <option value="0">Tampilkan Semua Posisi</option>
                            <?php mysqli_data_seek($posisi, 0); while($p = mysqli_fetch_assoc($posisi)): ?>
                                <option value="<?= $p['id_posisi'] ?>" <?= $selected == $p['id_posisi'] ? "selected" : "" ?>>
                                    <?= htmlspecialchars($p['nama_posisi']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </form>
                    <div class="d-flex gap-2">
                        <a href="cetak_validasi.php?id_posisi=<?= $selected ?>" target="_blank" class="btn btn-info text-white">
                            <i class="fas fa-print me-2"></i>Cetak PDF
                        </a>
                        <a href="kelola_hrd.php" class="btn btn-secondary">
                           <i class="fas fa-users-cog me-2"></i>Kelola HRD
                        </a>
                        <a class="btn btn-danger" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                         <a href="ganti_password.php" class="btn btn-warning text-dark">
        <i class="fas fa-key me-2"></i>Ganti Password
    </a>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Posisi</th>
                            <th>Peringkat</th>
                            <th class="text-start">Nama Calon</th>
                            <?php foreach ($kriteria_list as $k): ?>
                                <th><?= htmlspecialchars($k['nama_kriteria']); ?></th>
                            <?php endforeach; ?>
                            <th>Total Nilai</th>
                            <th>Status</th>
                            <th>Batch</th>
                            <th>Aksi Validasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($hasil) > 0): ?>
                            <?php mysqli_data_seek($hasil, 0); while($r = mysqli_fetch_assoc($hasil)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['nama_posisi']); ?></td>
                                    <td>
                                        <span class="rank-badge rank-<?= $r['peringkat'] <= 3 ? $r['peringkat'] : 'default' ?>">
                                            <?= $r['peringkat']; ?>
                                        </span>
                                    </td>
                                    <td class="text-start"><?= htmlspecialchars($r['nama']); ?></td>
                                    <?php foreach ($kriteria_list as $k):
                                        $nilai = $nilai_per_calon[$r['id_calon']][$r['id_posisi']][$k['id_kriteria']] ?? '-';
                                    ?>
                                        <td><?= $nilai ?></td>
                                    <?php endforeach; ?>
                                    <td><strong><?= number_format($r['total_nilai'], 2); ?></strong></td>
                                    <td>
                                        <?php
                                            $badge_class = 'bg-warning text-dark';
                                            if ($r['validasi_owner'] == 'Disetujui') $badge_class = 'bg-success';
                                            elseif ($r['validasi_owner'] == 'Ditolak') $badge_class = 'bg-danger';
                                        ?>
                                        <span class="badge rounded-pill <?= $badge_class; ?>">
                                            <?= htmlspecialchars($r['validasi_owner']); ?>
                                        </span>
                                    </td>
                                    <td><?= $r['nama_rekrutmen'] ? htmlspecialchars($r['nama_rekrutmen']) : '-' ?></td>
                                    <td>
                                        <form method="post" class="d-flex gap-2 justify-content-center">
                                            <input type="hidden" name="id_hasil" value="<?= $r['id_hasil']; ?>" />
                                            <button class="btn btn-sm btn-success rounded-pill" type="submit" name="aksi" value="setujui" title="Setujui">
                                                <i class="fas fa-check"></i> Setujui
                                            </button>
                                            <button class="btn btn-sm btn-danger rounded-pill" type="submit" name="aksi" value="tolak" title="Tolak">
                                                <i class="fas fa-times"></i> Tolak
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= 8 + count($kriteria_list) ?>" class="text-center text-muted p-5">
                                    Tidak ada data hasil ranking untuk divalidasi.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> DWI BHAKTI OFFSET • Hak Cipta Dilindungi</p>
    </footer>
</body>
</html>