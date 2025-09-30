<?php
session_start();
include "koneksi.php";

$error = "";

// Kalau tombol login ditekan
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    // Cek user
    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' LIMIT 1");
    $data  = mysqli_fetch_assoc($query);

    if ($data) {
        // Verifikasi password
        if (password_verify($password, $data['password'])) {
            // Simpan session
            $_SESSION['id_user'] = $data['id_user'];
            $_SESSION['role']    = $data['role'];
            $_SESSION['username'] = $data['username'];

            // Redirect sesuai role
            if ($data['role'] === "HRD") {
                header("Location: hrd/dashboard.php");
                exit;
            } elseif ($data['role'] === "Owner") {
                header("Location: owner/validasi.php");
                exit;
            }
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DWI BHAKTI OFFSET</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --light-bg: #f8f9fa;
            --text-dark: #212529;
            --text-light: #6c757d;
            --gradient-primary: linear-gradient(135deg, #0d6efd 0%, #4dabf7 100%);
        }
        
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', 'Roboto', sans-serif;
        }

        .login-container {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        .login-art {
            flex: 1;
            background: var(--gradient-primary);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 4rem;
            text-align: center;
        }

        .login-art h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            letter-spacing: 1px;
        }

        .login-art p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 400px;
        }

        .login-art .logo-placeholder {
            font-size: 4rem;
            margin-bottom: 2rem;
        }

        .login-form-container {
            flex: 1;
            background-color: var(--light-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        
        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 3rem;
            background-color: #fff;
            border-radius: 1rem;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            animation: fadeIn 1s ease-in-out;
        }

        .login-card h2 {
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }
        
        .login-card .text-muted {
            margin-bottom: 2.5rem;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .input-group-custom .form-control {
            padding-left: 3rem;
            height: 50px;
            border-radius: 0.75rem;
        }
        
        .input-group-custom .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            z-index: 5;
        }

        .btn-login {
            width: 100%;
            padding: 0.85rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1.1rem;
            background: var(--gradient-primary);
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.3);
        }

        @media (max-width: 768px) {
            .login-art {
                display: none;
            }
            .login-form-container {
                flex-basis: 100%;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-art">
            <div class="logo-placeholder">
                <i class="fas fa-building"></i>
            </div>
            <h1>DWI BHAKTI OFFSET</h1>
            <p>Sistem Pendukung Keputusan untuk Perekrutan Karyawan yang Lebih Objektif dan Efisien.</p>
        </div>
        
        <div class="login-form-container">
            <div class="login-card">
                <h2 class="text-center">Selamat Datang</h2>
                <p class="text-center text-muted">Silakan masuk untuk melanjutkan.</p>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?= $error; ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="input-group-custom">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-control" name="username" placeholder="Username" required>
                    </div>
                    <div class="input-group-custom">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary btn-login mt-3">Login</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>