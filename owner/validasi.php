<?php
require_once __DIR__ . "/../koneksi.php";
require_once __DIR__ . "/../partials/auth.php";
require_role('Owner');

// Ambil daftar posisi
$posisi = mysqli_query($koneksi, "SELECT * FROM posisi ORDER BY id_posisi");
$selected = isset($_GET['id_posisi']) ? (int)$_GET['id_posisi'] : 0;

// Query hasil ranking (hanya data aktif, tidak termasuk history)
$sql = "SELECT 
            hr.id_hasil,
            hr.id_posisi,
            hr.id_calon,
            hr.peringkat,
            hr.total_nilai,
            hr.validasi_owner,
            c.nama,
            p.nama_posisi
        FROM hasil_ranking hr
        JOIN calon_karyawan c ON c.id_calon = hr.id_calon
        JOIN posisi p ON p.id_posisi = hr.id_posisi
        WHERE hr.is_history = 0";  // <-- filter penting

if ($selected) $sql .= " AND hr.id_posisi=$selected";

$sql .= " ORDER BY p.id_posisi, hr.peringkat";

$hasil = mysqli_query($koneksi, $sql);

// Proses validasi
if (isset($_POST['aksi']) && isset($_POST['id_hasil'])) {
    $id_hasil = (int)$_POST['id_hasil'];
    $status = $_POST['aksi'] === 'setujui' ? 'Disetujui' : 'Ditolak';
    mysqli_query($koneksi, "UPDATE hasil_ranking SET validasi_owner='$status' WHERE id_hasil=$id_hasil");
    header("Location: validasi.php?id_posisi=" . $selected);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Owner - Validasi Ranking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .header { background-color: #007bff; color: white; padding: 2rem 1rem; text-align: center; margin-bottom: 2rem; }
        .header h1 { margin-bottom: 0.5rem; }
        .container { max-width: 1200px; }
        .card-custom { border: none; border-radius: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .table-custom th, .table-custom td { vertical-align: middle; }
        .table-custom tbody tr:hover { background-color: #f1f1f1; }
        .btn-custom { border-radius: 50rem; padding: .5rem 1.5rem; }
        .btn-logout { background-color: #dc3545; color: white; border-radius: 50rem; }
        .btn-logout:hover { background-color: #c82333; color: white; }
        .footer { text-align: center; padding: 1rem; color: #6c757d; font-size: 0.9rem; }
        .badge-info { background-color: #17a2b8; }
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
    </style>
</head>
<body>
    <header class="header">
        <h1>Dashboard Owner</h1>
        <h4>Validasi Hasil Ranking</h4>
    </header>

    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <form method="get" class="d-flex align-items-center flex-grow-1">
                <label class="form-label me-2 mb-0 fw-bold text-nowrap">Filter Posisi:</label>
                <select name="id_posisi" class="form-select" onchange="this.form.submit()">
                    <option value="0">Semua Posisi</option>
                    <?php while($p = mysqli_fetch_assoc($posisi)): ?>
                        <option value="<?= $p['id_posisi'] ?>" <?= $selected == $p['id_posisi'] ? "selected" : "" ?>>
                            <?= htmlspecialchars($p['nama_posisi']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
            <div class="d-flex gap-2">
                <a href="ganti_password.php" class="btn btn-primary btn-custom">Ganti Password</a>
                <a href="kelola_hrd.php" class="btn btn-secondary btn-custom">Kelola HRD</a>
                <a class="btn btn-logout btn-custom" href="../logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>

        <div class="card card-custom">
            <div class="card-body">
                <h2 class="text-center mb-4">Hasil Ranking</h2>
                <div class="table-responsive">
                    <table class="table table-hover table-custom">
                        <thead class="bg-light">
                            <tr>
                                <th>Posisi</th>
                                <th>Peringkat</th>
                                <th>Calon</th>
                                <th>Total Nilai</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($hasil) > 0): ?>
                                <?php while($r = mysqli_fetch_assoc($hasil)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['nama_posisi']); ?></td>
                                        <td><?= $r['peringkat']; ?></td>
                                        <td><?= htmlspecialchars($r['nama']); ?></td>
                                        <td><?= number_format($r['total_nilai'], 2); ?></td>
                                        <td>
                                            <?php
                                                $badge_class = 'badge-info';
                                                if ($r['validasi_owner'] == 'Disetujui') $badge_class = 'badge-success';
                                                elseif ($r['validasi_owner'] == 'Ditolak') $badge_class = 'badge-danger';
                                            ?>
                                            <span class="badge <?= $badge_class; ?>">
                                                <?= htmlspecialchars($r['validasi_owner']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="post" class="d-flex gap-2">
                                                <input type="hidden" name="id_hasil" value="<?= $r['id_hasil']; ?>" />
                                                <button class="btn btn-sm btn-success" type="submit" name="aksi" value="setujui">
                                                    <i class="fas fa-check me-1"></i>Setujui
                                                </button>
                                                <button class="btn btn-sm btn-danger" type="submit" name="aksi" value="tolak">
                                                    <i class="fas fa-times me-1"></i>Tolak
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Tidak ada data hasil ranking.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2023 SPK Karyawan • Hak Cipta Dilindungi</p>
    </footer>
</body>
</html>
