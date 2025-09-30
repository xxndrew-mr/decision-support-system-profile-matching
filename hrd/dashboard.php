<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DWI BHAKTI OFFSET</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --primary-light: #4dabf7;
            --secondary-color: #6c757d;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --text-dark: #212529;
            --text-light: #6c757d;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
            --gradient-primary: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', 'Roboto', sans-serif;
            color: var(--text-dark);
        }

        .header {
            background: var(--gradient-primary);
            color: #fff;
            padding: 2rem 2rem 5rem 2rem;
            text-align: center;
            position: relative;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
        }
        
        .header-content h1 {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .header-content h4 {
            font-weight: 300;
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
            transform: rotate(180deg);
        }

        .wave svg {
            position: relative;
            display: block;
            width: calc(100% + 1.3px);
            height: 70px;
        }

        .wave .shape-fill {
            fill: var(--light-bg);
        }

        .main-container {
            margin-top: -3rem;
            position: relative;
            z-index: 10;
        }

        .welcome-card {
            background: var(--card-bg);
            border: none;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow);
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .welcome-card h2 {
            font-weight: 600;
            color: var(--text-dark);
        }

        .welcome-card span {
            color: var(--primary-color);
            font-weight: 700;
        }

        .card-menu {
            border: none;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            background-color: var(--card-bg);
            padding: 2rem;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: var(--text-dark);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        
        .card-menu:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            color: var(--primary-color);
        }
        
        .card-menu .icon-container {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #e6f7ff 0%, #d0ebff 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            transition: background 0.3s;
        }

        .card-menu:hover .icon-container {
             background: var(--gradient-primary);
        }

        .card-menu i {
            font-size: 2.5rem;
            color: var(--primary-color);
            transition: color 0.3s;
        }

        .card-menu:hover i {
            color: #fff;
        }
        
        .card-menu h3 {
            font-weight: 600;
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }

        .card-menu p {
            font-size: 0.9rem;
            color: var(--text-light);
            line-height: 1.5;
        }
        
        .info-card {
            background-color: var(--card-bg);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: var(--shadow);
            height: 100%;
        }

        .info-card table {
            margin-top: 1.5rem;
        }

        .info-card th {
            background-color: var(--text-dark);
            color: #fff;
        }

        .footer {
            text-align: center;
            padding: 2rem;
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin-top: 3rem;
        }

        .btn-logout {
            border-radius: 50px;
            font-weight: 600;
        }
        
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <h1>DWI BHAKTI OFFSET</h1>
            <h4>Sistem Pendukung Keputusan Perekrutan Karyawan</h4>
        </div>
        <div class="wave">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M985.66,92.83C906.67,72,823.78,31.84,743.84,14.19c-82.26-17.64-168.06-16.33-250.45.39-57.84,11.73-114,31.07-172,41.86A600.21,600.21,0,0,1,0,27.35V120H1200V95.8C1132.19,118.92,1055.71,111.31,985.66,92.83Z" class="shape-fill"></path>
            </svg>
        </div>
    </header>

    <div class="container main-container">
        
        <div class="welcome-card">
            <h2>Selamat Datang, <span><?php echo htmlspecialchars($_SESSION['username']); ?>!</span></h2>
            <p class="text-muted">Pilih menu di bawah untuk mulai mengelola data perekrutan karyawan.</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <div class="col">
                        <a href="calon.php" class="card-menu">
                            <div class="icon-container"><i class="fas fa-user-plus"></i></div>
                            <h3>Calon Karyawan</h3>
                            <p>Kelola data pelamar dan berikan penilaian berdasarkan kriteria.</p>
                        </a>
                    </div>
                    <div class="col">
                        <a href="ranking.php" class="card-menu">
                            <div class="icon-container"><i class="fas fa-trophy"></i></div>
                            <h3>Hasil Ranking</h3>
                            <p>Lihat hasil perangkingan kandidat berdasarkan metode Profile Matching.</p>
                        </a>
                    </div>
                    <div class="col">
                        <a href="/spk_karyawan/ideal.php" class="card-menu">
                            <div class="icon-container"><i class="fas fa-cogs"></i></div>
                            <h3>Profil Ideal</h3>
                            <p>Atur dan sesuaikan nilai standar yang dibutuhkan untuk setiap posisi.</p>
                        </a>
                    </div>
                    <div class="col">
                        <a href="riwayat.php" class="card-menu">
                            <div class="icon-container"><i class="fas fa-history"></i></div>
                            <h3>Riwayat Ranking</h3>
                            <p>Akses arsip dan laporan hasil perangkingan dari periode sebelumnya.</p>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                 <div class="info-card">
                    <h4 class="mb-3">Tentang Metode</h4>
                    <p class="text-muted small">Metode <strong>Profile Matching</strong> membandingkan profil kompetensi calon dengan profil ideal posisi untuk menemukan kandidat terbaik.</p>
                    
                    <hr class="my-4">

                    <h4 class="mb-3 text-center">Tabel Bobot GAP</h4>
                    <table class="table table-sm table-bordered text-center">
                        <thead class="thead-dark">
                            <tr><th>Selisih</th><th>Bobot</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>0</td><td>5.0</td></tr>
                            <tr><td>1</td><td>4.5</td></tr>
                            <tr><td>-1</td><td>4.0</td></tr>
                            <tr><td>2</td><td>3.5</td></tr>
                            <tr><td>-2</td><td>3.0</td></tr>
                            <tr><td>3</td><td>2.5</td></tr>
                            <tr><td>-3</td><td>2.0</td></tr>
                            <tr><td>4</td><td>1.5</td></tr>
                            <tr><td>-4</td><td>1.0</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="/spk_karyawan/logout.php" class="btn btn-danger btn-lg btn-logout">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?= date("Y") ?> DWI BHAKTI OFFSET • Sistem Pendukung Keputusan Karyawan</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>