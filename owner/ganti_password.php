<?php
require_once __DIR__ . "/../koneksi.php";
require_once __DIR__ . "/../partials/auth.php";
require_role('Owner');

$owner_id = $_SESSION['id_user'] ?? null;
$pesan = "";

if (isset($_POST['ganti_password'])) {
    $baru = trim($_POST['password_baru']);
    $konfirmasi = trim($_POST['password_konfirmasi']);
    if ($baru && $konfirmasi && $baru === $konfirmasi) {
        $hash = password_hash($baru, PASSWORD_DEFAULT);
        mysqli_query($koneksi, "UPDATE users SET password='$hash' WHERE id_user=$owner_id");
        $pesan = "✅ Password berhasil diganti!";
    } else {
        $pesan = "⚠️ Konfirmasi password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ganti Password Owner</title>
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
            max-width: 600px;
        }
        .card-custom {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 2rem;
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
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border-color: #ffeeba;
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>Dashboard Owner</h1>
        <h4>Ganti Password</h4>
    </header>

    <div class="container mt-5">
        <div class="card card-custom">
            <h2 class="text-center mb-4">Ganti Password Owner</h2>
            <?php if ($pesan): ?>
                <?php
                    $alert_class = strpos($pesan, '✅') !== false ? 'alert-success' : 'alert-warning';
                ?>
                <div class="alert <?= $alert_class; ?>" role="alert">
                    <?= $pesan ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="password_baru" class="form-label">Password Baru</label>
                    <input type="password" id="password_baru" name="password_baru" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password_konfirmasi" class="form-label">Konfirmasi Password</label>
                    <input type="password" id="password_konfirmasi" name="password_konfirmasi" class="form-control" required>
                </div>
                <div class="d-flex justify-content-between">
                    <button class="btn btn-success btn-custom" type="submit" name="ganti_password">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                    <a href="validasi.php" class="btn btn-secondary btn-custom">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; 2023 SPK Karyawan • Hak Cipta Dilindungi</p>
    </footer>
</body>
</html>