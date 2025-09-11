<?php
require_once __DIR__ . "/../koneksi.php";
require_once __DIR__ . "/../partials/auth.php";
require_role('HRD');


// CREATE/UPDATE
if (isset($_POST['aksi'])) {
    if ($_POST['aksi'] === 'tambah') {
        $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
        $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
        $email = mysqli_real_escape_string($koneksi, $_POST['email']);
        $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
        mysqli_query($koneksi, "INSERT INTO calon_karyawan(nama,no_hp,email,alamat) VALUES('$nama','$no_hp','$email','$alamat')");
    }
    if ($_POST['aksi'] === 'edit') {
        $id = (int)$_POST['id_calon'];
        $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
        $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
        $email = mysqli_real_escape_string($koneksi, $_POST['email']);
        $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
        mysqli_query($koneksi, "UPDATE calon_karyawan SET nama='$nama', no_hp='$no_hp', email='$email', alamat='$alamat' WHERE id_calon=$id");
    }
}
// DELETE
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM calon_karyawan WHERE id_calon=$id");
    header("Location: calon.php");
    exit;
}

$calon = mysqli_query($koneksi, "SELECT * FROM calon_karyawan ORDER BY id_calon DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRD - Calon Karyawan</title>
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
        .actions-cell {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navigation Menu -->
        <ul class="nav nav-tabs justify-content-center mb-4">
            <li class="nav-item">
                <a class="nav-link active" href="calon.php">
                    <i class="fas fa-user-tie"></i> Calon Karyawan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="penilaian.php">
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Data Calon Karyawan</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                    <i class="fas fa-plus"></i> Tambah Calon Baru
                </button>
            </div>

            <!-- Candidates List Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>No HP</th>
                            <th>Email</th>
                            <th>Alamat</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($r = mysqli_fetch_assoc($calon)): ?>
                        <tr>
                            <td><?php echo $r['id_calon']; ?></td>
                            <td><?php echo htmlspecialchars($r['nama']); ?></td>
                            <td><?php echo htmlspecialchars($r['no_hp']); ?></td>
                            <td><?php echo htmlspecialchars($r['email']); ?></td>
                            <td><?php echo htmlspecialchars($r['alamat']); ?></td>
                            <td class="actions-cell text-end">
                                <button class="btn btn-info btn-sm text-white edit-btn"
                                    data-bs-toggle="modal" data-bs-target="#editModal"
                                    data-id="<?php echo $r['id_calon']; ?>"
                                    data-nama="<?php echo htmlspecialchars($r['nama']); ?>"
                                    data-nohp="<?php echo htmlspecialchars($r['no_hp']); ?>"
                                    data-email="<?php echo htmlspecialchars($r['email']); ?>"
                                    data-alamat="<?php echo htmlspecialchars($r['alamat']); ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <a class="btn btn-danger btn-sm" href="?hapus=<?php echo $r['id_calon']; ?>" onclick="return confirm('Hapus data ini?')">
                                    <i class="fas fa-trash-alt"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <p class="text-center text-muted mt-4">&copy; HRD Panel • SPK Profile Matching</p>
        </div>
    </div>

    <!-- Tambah Modal -->
    <div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahModalLabel">Tambah Calon Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="tambahForm">
                        <input type="hidden" name="aksi" value="tambah" />
                        <div class="mb-3">
                            <label for="tambahNama" class="form-label">Nama</label>
                            <input type="text" name="nama" class="form-control" id="tambahNama" required />
                        </div>
                        <div class="mb-3">
                            <label for="tambahNoHP" class="form-label">No HP</label>
                            <input type="text" name="no_hp" class="form-control" id="tambahNoHP" />
                        </div>
                        <div class="mb-3">
                            <label for="tambahEmail" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" id="tambahEmail" />
                        </div>
                        <div class="mb-3">
                            <label for="tambahAlamat" class="form-label">Alamat</label>
                            <input type="text" name="alamat" class="form-control" id="tambahAlamat" />
                        </div>
                        <div class="modal-footer d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Calon Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="editForm">
                        <input type="hidden" name="aksi" value="edit" />
                        <input type="hidden" name="id_calon" id="editId" />
                        <div class="mb-3">
                            <label for="editNama" class="form-label">Nama</label>
                            <input type="text" name="nama" class="form-control" id="editNama" required />
                        </div>
                        <div class="mb-3">
                            <label for="editNoHP" class="form-label">No HP</label>
                            <input type="text" name="no_hp" class="form-control" id="editNoHP" />
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" id="editEmail" />
                        </div>
                        <div class="mb-3">
                            <label for="editAlamat" class="form-label">Alamat</label>
                            <input type="text" name="alamat" class="form-control" id="editAlamat" />
                        </div>
                        <div class="modal-footer d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ambil semua tombol edit
            const editButtons = document.querySelectorAll('.edit-btn');

            // Tambahkan event listener untuk setiap tombol edit
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Ambil data dari atribut data-*
                    const id = this.getAttribute('data-id');
                    const nama = this.getAttribute('data-nama');
                    const nohp = this.getAttribute('data-nohp');
                    const email = this.getAttribute('data-email');
                    const alamat = this.getAttribute('data-alamat');
                    
                    // Masukkan data ke dalam modal edit
                    document.getElementById('editId').value = id;
                    document.getElementById('editNama').value = nama;
                    document.getElementById('editNoHP').value = nohp;
                    document.getElementById('editEmail').value = email;
                    document.getElementById('editAlamat').value = alamat;
                });
            });
        });
    </script>
</body>
</html>
