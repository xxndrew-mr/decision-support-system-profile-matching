<?php
require_once __DIR__ . "/../koneksi.php";
require_once __DIR__ . "/../partials/auth.php";
require_role('HRD');

$msg = "";
if (isset($_GET['msg'])) $msg = $_GET['msg'];

// Ambil filter (jika ada)
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : "";
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : "";
$filter_tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : "";

// Buat query filter
$where = [];
if (!empty($filter_tahun)) {
    $where[] = "YEAR(hr.created_at) = '".intval($filter_tahun)."'";
}
if (!empty($filter_bulan)) {
    $where[] = "MONTH(hr.created_at) = '".intval($filter_bulan)."'";
}
if (!empty($filter_tanggal)) {
    $where[] = "DAY(hr.created_at) = '".intval($filter_tanggal)."'";
}
$where_sql = "";
if (!empty($where)) {
    $where_sql = " AND " . implode(" AND ", $where);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRD - Riwayat Ranking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --light-bg: #f0f2f5;
            --card-bg: #fff;
            --shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        body { background-color: var(--light-bg); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
        .container { margin-top: 2rem; margin-bottom: 2rem; }
        .card { border: none; border-radius: 1rem; box-shadow: var(--shadow); background-color: var(--card-bg); padding: 2.5rem; transition: all 0.3s ease-in-out; }
        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); }
        h2 { font-weight: 700; color: #333; margin-bottom: 1.5rem; }
        .table { background-color: var(--card-bg); border-radius: 1rem; overflow: hidden; }
        .table thead th { background-color: #e9ecef; }
    </style>
</head>
<body>

<ul class="nav nav-tabs justify-content-center mb-4">
    <li class="nav-item">
        <a class="nav-link" href="dashboard.php">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </li>
</ul>

<div class="container">
    <div class="card">
        <h2 class="text-center">Riwayat Ranking</h2>

        <!-- FILTER FORM -->
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <select name="tahun" class="form-select">
                    <option value="">Pilih Tahun</option>
                    <?php for ($y = date("Y"); $y >= 2000; $y--): ?>
                        <option value="<?= $y ?>" <?= $filter_tahun==$y?"selected":"" ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="bulan" class="form-select">
                    <option value="">Pilih Bulan</option>
                    <?php for ($m=1; $m<=12; $m++): ?>
                        <option value="<?= $m ?>" <?= $filter_bulan==$m?"selected":"" ?>>
                            <?= date("F", mktime(0,0,0,$m,1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="tanggal" class="form-select">
                    <option value="">Pilih Tanggal</option>
                    <?php for ($d=1; $d<=31; $d++): ?>
                        <option value="<?= $d ?>" <?= $filter_tanggal==$d?"selected":"" ?>><?= $d ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="hapus_riwayat.php?all=1" 
                   onclick="return confirm('Yakin ingin menghapus SEMUA riwayat?')" 
                   class="btn btn-danger w-100">Hapus Semua</a>
            </div>
        </form>

        <?php if(!empty($msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <?php
        $q = mysqli_query($koneksi, "SELECT hr.*, c.nama, c.id_calon, p.nama_posisi, r.nama_rekrutmen
            FROM hasil_ranking hr
            JOIN calon_karyawan c ON hr.id_calon=c.id_calon
            JOIN posisi p ON hr.id_posisi=p.id_posisi
            LEFT JOIN rekrutmen r ON c.id_rekrutmen = r.id_rekrutmen
            WHERE hr.is_history=1 $where_sql
            ORDER BY hr.created_at DESC, p.nama_posisi, hr.peringkat");

        $riwayat = [];
        while ($r = mysqli_fetch_assoc($q)) {
            $posisi = $r['nama_posisi'];
            $tanggal = date("d-m-Y", strtotime($r['created_at']));
            if (!isset($riwayat[$posisi])) $riwayat[$posisi] = [];
            if (!isset($riwayat[$posisi][$tanggal])) $riwayat[$posisi][$tanggal] = [];
            $riwayat[$posisi][$tanggal][] = $r;
        }

        if (empty($riwayat)): ?>
            <div class="alert alert-info text-center" role="alert">
                Tidak ada riwayat ranking yang tersedia.
            </div>
        <?php else: ?>
            <?php foreach ($riwayat as $posisi => $data_posisi): ?>
                <div class="mt-4">
                    <h4 class="text-primary mb-3">Posisi: <?= htmlspecialchars($posisi) ?></h4>
                    <?php foreach ($data_posisi as $tanggal => $data_tanggal): ?>
                        <h6 class="mt-3 text-muted">Tanggal: <?= htmlspecialchars($tanggal) ?></h6>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Calon</th>
                                        <th>Peringkat</th>
                                        <th>Total Nilai</th>
                                        <th>Batch Rekrutmen</th>
                                        <th>Status Validasi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data_tanggal as $r): ?>
                                        <tr>
                                            <td>
                                                <a href="#" 
                                                   class="calon-detail" 
                                                   data-id="<?= $r['id_calon'] ?>">
                                                   <?= htmlspecialchars($r['nama']) ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($r['peringkat']) ?></td>
                                            <td><?= number_format($r['total_nilai'], 2) ?></td>
                                            <td><?= htmlspecialchars($r['nama_rekrutmen'] ?: '-') ?></td>
                                            <td><?= htmlspecialchars($r['validasi_owner']) ?></td>
                                            <td class="actions-cell">
                                                <a href="hapus_riwayat.php?id=<?= htmlspecialchars($r['id_hasil']) ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Detail Calon -->
<div class="modal fade" id="calonModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Calon</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body" id="calonDetailContent">
        Memuat data...
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".calon-detail").forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.preventDefault();
            let idCalon = this.dataset.id;
            let modal = new bootstrap.Modal(document.getElementById("calonModal"));
            modal.show();
            document.getElementById("calonDetailContent").innerHTML = "Memuat data...";
            fetch("detail_calon.php?id=" + idCalon)
                .then(response => response.text())
                .then(data => {
                    document.getElementById("calonDetailContent").innerHTML = data;
                })
                .catch(err => {
                    document.getElementById("calonDetailContent").innerHTML = "<p class='text-danger'>Gagal memuat data.</p>";
                });
        });
    });
});
</script>
</body>
</html>
