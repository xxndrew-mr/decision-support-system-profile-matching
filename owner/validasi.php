<?php
require_once __DIR__ . "/../koneksi.php";
require_once __DIR__ . "/../partials/auth.php";
require_role('Owner');

// Ambil ID user dari sesi, diasumsikan user_id disimpan dalam sesi setelah login
// Ganti $_SESSION['user_id'] dengan variabel sesi yang benar jika berbeda.
$owner_id = isset($_SESSION['id_user']) ? (int)$_SESSION['id_user'] : null;

// Pesan untuk menampilkan notifikasi
$pesan_ganti_password = '';
$pesan = '';
$pesan_hapus = '';

// Proses ganti password owner
if (isset($_POST['ganti_password_owner'])) {
    $password_baru = trim($_POST['password_baru']);
    $password_konfirmasi = trim($_POST['password_konfirmasi']);

    if (empty($password_baru) || empty($password_konfirmasi)) {
        $pesan_ganti_password = "⚠️ Harap isi semua kolom untuk mengganti password.";
    } elseif ($password_baru !== $password_konfirmasi) {
        $pesan_ganti_password = "⚠️ Konfirmasi password tidak cocok.";
    } elseif ($owner_id) {
        $hash = password_hash($password_baru, PASSWORD_DEFAULT);
        $update = mysqli_query($koneksi, "UPDATE users SET password='$hash' WHERE id_user=$owner_id");
        if ($update) {
            $pesan_ganti_password = "✅ Password berhasil diganti!";
        } else {
            $pesan_ganti_password = "⚠️ Gagal mengganti password. Silakan coba lagi.";
        }
    }
}

// Proses validasi hasil ranking
if (isset($_POST['aksi']) && isset($_POST['id_hasil'])) {
    $id_hasil = (int)$_POST['id_hasil'];
    $status = $_POST['aksi'] === 'setujui' ? 'Disetujui' : 'Ditolak';
    mysqli_query($koneksi, "UPDATE hasil_ranking SET validasi_owner='$status' WHERE id_hasil=$id_hasil");
}

