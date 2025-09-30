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
        mysqli_query($koneksi, "INSERT INTO rekrutmen(nama_rekrutmen,tanggal_mulai,tanggal_selesai) 
                                VALUES('$nama','$mulai','$selesai')");
    }
    if ($_POST['aksi'] === 'edit_batch') {
        $id     = (int)$_POST['id_rekrutmen'];
        $nama   = mysqli_real_escape_string($koneksi, $_POST['nama_rekrutmen']);
        $mulai  = mysqli_real_escape_string($koneksi, $_POST['tanggal_mulai']);
        $selesai= mysqli_real_escape_string($koneksi, $_POST['tanggal_selesai']);
        mysqli_query($koneksi, "UPDATE rekrutmen 
                                SET nama_rekrutmen='$nama', tanggal_mulai='$mulai', tanggal_selesai='$selesai' 
                                WHERE id_rekrutmen=$id");
    }
}
if (isset($_GET['hapus_batch'])) {
    $id = (int)$_GET['hapus_batch'];
    mysqli_query($koneksi, "DELETE FROM rekrutmen WHERE id_rekrutmen=$id");
    header("Location: calon.php");
    exit;
}

// CREATE/UPDATE Calon
if (isset($_POST['aksi'])) {
    if ($_POST['aksi'] === 'tambah') {
        $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
        $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
        $email = mysqli_real_escape_string($koneksi, $_POST['email']);
        $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
        $id_rekrutmen = (int)$_POST['id_rekrutmen'];
        mysqli_query($koneksi, "INSERT INTO calon_karyawan(nama,no_hp,email,alamat,id_rekrutmen) 
                                VALUES('$nama','$no_hp','$email','$alamat',$id_rekrutmen)");
    }
    if ($_POST['aksi'] === 'edit') {
        $id = (int)$_POST['id_calon'];
        $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
        $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
        $email = mysqli_real_escape_string($koneksi, $_POST['email']);
        $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
        $id_rekrutmen = (int)$_POST['id_rekrutmen'];
        mysqli_query($koneksi, "UPDATE calon_karyawan 
                                SET nama='$nama', no_hp='$no_hp', email='$email', alamat='$alamat', id_rekrutmen=$id_rekrutmen 
                                WHERE id_calon=$id");
    }
    if ($_POST['aksi'] === 'subnilai') {
        $id_calon = (int)$_POST['id_calon'];
        $id_posisi = (int)$_POST['id_posisi'];
        $id_user = (int)$_SESSION['id_user'];

        foreach ($kriteria as $k) {
            $idk = (int)$k['id_kriteria'];
            $nilai = (int)($_POST['nilai'][$idk] ?? 0);

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
}

// DELETE Calon
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM calon_karyawan WHERE id_calon=$id");
    header("Location: calon.php");
    exit;
}

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
  <title>HRD - Calon Karyawan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body { background-color:#f0f2f5; }
    .card { border:none; border-radius:1rem; box-shadow:0 5px 15px rgba(0,0,0,0.1); padding:2rem; }
    .table { background:#fff; border-radius:.75rem; overflow:hidden; }
    .table thead th { background:#e9ecef; }
    .btn { border-radius:.5rem; }
    .nav-tabs .nav-link.active { font-weight:bold; color:#0d6efd !important; }
    footer { margin-top:2rem; text-align:center; color:#6c757d; }
  </style>
</head>
<body>
<div class="container my-4">
  <!-- NAV -->
  <ul class="nav nav-tabs mb-4 justify-content-center">
    <li class="nav-item"><a class="nav-link active" href="calon.php"><i class="fas fa-user-tie"></i> Calon</a></li>
    <li class="nav-item"><a class="nav-link" href="ranking.php"><i class="fas fa-trophy"></i> Ranking</a></li>
    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-arrow-left"></i> Dashboard</a></li>
    <li class="nav-item"><a class="nav-link text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>

  <div class="card">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Data Calon Karyawan</h2>
      <div class="d-flex gap-2">
        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#batchModal"><i class="fas fa-calendar"></i> Kelola Batch</button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal"><i class="fas fa-plus"></i> Tambah</button>
      </div>
    </div>

    <!-- Modal Edit Calon -->
<div class="modal fade" id="editModal"><div class="modal-dialog"><div class="modal-content">
  <form method="post">
    <input type="hidden" name="aksi" value="edit">
    <input type="hidden" name="id_calon" id="editId">
    <div class="modal-header"><h5 class="modal-title">Edit Calon</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-2"><label>Nama</label><input name="nama" id="editNama" class="form-control" required></div>
      <div class="mb-2"><label>No HP</label><input name="no_hp" id="editNoHP" class="form-control"></div>
      <div class="mb-2"><label>Email</label><input name="email" id="editEmail" class="form-control"></div>
      <div class="mb-2"><label>Alamat</label><input name="alamat" id="editAlamat" class="form-control"></div>
      <div class="mb-2"><label>Batch Rekrutmen</label>
        <select name="id_rekrutmen" id="editRekrutmen" class="form-select" required>
          <option value="">- Pilih Batch -</option>
          <?php mysqli_data_seek($rekrutmen,0); while($b = mysqli_fetch_assoc($rekrutmen)): ?>
            <option value="<?= $b['id_rekrutmen'] ?>"><?= htmlspecialchars($b['nama_rekrutmen']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>
    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button><button class="btn btn-primary">Simpan</button></div>
  </form>
</div></div></div>

<!-- Modal SubForm Nilai & Posisi -->
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
<!-- Modal Tambah Calon -->
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


<!-- Modal Kelola Batch --> 
 <div class="modal fade" id="batchModal" tabindex="-1"> <div class="modal-dialog modal-lg"><div class="modal-content"> <div class="modal-header"><h5 class="modal-title">Kelola Batch Rekrutmen</h5><button class="btn-close" data-bs-dismiss="modal"></button></div> <div class="modal-body"> <!-- Form tambah batch --> <form method="post" class="mb-3"> <input type="hidden" name="aksi" value="tambah_batch"> <div class="row g-2"> <div class="col-md-4"><input name="nama_rekrutmen" class="form-control" placeholder="Nama Batch" required></div> <div class="col-md-3"><input type="date" name="tanggal_mulai" class="form-control" required></div> <div class="col-md-3"><input type="date" name="tanggal_selesai" class="form-control"></div> <div class="col-md-2"><button class="btn btn-primary w-100"><i class="fas fa-plus"></i> Tambah</button></div> </div> </form> <!-- Tabel batch --> <table class="table table-bordered"> <thead><tr><th>ID</th><th>Nama</th><th>Mulai</th><th>Selesai</th><th>Aksi</th></tr></thead> <tbody> <?php mysqli_data_seek($rekrutmen,0); while($b = mysqli_fetch_assoc($rekrutmen)): ?> <tr> <td><?= $b['id_rekrutmen'] ?></td> <td><?= htmlspecialchars($b['nama_rekrutmen']) ?></td> <td><?= $b['tanggal_mulai'] ?></td> <td><?= $b['tanggal_selesai'] ?></td> <td> <!-- Edit batch --> <form method="post" class="d-inline"> <input type="hidden" name="aksi" value="edit_batch"> <input type="hidden" name="id_rekrutmen" value="<?= $b['id_rekrutmen'] ?>"> <input type="hidden" name="nama_rekrutmen" value="<?= htmlspecialchars($b['nama_rekrutmen']) ?>"> <input type="hidden" name="tanggal_mulai" value="<?= $b['tanggal_mulai'] ?>"> <input type="hidden" name="tanggal_selesai" value="<?= $b['tanggal_selesai'] ?>"> <button class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i></button> </form> <a href="?hapus_batch=<?= $b['id_rekrutmen'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus batch ini?')"><i class="fas fa-trash"></i></a> </td> </tr> <?php endwhile; ?> </tbody> </table> </div> </div></div> </div>
  
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead>
          <tr><th>Nama</th><th>No HP</th><th>Email</th><th>Alamat</th>
              <th>Batch</th><th>Posisi & Penilaian</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php while($r = mysqli_fetch_assoc($calon)): ?>
          <tr>
            <td><?= htmlspecialchars($r['nama']) ?></td>
            <td><?= htmlspecialchars($r['no_hp']) ?></td>
            <td><?= htmlspecialchars($r['email']) ?></td>
            <td><?= htmlspecialchars($r['alamat']) ?></td>
            <td><?= $r['nama_rekrutmen'] ?: '-' ?></td>
            <td>
              <?php
              $idc = $r['id_calon'];
              $qp = mysqli_query($koneksi, "SELECT p.*, o.nama_posisi, k.nama_kriteria, s.nama_subkriteria 
                  FROM penilaian p 
                  JOIN posisi o ON o.id_posisi = p.id_posisi
                  JOIN kriteria k ON k.id_kriteria = p.id_kriteria
                  LEFT JOIN subkriteria s ON s.id_kriteria = p.id_kriteria AND s.nilai = p.nilai
                  WHERE p.id_calon=$idc ORDER BY o.nama_posisi, k.id_kriteria");
              $current_posisi = "";
              while($pen = mysqli_fetch_assoc($qp)){
                if($current_posisi != $pen['nama_posisi']){
                  if($current_posisi != "") echo "</ul>";
                  $current_posisi = $pen['nama_posisi'];
                  echo "<strong>".htmlspecialchars($current_posisi)."</strong><ul>";
                }
                $label = $pen['nama_subkriteria'] ? $pen['nama_subkriteria']." (".$pen['nilai'].")" : $pen['nilai'];
                echo "<li>".htmlspecialchars($pen['nama_kriteria']).": ".$label."</li>";
              }
              if($current_posisi != "") echo "</ul>";
              ?>
            </td>
            <td>
              <button class="btn btn-info btn-sm text-white edit-btn"
                data-bs-toggle="modal" data-bs-target="#editModal"
                data-id="<?= $r['id_calon'] ?>"
                data-nama="<?= htmlspecialchars($r['nama']) ?>"
                data-nohp="<?= htmlspecialchars($r['no_hp']) ?>"
                data-email="<?= htmlspecialchars($r['email']) ?>"
                data-alamat="<?= htmlspecialchars($r['alamat']) ?>"
                data-rekrutmen="<?= $r['id_rekrutmen'] ?>">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-success btn-sm subform-btn"
                data-bs-toggle="modal" data-bs-target="#subFormModal"
                data-id="<?= $r['id_calon'] ?>" data-nama="<?= htmlspecialchars($r['nama']) ?>">
                <i class="fas fa-plus-circle"></i>
              </button>
              <a href="?hapus=<?= $r['id_calon'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')"><i class="fas fa-trash"></i></a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
    <footer><p class="mt-4 text-muted">&copy; HRD Panel • SPK Profile Matching</p></footer>
  </div>
</div>

<!-- Modal (Edit, SubForm, Batch, Tambah) tetap sama dengan punyamu -->
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
