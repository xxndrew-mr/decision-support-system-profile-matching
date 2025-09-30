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
$filter_nama = isset($_GET['nama']) ? mysqli_real_escape_string($koneksi, $_GET['nama']) : "";

// Buat query filter
$where = [];
if (!empty($filter_tahun)) $where[] = "YEAR(hr.created_at) = '" . intval($filter_tahun) . "'";
if (!empty($filter_bulan)) $where[] = "MONTH(hr.created_at) = '" . intval($filter_bulan) . "'";
if (!empty($filter_tanggal)) $where[] = "DAY(hr.created_at) = '" . intval($filter_tanggal) . "'";
if (!empty($filter_nama)) $where[] = "c.nama LIKE '%$filter_nama%'";
$where_sql = !empty($where) ? " AND " . implode(" AND ", $where) : "";

// Ambil daftar kriteria untuk header tabel dinamis
$kriteria_query = mysqli_query($koneksi, "SELECT id_kriteria, nama_kriteria FROM kriteria ORDER BY id_kriteria");
$kriteria_list = [];
while ($k = mysqli_fetch_assoc($kriteria_query)) {
    $kriteria_list[] = $k;
}

// Ambil semua data penilaian historis yang sesuai dengan filter
$penilaian_query = mysqli_query($koneksi, "SELECT p.id_calon, p.id_posisi, p.id_kriteria, p.nilai
    FROM penilaian p
    JOIN hasil_ranking hr ON p.id_calon = hr.id_calon AND p.id_posisi = hr.id_posisi
    JOIN calon_karyawan c ON p.id_calon = c.id_calon
    WHERE hr.is_history = 1 $where_sql");

$nilai_per_calon = [];
while ($nilai_row = mysqli_fetch_assoc($penilaian_query)) {
    $nilai_per_calon[$nilai_row['id_calon']][$nilai_row['id_posisi']][$nilai_row['id_kriteria']] = $nilai_row['nilai'];
}

// Ambil data riwayat utama
$q = mysqli_query($koneksi, "SELECT hr.*, c.nama, c.id_calon, p.nama_posisi, p.id_posisi, r.nama_rekrutmen
    FROM hasil_ranking hr
    JOIN calon_karyawan c ON hr.id_calon=c.id_calon
    JOIN posisi p ON hr.id_posisi=p.id_posisi
    LEFT JOIN rekrutmen r ON c.id_rekrutmen = r.id_rekrutmen
    WHERE hr.is_history=1 $where_sql
    ORDER BY hr.created_at DESC, p.nama_posisi, hr.peringkat");

// Kelompokkan data untuk ditampilkan
$riwayat = [];
while ($r = mysqli_fetch_assoc($q)) {
    $group_key = date("F Y", strtotime($r['created_at'])); // Grup per Bulan dan Tahun
    if (!isset($riwayat[$group_key])) $riwayat[$group_key] = [];
    $riwayat[$group_key][] = $r;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRD - Riwayat Ranking Rinci</title>
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
            border: none; border-radius: 1.25rem; box-shadow: var(--shadow);
            background-color: var(--card-bg); padding: 2.5rem;
        }
        .filter-card {
            background-color: #f1f3f5; border-radius: 1rem; padding: 1.5rem; margin-bottom: 2rem;
        }
        .accordion-item {
            border: none; border-radius: 1rem !important; margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }
        .accordion-button {
            border-radius: 1rem !important; background-color: var(--card-bg);
            font-size: 1.25rem; font-weight: 600; color: var(--primary-color);
        }
        .accordion-button:not(.collapsed) {
            box-shadow: none; background-color: #e7f5ff;
        }
        .accordion-button:focus { box-shadow: none; }
        .accordion-body { padding: 0; }
        .table { margin-bottom: 0; }
        .table th { font-weight: 500; }
        .table td, .table th { text-align: center; vertical-align: middle;}
        .table td:nth-child(1) { text-align: left; } /* Nama Calon rata kiri */
        .footer { margin-top:3rem; text-align:center; color: var(--text-light); }
    </style>
</head>
<body>

<div class="container my-4">
    <div class="main-card">
        <div class="text-center mb-4">
            <h2 class="mb-1">Riwayat Hasil Ranking</h2>
            <p class="text-muted">Arsip hasil penilaian dan perangkingan kandidat.</p>
        </div>

        <div class="filter-card">
            <form method="GET" class="row g-3 align-items-end">
                 <div class="col-md-3">
                    <label for="nama" class="form-label">Cari Nama Calon</label>
                    <input type="text" name="nama" id="nama" class="form-control" placeholder="Ketik nama..." value="<?= htmlspecialchars($filter_nama) ?>">
                </div>
                <div class="col-md-2">
                    <label for="tahun" class="form-label">Tahun</label>
                    <select name="tahun" id="tahun" class="form-select">
                        <option value="">Semua</option>
                        <?php for ($y = date("Y"); $y >= 2020; $y--): ?>
                            <option value="<?= $y ?>" <?= $filter_tahun==$y?"selected":"" ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="bulan" class="form-label">Bulan</label>
                    <select name="bulan" id="bulan" class="form-select">
                        <option value="">Semua</option>
                        <?php for ($m=1; $m<=12; $m++): ?>
                            <option value="<?= $m ?>" <?= $filter_bulan==$m?"selected":"" ?>><?= date("F", mktime(0,0,0,$m,1)) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="tanggal" class="form-label">Tanggal</label>
                    <select name="tanggal" id="tanggal" class="form-select">
                        <option value="">Semua</option>
                        <?php for ($d=1; $d<=31; $d++): ?>
                            <option value="<?= $d ?>" <?= $filter_tanggal==$d?"selected":"" ?>><?= $d ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filter</button>
                    <a href="cetak_pdf.php?<?= http_build_query($_GET) ?>" target="_blank" class="btn btn-info text-white"><i class="fas fa-print"></i> PDF</a>
                </div>
            </form>
        </div>

        <?php if(!empty($msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="accordion mt-4" id="riwayatAccordion">
            <?php if (empty($riwayat)): ?>
                <div class="alert alert-warning text-center">Tidak ada riwayat untuk filter yang dipilih.</div>
            <?php else: ?>
                <?php $i = 0; foreach ($riwayat as $grup => $data_grup): $i++; ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-<?= $i ?>">
                        <button class="accordion-button <?= $i > 1 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $i ?>">
                            Riwayat Bulan <?= htmlspecialchars($grup) ?>
                        </button>
                    </h2>
                    <div id="collapse-<?= $i ?>" class="accordion-collapse collapse <?= $i == 1 ? 'show' : '' ?>" data-bs-parent="#riwayatAccordion">
                        <div class="accordion-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nama Calon</th>
                                            <th>Posisi</th>
                                            <th>Peringkat</th>
                                            <?php foreach($kriteria_list as $k): ?>
                                                <th><?= htmlspecialchars($k['nama_kriteria']); ?></th>
                                            <?php endforeach; ?>
                                            <th>Total Nilai</th>
                                            <th>Batch</th> <th>Tgl. Proses</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data_grup as $r): ?>
                                            <tr>
                                                <td>
                                                    <a href="#" class="calon-detail text-decoration-none" data-id="<?= $r['id_calon'] ?>">
                                                        <?= htmlspecialchars($r['nama']) ?>
                                                    </a>
                                                </td>
                                                <td><?= htmlspecialchars($r['nama_posisi']) ?></td>
                                                <td><span class="badge bg-primary rounded-pill"><?= $r['peringkat'] ?></span></td>
                                                <?php foreach ($kriteria_list as $k):
                                                    $nilai = $nilai_per_calon[$r['id_calon']][$r['id_posisi']][$k['id_kriteria']] ?? '-';
                                                ?>
                                                    <td><?= $nilai; ?></td>
                                                <?php endforeach; ?>
                                                <td><strong><?= number_format($r['total_nilai'], 2) ?></strong></td>
                                                <td><?= htmlspecialchars($r['nama_rekrutmen'] ?: '-') ?></td> <td><small><?= date("d-m-Y", strtotime($r['created_at'])) ?></small></td>
                                                <td>
                                                    <a href="hapus_riwayat.php?id=<?= $r['id_hasil'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus data ini?')" title="Hapus Riwayat">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
         <div class="text-center mt-4">
             <a href="hapus_riwayat.php?all=1" onclick="return confirm('PERINGATAN: Anda akan menghapus SEMUA data riwayat. Aksi ini tidak dapat dibatalkan. Lanjutkan?')" class="btn btn-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>Hapus Seluruh Riwayat
            </a>
         </div>
    </div>
    <div class="footer">
        <a class="btn btn-secondary" href="dashboard.php"><i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard</a>
    </div>
</div>

<div class="modal fade" id="calonModal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content" style="border-radius: 1rem;">
      <div class="modal-header">
        <h5 class="modal-title">Detail Riwayat Penilaian Calon</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="calonDetailContent">
        <p class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</p>
      </div>
  </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ... (Kode Javascript tidak ada perubahan)
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".calon-detail").forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.preventDefault();
            let idCalon = this.dataset.id;
            let modal = new bootstrap.Modal(document.getElementById("calonModal"));
            let content = document.getElementById("calonDetailContent");
            content.innerHTML = '<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</p>';
            modal.show();
            fetch("detail_calon.php?id=" + idCalon)
                .then(response => response.text())
                .then(data => { content.innerHTML = data; })
                .catch(err => { content.innerHTML = "<p class='text-danger text-center'>Gagal memuat data detail.</p>"; });
        });
    });
});
</script>
</body>
</html>