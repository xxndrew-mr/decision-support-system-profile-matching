<?php
require_once __DIR__ . "/../koneksi.php";
require_once __DIR__ . "/../partials/auth.php";
require_role('Owner');

$pesan = "";
if (isset($_POST['buat_hrd'])) {
    $u = trim($_POST['username']);
    $p = trim($_POST['password']);
    if ($u && $p) {
        $hash = password_hash($p, PASSWORD_DEFAULT);
        mysqli_query($koneksi, "INSERT INTO users (username,password,role) VALUES('$u','$hash','HRD')");
        $pesan = "✅ HRD berhasil dibuat.";
    }
}
if (isset($_POST['hapus_hrd'])) {
    $id = (int)$_POST['id_user'];
    mysqli_query($koneksi, "DELETE FROM users WHERE id_user=$id AND role='HRD'");
    $pesan = "✅ HRD berhasil dihapus.";
}
$hrd = mysqli_query($koneksi, "SELECT * FROM users WHERE role='HRD' ORDER BY username");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Akun HRD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 2rem 1rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        .header h1 {
            margin-bottom: 0.5rem;
        }
        .container {
            max-width: 800px;
        }
        .card-custom {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        .table-custom th, .table-custom td {
            vertical-align: middle;
        }
        .table-custom tbody tr:hover {
            background-color: #f1f1f1;
        }
        .btn-custom {
            border-radius: 50rem;
            padding: .5rem 1.5rem;
        }
        .footer {
            text-align: center;
            padding: 1rem;
            color: #6c757d;
            font-size: 0.9rem;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>Dashboard Owner</h1>
        <h4>Kelola Akun HRD</h4>
    </header>

    <div class="container mt-5">
        <div class="card card-custom">
            <h2 class="text-center mb-4">Manajemen Akun HRD</h2>
            <?php if ($pesan): ?>
                <div class="alert alert-success" role="alert">
                    <?= $pesan ?>
                </div>
            <?php endif; ?>

            <form method="post" class="mb-4">
                <div class="row g-2">
                    <div class="col-md-5">
                        <input type="text" name="username" placeholder="Username HRD" class="form-control" required>
                    </div>
                    <div class="col-md-5">
                        <input type="password" name="password" placeholder="Password" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" name="buat_hrd">
                            <i class="fas fa-plus me-1"></i>Tambah
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-custom">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($hrd) > 0): ?>
                            <?php while($u = mysqli_fetch_assoc($hrd)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($u['username']); ?></td>
                                    <td class="text-end">
                                        <form method="post" class="d-inline-block">
                                            <input type="hidden" name="id_user" value="<?= $u['id_user']; ?>">
                                            <button class="btn btn-sm btn-danger" name="hapus_hrd">
                                                <i class="fas fa-trash-alt me-1"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="text-center text-muted">Tidak ada akun HRD yang terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-center">
                <a href="validasi.php" class="btn btn-secondary btn-custom">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; 2023 SPK Karyawan • Hak Cipta Dilindungi</p>
    </footer>
</body>
</html>