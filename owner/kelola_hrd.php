<?php
require_once __DIR__ . "/../koneksi.php";
require_once __DIR__ . "/../partials/auth.php";
require_role('Owner');

$pesan = "";
if (isset($_POST['buat_hrd'])) {
    $u = trim($_POST['username']);
    $p = trim($_POST['password']);
    if (!empty($u) && !empty($p)) {
        // Cek dulu apakah username sudah ada
        $cek = mysqli_query($koneksi, "SELECT id_user FROM users WHERE username='".mysqli_real_escape_string($koneksi, $u)."'");
        if (mysqli_num_rows($cek) == 0) {
            $hash = password_hash($p, PASSWORD_DEFAULT);
            mysqli_query($koneksi, "INSERT INTO users (username,password,role) VALUES('".mysqli_real_escape_string($koneksi, $u)."','$hash','HRD')");
            $pesan = "✅ Akun HRD baru berhasil dibuat.";
        } else {
            $pesan = "⚠️ Gagal! Username '$u' sudah digunakan.";
        }
    } else {
        $pesan = "⚠️ Gagal! Username dan password tidak boleh kosong.";
    }
}
if (isset($_POST['hapus_hrd'])) {
    $id = (int)$_POST['id_user'];
    // Jangan hapus akun sendiri
    if ($id != $_SESSION['id_user']) {
        mysqli_query($koneksi, "DELETE FROM users WHERE id_user=$id AND role='HRD'");
        $pesan = "✅ Akun HRD berhasil dihapus.";
    } else {
        $pesan = "⚠️ Anda tidak dapat menghapus akun Anda sendiri.";
    }
}
$hrd = mysqli_query($koneksi, "SELECT * FROM users WHERE role='HRD' ORDER BY username");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Owner - Kelola Akun HRD</title>
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

        .container { max-width: 800px; margin-top: -2.5rem; position: relative; z-index: 10;}
        
        .main-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: var(--shadow);
            background-color: var(--card-bg);
            padding: 2.5rem;
        }

        .form-tambah-hrd {
            background-color: #f1f3f5;
            padding: 1.5rem;
            border-radius: 1rem;
        }

        .table {
            border-collapse: separate;
            border-spacing: 0 0.75rem;
            margin-top: -0.75rem; /* Menghilangkan margin atas dari border-spacing */
        }
        .table th, .table td {
            border: none;
            vertical-align: middle;
        }
        .table thead th {
            border-bottom: 2px solid #dee2e6;
            padding: 1rem 1.5rem;
        }
        .table tbody tr {
            background-color: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        .table td { padding: 1rem 1.5rem; }
        .table td:first-child { border-top-left-radius: 0.75rem; border-bottom-left-radius: 0.75rem; font-weight: 500;}
        .table td:last-child { border-top-right-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }
        
        .footer { text-align: center; padding: 2rem; color: #6c757d; font-size: 0.9rem; }
    </style>
</head>
<body>
    <header class="header">
        <h1>Dashboard Owner</h1>
        <h4>Kelola Akun HRD</h4>
    </header>

    <div class="container">
        <div class="main-card">
            <div class="text-center mb-4">
                <h2 class="mb-1">Manajemen Akun HRD</h2>
                <p class="text-muted">Tambah atau hapus akun untuk staf HRD.</p>
            </div>
            
            <?php if ($pesan): ?>
                <?php $alert_class = strpos($pesan, '✅') !== false ? 'alert-success' : 'alert-danger'; ?>
                <div class="alert <?= $alert_class; ?> alert-dismissible fade show" role="alert">
                    <?= $pesan ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="form-tambah-hrd mb-4">
                <form method="post">
                    <div class="row g-3 align-items-center">
                        <div class="col-md">
                            <label for="username" class="visually-hidden">Username</label>
                            <input type="text" id="username" name="username" placeholder="Username HRD Baru" class="form-control" required>
                        </div>
                        <div class="col-md">
                            <label for="password" class="visually-hidden">Password</label>
                            <input type="password" id="password" name="password" placeholder="Password" class="form-control" required>
                        </div>
                        <div class="col-md-auto">
                            <button class="btn btn-primary w-100" name="buat_hrd" type="submit">
                                <i class="fas fa-plus me-2"></i>Tambah Akun
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table">
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
                                    <td>
                                        <i class="fas fa-user-shield text-primary me-2"></i>
                                        <?= htmlspecialchars($u['username']); ?>
                                    </td>
                                    <td class="text-end">
                                        <form method="post" class="d-inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun HRD ini?');">
                                            <input type="hidden" name="id_user" value="<?= $u['id_user']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" name="hapus_hrd">
                                                <i class="fas fa-trash-alt me-1"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="text-center text-muted p-4">
                                    Belum ada akun HRD yang terdaftar.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-center">
                <a href="validasi.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Validasi
                </a>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; <?= date('Y') ?> DWI BHAKTI OFFSET • Hak Cipta Dilindungi</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>