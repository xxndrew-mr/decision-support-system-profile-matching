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
            $error = "Password salah!";
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
    <title>Login SPK Perekrutan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --bg-color: #f0f2f5;
            --card-bg: #fff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 2rem;
        }
        .login-card {
            background-color: var(--card-bg);
            border-radius: 1.5rem;
            box-shadow: var(--shadow);
            padding: 3rem 2rem;
            width: 100%;
            max-width: 400px;
            text-align: center;
            transition: all 0.3s ease-in-out;
        }
        .login-card h2 {
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }
        .login-card p {
            color: var(--secondary-color);
            margin-bottom: 2rem;
        }
        .login-card .form-control {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
            margin-bottom: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .login-card .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .login-card .btn-primary {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.75rem;
            font-weight: 600;
            background-color: var(--primary-color);
            border: none;
            transition: transform 0.2s;
        }
        .login-card .btn-primary:hover {
            transform: translateY(-2px);
            background-color: #0a58ca;
        }
        .error-message {
            color: #dc3545;
            font-weight: 500;
            margin-top: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Selamat Datang</h2>
        <p>Silakan masuk untuk melanjutkan ke dashboard.</p>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger error-message" role="alert">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="text" class="form-control" name="username" placeholder="Username" required>
            <input type="password" class="form-control" name="password" placeholder="Password" required>
            <button type="submit" name="login" class="btn btn-primary">Login</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
