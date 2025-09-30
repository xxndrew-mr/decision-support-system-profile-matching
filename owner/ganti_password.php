<?php
require_once __DIR__ . "/../koneksi.php";
require_once __DIR__ . "/../partials/auth.php";
require_role('Owner');

$owner_id = $_SESSION['id_user'] ?? null;
$pesan = "";

if (isset($_POST['ganti_password'])) {
    $baru = trim($_POST['password_baru']);
    $konfirmasi = trim($_POST['password_konfirmasi']);
    if (!empty($baru) && $baru === $konfirmasi) {
        $hash = password_hash($baru, PASSWORD_DEFAULT);
        mysqli_query($koneksi, "UPDATE users SET password='$hash' WHERE id_user=$owner_id");
        $pesan = "✅ Password Anda telah berhasil diperbarui!";
    } else {
        $pesan = "⚠️ Gagal! Password baru dan konfirmasi tidak cocok.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Owner - Ganti Password</title>
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

        .container { max-width: 600px; margin-top: -2.5rem; position: relative; z-index: 10;}
        
        .main-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: var(--shadow);
            background-color: var(--card-bg);
            padding: 2.5rem;
        }
        
        .input-group-password {
            position: relative;
        }
        
        .form-control-password {
            padding-right: 3rem; /* Ruang untuk ikon mata */
        }
        
        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        
        .footer { text-align: center; padding: 2rem; color: #6c757d; font-size: 0.9rem; }
    </style>
</head>
<body>
    <header class="header">
        <h1>Dashboard Owner</h1>
        <h4>Ganti Password</h4>
    </header>

    <div class="container">
        <div class="main-card">
            <div class="text-center mb-4">
                <h2 class="mb-1">Ubah Password Anda</h2>
                <p class="text-muted">Untuk keamanan, gunakan password yang kuat dan unik.</p>
            </div>

            <?php if ($pesan): ?>
                <?php
                    $alert_class = strpos($pesan, '✅') !== false ? 'alert-success' : 'alert-danger';
                ?>
                <div class="alert <?= $alert_class; ?>" role="alert">
                    <?= $pesan ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label for="password_baru" class="form-label">Password Baru</label>
                    <div class="input-group-password">
                        <input type="password" id="password_baru" name="password_baru" class="form-control form-control-password" required>
                        <i class="fas fa-eye toggle-password" data-target="password_baru"></i>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password_konfirmasi" class="form-label">Konfirmasi Password Baru</label>
                    <div class="input-group-password">
                        <input type="password" id="password_konfirmasi" name="password_konfirmasi" class="form-control form-control-password" required>
                        <i class="fas fa-eye toggle-password" data-target="password_konfirmasi"></i>
                    </div>
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                     <a href="validasi.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                    <button class="btn btn-primary" type="submit" name="ganti_password">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; <?= date('Y') ?> DWI BHAKTI OFFSET • Hak Cipta Dilindungi</p>
    </footer>

    <script>
        document.querySelectorAll('.toggle-password').forEach(toggler => {
            toggler.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    this.classList.remove('fa-eye');
                    this.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    this.classList.remove('fa-eye-slash');
                    this.classList.add('fa-eye');
                }
            });
        });
    </script>
</body>
</html>