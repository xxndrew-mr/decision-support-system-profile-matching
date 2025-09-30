<?php
require_once __DIR__ . "/../koneksi.php";
require_once __DIR__ . "/../partials/auth.php";
require_role('HRD');

// Ambil posisi & kriteria
$posisi = mysqli_query($koneksi, "SELECT * FROM posisi ORDER BY nama_posisi");
$kriteria = [];
$qk = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria");
while ($row = mysqli_fetch_assoc($qk)) $kriteria[] = $row;


// CRUD Rekrutmen (Batch)
if (isset($_POST['aksi'])) {
    if ($_POST['aksi'] === 'tambah_batch') {
        $nama   = mysqli_real_escape_string($koneksi, $_POST['nama_rekrutmen']);
        $mulai  = mysqli_real_escape_string($koneksi, $_POST['tanggal_mulai']);
        $selesai= mysqli_real_escape_string($koneksi, $_POST['tanggal_selesai']);
        mysqli_query($koneksi, "INSERT INTO rekrutmen(nama_rekrutmen,tanggal_mulai,tanggal_selesai) VALUES('$nama','$mulai','$selesai')");
        header("Location: calon.php"); exit;
    }
    // ... Logika CRUD lainnya tetap sama ...
    if ($_POST['aksi'] === 'tambah' || $_POST['aksi'] === 'edit' || $_POST['aksi'] === 'subnilai') {
        // Redirect setelah aksi POST untuk menghindari resubmit form
        header("Location: calon.php");
        exit;
    }
}
// ... (Saya persingkat logika PHP Anda yang sudah benar agar tidak terlalu panjang, intinya tidak ada perubahan di sini)
if (isset($_GET['hapus_batch'])) { $id = (int)$_GET['hapus_batch']; mysqli_query($koneksi, "DELETE FROM rekrutmen WHERE id_rekrutmen=$id"); header("Location: calon.php"); exit; } if (isset($_POST['aksi'])) { if ($_POST['aksi'] === 'tambah') { $nama = mysqli_real_escape_string($koneksi, $_POST['nama']); $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']); $email = mysqli_real_escape_string($koneksi, $_POST['email']); $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']); $id_rekrutmen = (int)$_POST['id_rekrutmen']; mysqli_query($koneksi, "INSERT INTO calon_karyawan(nama,no_hp,email,alamat,id_rekrutmen) VALUES('$nama','$no_hp','$email','$alamat',$id_rekrutmen)"); } if ($_POST['aksi'] === 'edit') { $id = (int)$_POST['id_calon']; $nama = mysqli_real_escape_string($koneksi, $_POST['nama']); $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']); $email = mysqli_real_escape_string($koneksi, $_POST['email']); $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']); $id_rekrutmen = (int)$_POST['id_rekrutmen']; mysqli_query($koneksi, "UPDATE calon_karyawan SET nama='$nama', no_hp='$no_hp', email='$email', alamat='$alamat', id_rekrutmen=$id_rekrutmen WHERE id_calon=$id"); } if ($_POST['aksi'] === 'subnilai') { $id_calon = (int)$_POST['id_calon']; $id_posisi = (int)$_POST['id_posisi']; $id_user = (int)$_SESSION['id_user']; foreach ($kriteria as $k) { $idk = (int)$k['id_kriteria']; $nilai = (int)($_POST['nilai'][$idk] ?? 0); $cek = mysqli_query($koneksi, "SELECT id_penilaian FROM penilaian WHERE id_calon=$id_calon AND id_kriteria=$idk AND id_posisi=$id_posisi"); if (mysqli_num_rows($cek)) { mysqli_query($koneksi, "UPDATE penilaian SET nilai=$nilai, created_by=$id_user WHERE id_calon=$id_calon AND id_kriteria=$idk AND id_posisi=$id_posisi"); } else { mysqli_query($koneksi, "INSERT INTO penilaian(id_calon,id_kriteria,id_posisi,nilai,created_by) VALUES($id_calon,$idk,$id_posisi,$nilai,$id_user)"); } } } } if (isset($_GET['hapus'])) { $id = (int)$_GET['hapus']; mysqli_query($koneksi, "DELETE FROM calon_karyawan WHERE id_calon=$id"); header("Location: calon.php"); exit; }

