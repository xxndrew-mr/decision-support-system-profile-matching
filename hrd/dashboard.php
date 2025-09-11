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
    <title>DWI BHAKTI OFFSET</title>
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
        .header h4 {
            font-weight: 400;
            font-size: 1rem;
            opacity: 0.9;
        }
        .container {
            flex-grow: 1;
            padding: 2rem 1rem;
            max-width: 1200px;
            margin-top: 0rem; /* Overlap with the header */
            position: relative;
            z-index: 10;
        }
        .info-card {
            background-color: var(--card-bg);
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: var(--shadow);
            animation: fadeIn 1s ease-in-out;
        }
        .info-card h2 {
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        .info-card p strong {
            color: var(--primary-color);
        }
        .info-card table {
            margin-top: 1.5rem;
            border-collapse: separate;
            border-spacing: 0 0.5rem;
        }
        .info-card table th, .info-card table td {
            text-align: center;
            border: none;
            padding: 0.75rem;
        }
        .info-card table thead th {
            border-bottom: 2px solid var(--primary-color);
        }
        .info-card table tbody tr {
            background-color: var(--light-bg);
            border-radius: 0.75rem;
            transition: all 0.2s ease-in-out;
        }
        .info-card table tbody tr:hover {
            background-color: #e2e6ea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .info-card table tbody td:first-child {
            border-top-left-radius: 0.75rem;
            border-bottom-left-radius: 0.75rem;
        }
        .info-card table tbody td:last-child {
            border-top-right-radius: 0.75rem;
            border-bottom-right-radius: 0.75rem;
        }
        .menu-grid {
            margin-top: 2rem;
        }
        .card-menu {
            border: none;
            border-radius: 1.5rem;
            box-shadow: var(--shadow);
            background-color: var(--card-bg);
            padding: 2.5rem;
            text-align: center;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 220px;
        }
        .card-menu:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        .card-menu i {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            transition: transform 0.3s, color 0.3s;
        }
        .card-menu:hover i {
            transform: scale(1.1);
        }
        .card-menu h3 {
            font-weight: 600;
            font-size: 1.35rem;
            transition: color 0.3s;
        }
        .card-menu:hover h3 {
            color: var(--primary-color);
        }
        .logout-container {
            text-align: center;
            margin-top: 3rem;
            animation: fadeIn 1.5s ease-in-out;
        }
        .btn-logout {
            background: var(--gradient-red);
            color: #fff;
            border: none;
            padding: 1rem 2.5rem;
            border-radius: 2rem;
            font-size: 1.1rem;
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

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideDown {
            from { transform: translateY(-100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DWI BHAKTI OFFSET</h1>
        <h4>Jl. Otista Raya No.36, Ciputat, Kec. Ciputat, Kota Tangerang Selatan, Banten 15411.</h4>
    </div>

    <div class="container">
        <!-- Main content row for the two-column layout -->
        <div class="row g-4">
            <!-- Left Column for Info Card -->
            <div class="col-lg-6">
                <div class="info-card mb-lg-0">
                    <h2>Apa itu Metode Profile Matching?</h2>
                    <p>Sistem Pendukung Keputusan (SPK) ini menggunakan metode <strong>Profile Matching</strong>. Metode ini bekerja dengan membandingkan profil setiap calon karyawan dengan profil ideal yang dibutuhkan oleh suatu posisi. Perbedaan (gap) antara profil calon dan profil ideal dihitung untuk menentukan seberapa cocok seorang kandidat.</p>
                    <p>Semakin kecil nilai <em>gap</em> yang didapatkan, maka semakin tinggi nilai kandidat tersebut. Nilai akhir dari setiap calon karyawan dihitung berdasarkan nilai total dan nilai rangking yang akan dihasilkan pada halaman Ranking. Hal ini membantu dalam pengambilan keputusan yang lebih objektif dan akurat.</p>
                    
                    <div class="table-responsive">
                        <h4 class="mt-4 mb-3 text-center">Tabel Nilai Bobot GAP</h4>
                        <table class="table rounded">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">Selisih (Gap)</th>
                                    <th scope="col">Nilai Bobot</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>0</td>
                                    <td>5.0</td>
                                </tr>
                                <tr>
                                    <td>1</td>
                                    <td>4.5</td>
                                </tr>
                                <tr>
                                    <td>-1</td>
                                    <td>4.0</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>3.5</td>
                                </tr>
                                <tr>
                                    <td>-2</td>
                                    <td>3.0</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>2.5</td>
                                </tr>
                                <tr>
                                    <td>-3</td>
                                    <td>2.0</td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>1.5</td>
                                </tr>
                                <tr>
                                    <td>-4</td>
                                    <td>1.0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column for Menu Cards -->
            <div class="col-lg-6">
                <div class="row row-cols-1 row-cols-md-2 g-4 menu-grid">
                    <div class="col">
                        <a class="card-menu">
                             <p>Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>! Pilih menu di bawah untuk melanjutkan.</p>
                        </a>
                    </div>
                    <!-- Calon Karyawan Card -->
                    <div class="col">
                        <a href="calon.php" class="card-menu">
                            <i class="fas fa-user-tie"></i>
                            <h3>Calon Karyawan</h3>
                        </a>
                    </div>
                    <!-- Penilaian Card -->
                    <div class="col">
                        <a href="penilaian.php" class="card-menu">
                            <i class="fas fa-chart-bar"></i>
                            <h3>Penilaian</h3>
                        </a>
                    </div>
                    <!-- Ranking Card -->
                    <div class="col">
                        <a href="ranking.php" class="card-menu">
                            <i class="fas fa-trophy"></i>
                            <h3>Ranking</h3>
                        </a>
                    </div>
                    <!-- Nilai Ideal Card -->
                    <div class="col">
                        <a href="/spk_karyawan/ideal.php" class="card-menu">
                            <i class="fas fa-cogs"></i>
                            <h3>Nilai Ideal</h3>
                        </a>
                    </div>
                    <div class="col">
                        <a href="riwayat.php" class="card-menu">
                            <i class="fas fa-history"></i></i>
                            <h3>Riwayat</h3>
                        </a>
                    </div>
                    <div class="logout-container">
            <a href="/spk_karyawan/logout.php" class="btn btn-danger btn-lg btn-logout">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>
                </div>
            </div>
        </div>        
    </div>

    <footer class="footer">
        <p>&copy; 2023 SPK Karyawan • Hak Cipta Dilindungi</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