// Proses tambah akun HRD
if (isset($_POST['buat_hrd'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    if ($username && $password) {
        $username = mysqli_real_escape_string($koneksi, $username);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
        if (mysqli_num_rows($cek) > 0) {
            $pesan = "⚠️ Username sudah digunakan!";
        } else {
            mysqli_query($koneksi, "INSERT INTO users (username, password, role) VALUES ('$username', '$hash', 'HRD')");
            $pesan = "✅ Akun HRD berhasil dibuat!";
        }
    } else {
        $pesan = "⚠️ Harap isi username dan password.";
    }
}

// Proses hapus akun HRD
if (isset($_POST['hapus_hrd']) && isset($_POST['id_user'])) {
    $id_user = (int)$_POST['id_user'];
    $hapus = mysqli_query($koneksi, "DELETE FROM users WHERE id_user=$id_user AND role='HRD'");
    if ($hapus) {
        $pesan_hapus = "✅ Akun HRD berhasil dihapus!";
    } else {
        $pesan_hapus = "⚠️ Gagal menghapus akun!";
    }
}

$posisi = mysqli_query($koneksi, "SELECT * FROM posisi ORDER BY id_posisi");
$selected = isset($_GET['id_posisi']) ? (int)$_GET['id_posisi'] : 0;

$sql = "SELECT hr.*, c.nama, p.nama_posisi 
        FROM hasil_ranking hr 
        JOIN calon_karyawan c ON c.id_calon=hr.id_calon 
        JOIN posisi p ON p.id_posisi=hr.id_posisi";
if ($selected) $sql .= " WHERE hr.id_posisi=$selected";
$sql .= " ORDER BY p.id_posisi, hr.peringkat";
$hasil = mysqli_query($koneksi, $sql);

$hrd_accounts = mysqli_query($koneksi, "SELECT * FROM users WHERE role='HRD' ORDER BY username");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner - Validasi Ranking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --light-bg: #f0f2f5;
            --card-bg: #fff;
            --shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
            --gradient-blue: linear-gradient(135deg, #0d6efd, #0056b3);
            --gradient-red: linear-gradient(45deg, #dc3545, #c82333);
            --success-color: #28a745;
            --danger-color: #dc3545;
        }
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .header {
            background: var(--gradient-blue);
            color: #fff;
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-bottom-left-radius: 4rem;
            border-bottom-right-radius: 4rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
            animation: slideDown 0.8s ease-in-out;
        }
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255, 255, 255, 0.05);
            transform: rotate(30deg);
            pointer-events: none;
            z-index: 1;
        }
        .header h1, .header h4 {
            position: relative;
            z-index: 2;
            margin: 0;
        }
        .header h1 {
            font-weight: 700;
            font-size: 2.8rem;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }
        .container {
            flex-grow: 1;
            padding: 2rem 1rem;
            max-width: 1200px;
            margin-top: 2rem;
            position: relative;
            z-index: 10;
        }
        .card-custom {
            background-color: var(--card-bg);
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            animation: fadeIn 1s ease-in-out;
        }
        .card-custom h2 {
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
        }
        .table-custom {
            border-radius: 1rem;
            overflow: hidden;
        }
        .table-custom thead {
            background-color: var(--primary-color);
            color: #fff;
        }
        .table-custom th, .table-custom td {
            vertical-align: middle;
            text-align: center;
            padding: 1rem;
        }
        .table-custom tbody tr:nth-child(even) {
            background-color: var(--light-bg);
        }
        .table-custom tbody tr:hover {
            background-color: #e9ecef;
        }
        .badge {
            font-size: 0.9em;
            font-weight: 600;
            padding: 0.5em 1em;
            border-radius: 1rem;
        }
        .badge-success { background-color: var(--success-color); color: #fff; }
        .badge-danger { background-color: var(--danger-color); color: #fff; }
        .badge-info { background-color: #0dcaf0; color: #fff; }
        .form-label {
            font-weight: 600;
            color: var(--primary-color);
        }
        .form-control, .form-select {
            border-radius: 1rem;
            padding: 0.75rem 1.25rem;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            border-color: var(--primary-color);
        }
        .btn-custom {
            border-radius: 2rem;
            font-weight: 600;
            padding: 0.75rem 2rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }
        .btn-primary { background: var(--gradient-blue); border: none; }
        .btn-danger { background: var(--gradient-red); border: none; }
        .btn-logout {
            background: var(--gradient-red);
            color: #fff;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 2rem;
            font-size: 1rem;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .btn-logout:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            color: #fff;
        }
        .footer {
            text-align: center;
            padding: 2rem;
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin-top: 4rem;
        }
        .modal-content {
            border-radius: 1.5rem;
            border: none;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        }
        .modal-header {
            border-bottom: none;
            padding: 2rem 2rem 0;
        }
        .modal-body {
            padding: 1rem 2rem 2rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard Owner</h1>
        <h4>Validasi hasil ranking dan manajemen akun HRD.</h4>
    </div>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <form method="get" class="d-flex align-items-center">
                <label class="form-label me-2 mb-0">Filter Posisi:</label>
                <select name="id_posisi" class="form-select" onchange="this.form.submit()">
                    <option value="0">Semua Posisi</option>
                    <?php while($p = mysqli_fetch_assoc($posisi)): ?>
                        <option value="<?php echo $p['id_posisi']; ?>" <?php if($selected==$p['id_posisi']) echo "selected"; ?>>
                            <?php echo htmlspecialchars($p['nama_posisi']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
            <a class="btn btn-logout" href="../logout.php">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>

        <!-- Card Validasi Ranking -->
        <div class="card-custom">
            <h2 class="text-center">Validasi Hasil Ranking</h2>
            <div class="table-responsive">
                <table class="table table-borderless table-hover table-custom">
                    <thead>
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
                        <?php while($r = mysqli_fetch_assoc($hasil)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['nama_posisi']); ?></td>
                                <td><?php echo $r['peringkat']; ?></td>
                                <td><?php echo htmlspecialchars($r['nama']); ?></td>
                                <td><?php echo number_format($r['total_nilai'], 2); ?></td>
                                <td>
                                    <?php
                                        $badge_class = 'badge-info';
                                        if ($r['validasi_owner'] == 'Disetujui') {
                                            $badge_class = 'badge-success';
                                        } elseif ($r['validasi_owner'] == 'Ditolak') {
                                            $badge_class = 'badge-danger';
                                        }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars($r['validasi_owner']); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="post" class="d-inline-block">
                                        <input type="hidden" name="id_hasil" value="<?php echo $r['id_hasil']; ?>" />
                                        <button class="btn btn-sm btn-primary btn-custom" type="submit" name="aksi" value="setujui">
                                            <i class="fas fa-check me-1"></i> Setujui
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-custom" type="submit" name="aksi" value="tolak">
                                            <i class="fas fa-times me-1"></i> Tolak
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Card Ganti Password Owner -->
        <div class="card-custom">
            <h2 class="text-center">Ganti Password Owner</h2>
            <?php if ($pesan_ganti_password): ?>
                <div class="alert <?php echo strpos($pesan_ganti_password, '✅') !== false ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                    <?php echo htmlspecialchars($pesan_ganti_password); ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="password_baru" class="form-label">Password Baru</label>
                    <input type="password" class="form-control" id="password_baru" name="password_baru" required>
                </div>
                <div class="mb-3">
                    <label for="password_konfirmasi" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" class="form-control" id="password_konfirmasi" name="password_konfirmasi" required>
                </div>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary btn-custom mt-3" type="submit" name="ganti_password_owner">Ganti Password</button>
                </div>
            </form>
        </div>

        <!-- Card Manajemen Akun HRD -->
        <div class="card-custom">
            <h2 class="text-center">Manajemen Akun HRD</h2>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>Daftar Akun HRD</h4>
                <button type="button" class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#modalHrd">
                    <i class="fas fa-user-plus me-2"></i> Tambah Akun
                </button>
            </div>
            <?php if ($pesan_hapus): ?>
                <div class="alert <?php echo strpos($pesan_hapus, '✅') !== false ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                    <?php echo htmlspecialchars($pesan_hapus); ?>
                </div>
            <?php endif; ?>
            <div class="table-responsive">
                <table class="table table-borderless table-hover table-custom">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($hrd = mysqli_fetch_assoc($hrd_accounts)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($hrd['username']); ?></td>
                                <td>
                                    <form method="post" class="d-inline-block" data-confirm-message="Apakah Anda yakin ingin menghapus akun ini?">
                                        <input type="hidden" name="id_user" value="<?php echo $hrd['id_user']; ?>" />
                                        <button class="btn btn-sm btn-danger" type="submit" name="hapus_hrd">
                                            <i class="fas fa-trash-alt me-1"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2023 SPK Karyawan • Hak Cipta Dilindungi</p>
    </footer>

    <!-- Modal untuk Buat Akun HRD -->
    <div class="modal fade" id="modalHrd" tabindex="-1" aria-labelledby="modalHrdLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalHrdLabel">Buat Akun HRD Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username HRD</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary btn-custom mt-3" type="submit" name="buat_hrd">Buat Akun</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk Konfirmasi Hapus -->
    <div class="modal fade" id="modalConfirmDelete" tabindex="-1" aria-labelledby="modalConfirmDeleteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfirmDeleteLabel">Konfirmasi Penghapusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteForms = document.querySelectorAll('form[data-confirm-message]');
            const modalConfirmDelete = new bootstrap.Modal(document.getElementById('modalConfirmDelete'));
            const confirmMessage = document.getElementById('confirmMessage');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            let formToDelete = null;

            deleteForms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    formToDelete = this;
                    confirmMessage.textContent = this.getAttribute('data-confirm-message');
                    modalConfirmDelete.show();
                });
            });

            confirmDeleteBtn.addEventListener('click', function() {
                if (formToDelete) {
                    formToDelete.submit();
                }
            });
        });
    </script>
</body>
</html>