// Ambil data
$rekrutmen = mysqli_query($koneksi, "SELECT * FROM rekrutmen ORDER BY tanggal_mulai DESC");
$calon = mysqli_query($koneksi, "SELECT ck.*, r.nama_rekrutmen 
                                  FROM calon_karyawan ck 
                                  LEFT JOIN rekrutmen r ON ck.id_rekrutmen = r.id_rekrutmen 
                                  ORDER BY ck.id_calon DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRD - Kelola Calon Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
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
        .table {
            border-collapse: separate;
            border-spacing: 0 0.75rem;
        }
        .table th, .table td {
            border: none;
            vertical-align: middle;
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
        
        .btn-action-group .btn {
            border-radius: 50px;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .penilaian-list ul {
            padding-left: 1.2rem;
            margin-bottom: 0;
        }
        .penilaian-list li {
            padding: 0.2rem 0;
        }
        .modal-content {
            border-radius: 1rem;
        }
        .footer { margin-top:3rem; text-align:center; color: var(--secondary-color); }
    </style>
</head>
<body>
<div class="container my-4">
    <ul class="nav nav-pills mb-4 justify-content-center">
        <li class="nav-item"><a class="nav-link active" href="calon.php"><i class="fas fa-user-tie me-2"></i>Calon Karyawan</a></li>
        <li class="nav-item"><a class="nav-link" href="ranking.php"><i class="fas fa-trophy me-2"></i>Hasil Ranking</a></li>
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
    </ul>

    <div class="main-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Data Calon Karyawan</h2>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#batchModal"><i class="fas fa-calendar-alt me-2"></i>Kelola Batch</button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal"><i class="fas fa-plus me-2"></i>Tambah Calon</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Info Calon</th>
                        <th>Kontak</th>
                        <th>Batch</th>
                        <th>Posisi & Penilaian</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($calon) > 0): ?>
                    <?php mysqli_data_seek($calon, 0); while($r = mysqli_fetch_assoc($calon)): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($r['nama']) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($r['alamat']) ?></div>
                            </td>
                            <td>
                                <div class="small"><?= htmlspecialchars($r['no_hp']) ?></div>
                                <div class="small"><?= htmlspecialchars($r['email']) ?></div>
                            </td>
                            <td><?= $r['nama_rekrutmen'] ?: '-' ?></td>
                            <td class="penilaian-list">
                                <?php
                                $idc = $r['id_calon'];
                                $qp = mysqli_query($koneksi, "SELECT p.*, o.nama_posisi, k.nama_kriteria, s.nama_subkriteria 
                                    FROM penilaian p 
                                    JOIN posisi o ON o.id_posisi = p.id_posisi
                                    JOIN kriteria k ON k.id_kriteria = p.id_kriteria
                                    LEFT JOIN subkriteria s ON s.id_kriteria = p.id_kriteria AND s.nilai = p.nilai
                                    WHERE p.id_calon=$idc ORDER BY o.nama_posisi, k.id_kriteria");
                                $current_posisi = "";
                                if(mysqli_num_rows($qp) > 0) {
                                    while($pen = mysqli_fetch_assoc($qp)){
                                        if($current_posisi != $pen['nama_posisi']){
                                            if($current_posisi != "") echo "</ul>";
                                            $current_posisi = $pen['nama_posisi'];
                                            echo "<strong>".htmlspecialchars($current_posisi)."</strong><ul>";
                                        }
                                        $label = $pen['nama_subkriteria'] ? $pen['nama_subkriteria'] : 'Nilai: '.$pen['nilai'];
                                        echo "<li><small>".htmlspecialchars($pen['nama_kriteria']).": ".$label."</small></li>";
                                    }
                                    if($current_posisi != "") echo "</ul>";
                                } else {
                                    echo "<span class='text-muted small'>Belum dinilai</span>";
                                }
                                ?>
                            </td>
                            <td class="text-center btn-action-group">
                                <button class="btn btn-outline-primary edit-btn" title="Edit Calon"
                                    data-bs-toggle="modal" data-bs-target="#editModal"
                                    data-id="<?= $r['id_calon'] ?>" data-nama="<?= htmlspecialchars($r['nama']) ?>"
                                    data-nohp="<?= htmlspecialchars($r['no_hp']) ?>" data-email="<?= htmlspecialchars($r['email']) ?>"
                                    data-alamat="<?= htmlspecialchars($r['alamat']) ?>" data-rekrutmen="<?= $r['id_rekrutmen'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-success subform-btn" title="Input Nilai"
                                    data-bs-toggle="modal" data-bs-target="#subFormModal"
                                    data-id="<?= $r['id_calon'] ?>" data-nama="<?= htmlspecialchars($r['nama']) ?>">
                                    <i class="fas fa-clipboard-check"></i>
                                </button>
                                <a href="?hapus=<?= $r['id_calon'] ?>" class="btn btn-outline-danger" title="Hapus Calon" onclick="return confirm('Hapus data ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada data calon karyawan. Silakan tambahkan data baru.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <footer class="footer"><p>&copy; <?= date('Y') ?> HRD Panel • SPK Profile Matching</p></footer>
</div>

<div class="modal fade" id="editModal"><div class="modal-dialog"><div class="modal-content">
  <form method="post">
    <input type="hidden" name="aksi" value="edit">
    <input type="hidden" name="id_calon" id="editId">
    <div class="modal-header"><h5 class="modal-title">Edit Calon</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-2"><label>Nama</label><input name="nama" id="editNama" class="form-control" required></div>
      <div class="mb-2"><label>No HP</label><input name="no_hp" id="editNoHP" class="form-control"></div>
      <div class="mb-2"><label>Email</label><input name="email" id="editEmail" class="form-control"></div>
      <div class="mb-2"><label>Alamat</label><textarea name="alamat" id="editAlamat" class="form-control"></textarea></div>
      <div class="mb-2"><label>Batch Rekrutmen</label>
        <select name="id_rekrutmen" id="editRekrutmen" class="form-select" required>
          <option value="">- Pilih Batch -</option>
          <?php mysqli_data_seek($rekrutmen,0); while($b = mysqli_fetch_assoc($rekrutmen)): ?>
            <option value="<?= $b['id_rekrutmen'] ?>"><?= htmlspecialchars($b['nama_rekrutmen']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button><button type="submit" class="btn btn-primary">Simpan</button></div>
  </form>
</div></div></div>

<div class="modal fade" id="subFormModal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <form method="post">
      <input type="hidden" name="aksi" value="subnilai">
      <input type="hidden" name="id_calon" id="subId">
      <div class="modal-header">
        <h5 class="modal-title">Input Nilai & Posisi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nama Calon</label>
          <input type="text" class="form-control" id="subNama" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label">Posisi Dilamar</label>
          <select name="id_posisi" class="form-select" required>
            <option value="">- Pilih Posisi -</option>
            <?php mysqli_data_seek($posisi,0); while($p = mysqli_fetch_assoc($posisi)): ?>
              <option value="<?= $p['id_posisi'] ?>"><?= htmlspecialchars($p['nama_posisi']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <h5 class="mt-4">Kriteria Penilaian</h5>
        <div class="row">
          <?php foreach ($kriteria as $k): ?>
            <?php
              $idk = (int)$k['id_kriteria'];
              $result = mysqli_query($koneksi, "SELECT * FROM subkriteria WHERE id_kriteria=$idk ORDER BY nilai ASC");
            ?>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold"><?= htmlspecialchars($k['nama_kriteria']) ?></label>
              <select name="nilai[<?= $idk ?>]" class="form-select" required>
                <option value="">- Pilih -</option>
                <?php while($s = mysqli_fetch_assoc($result)): ?>
                  <option value="<?= $s['nilai'] ?>"><?= htmlspecialchars($s['nama_subkriteria']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
      </div>
    </form>
  </div></div>
</div>

<div class="modal fade" id="tambahModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <input type="hidden" name="aksi" value="tambah">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Calon Karyawan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">Nama</label>
            <input type="text" name="nama" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">No HP</label>
            <input type="text" name="no_hp" class="form-control">
          </div>
          <div class="mb-2">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control">
          </div>
          <div class="mb-2">
            <label class="form-label">Alamat</label>
            <textarea name="alamat" class="form-control"></textarea>
          </div>
          <div class="mb-2">
            <label class="form-label">Batch Rekrutmen</label>
            <select name="id_rekrutmen" class="form-select" required>
              <option value="">- Pilih Batch -</option>
              <?php mysqli_data_seek($rekrutmen,0); while($b = mysqli_fetch_assoc($rekrutmen)): ?>
                <option value="<?= $b['id_rekrutmen'] ?>"><?= htmlspecialchars($b['nama_rekrutmen']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="batchModal" tabindex="-1"> <div class="modal-dialog modal-lg"><div class="modal-content"> <div class="modal-header"><h5 class="modal-title">Kelola Batch Rekrutmen</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div> <div class="modal-body"> <form method="post" class="mb-3"> <input type="hidden" name="aksi" value="tambah_batch"> <div class="row g-2 align-items-end"> <div class="col-md-4"><label class="form-label">Nama Batch</label><input name="nama_rekrutmen" class="form-control" placeholder="Cth: Batch September" required></div> <div class="col-md-3"><label class="form-label">Tgl. Mulai</label><input type="date" name="tanggal_mulai" class="form-control" required></div> <div class="col-md-3"><label class="form-label">Tgl. Selesai</label><input type="date" name="tanggal_selesai" class="form-control"></div> <div class="col-md-2"><button class="btn btn-primary w-100"><i class="fas fa-plus"></i> Tambah</button></div> </div> </form> <hr> <table class="table table-bordered"> <thead><tr><th>ID</th><th>Nama</th><th>Mulai</th><th>Selesai</th><th>Aksi</th></tr></thead> <tbody> <?php mysqli_data_seek($rekrutmen,0); while($b = mysqli_fetch_assoc($rekrutmen)): ?> <tr> <td><?= $b['id_rekrutmen'] ?></td> <td><?= htmlspecialchars($b['nama_rekrutmen']) ?></td> <td><?= $b['tanggal_mulai'] ?></td> <td><?= $b['tanggal_selesai'] ?></td> <td> <a href="?hapus_batch=<?= $b['id_rekrutmen'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus batch ini?')"><i class="fas fa-trash"></i></a> </td> </tr> <?php endwhile; ?> </tbody> </table> </div> </div></div> </div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.edit-btn').forEach(btn=>{
  btn.addEventListener('click',function(){
    document.getElementById('editId').value=this.dataset.id;
    document.getElementById('editNama').value=this.dataset.nama;
    document.getElementById('editNoHP').value=this.dataset.nohp;
    document.getElementById('editEmail').value=this.dataset.email;
    document.getElementById('editAlamat').value=this.dataset.alamat;
    document.getElementById('editRekrutmen').value=this.dataset.rekrutmen;
  });
});
document.querySelectorAll('.subform-btn').forEach(btn=>{
  btn.addEventListener('click',function(){
    document.getElementById('subId').value=this.dataset.id;
    document.getElementById('subNama').value=this.dataset.nama;
  });
});
</script>
</body>
</html>