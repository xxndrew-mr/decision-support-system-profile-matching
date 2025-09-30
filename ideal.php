<?php
// Ganti nama file sesuai dengan nama file Anda, misal: ideal.php
require_once "koneksi.php"; 
// Sesuaikan path jika file ini ada di dalam folder, contoh: require_once __DIR__ . "/../koneksi.php";
session_start();

if(!isset($_SESSION['role']) || ($_SESSION['role'] != 'HRD' && $_SESSION['role'] != 'Owner')){
    header("Location: login.php");
    exit;
}

$query = "SELECT pi.*, p.nama_posisi, k.nama_kriteria 
          FROM profile_ideal pi
          JOIN posisi p ON pi.id_posisi=p.id_posisi
          JOIN kriteria k ON pi.id_kriteria=k.id_kriteria
          ORDER BY p.nama_posisi, k.id_kriteria";
$result = mysqli_query($koneksi, $query);

// Group data by position name for cleaner display
$grouped_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $posisi = $row['nama_posisi'];
    if (!isset($grouped_data[$posisi])) {
        $grouped_data[$posisi] = [];
    }
    $grouped_data[$posisi][] = [
        'nama_kriteria' => $row['nama_kriteria'],
        'nilai_ideal' => $row['nilai_ideal']
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRD - Data Profile Ideal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
            --text-dark: #212529;
            --text-light: #6c757d;
        }
        body { background-color: var(--light-bg); font-family: 'Segoe UI', 'Roboto', sans-serif; }
        .main-card {
            border: none; border-radius: 1.25rem; box-shadow: var(--shadow);
            background-color: var(--card-bg); padding: 2.5rem;
        }
        .nav-pills .nav-link {
            border-radius: 0.75rem;
            font-weight: 500;
        }
        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.4);
        }
        .accordion-item {
            border: none; border-radius: 1rem !important; margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            overflow: hidden;
        }
        .accordion-button {
            border-radius: 1rem !important; background-color: var(--card-bg);
            font-size: 1.15rem; font-weight: 600; color: var(--text-dark);
        }
        .accordion-button:not(.collapsed) {
            box-shadow: none; background-color: var(--primary-color);
            color: #fff;
        }
        .accordion-button:focus { box-shadow: none; }
        .accordion-button::after {
             background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23212529'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
        }
        .accordion-button:not(.collapsed)::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
        }
        .accordion-body { padding: 1.5rem; }
        .table-ideal th { width: 50%; }
        .footer { margin-top:3rem; text-align:center; color: var(--text-light); }
    </style>
</head>
<body>
<div class="container my-4">
    <ul class="nav nav-pills mb-4 justify-content-center">
        <li class="nav-item"><a class="nav-link" href="hrd/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="hrd/calon.php"><i class="fas fa-user-plus me-2"></i>Calon Karyawan</a></li>
        <li class="nav-item"><a class="nav-link active" href="ideal.php"><i class="fas fa-cogs me-2"></i>Profil Ideal</a></li>
        <li class="nav-item"><a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
    </ul>

    <div class="main-card">
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <h2 class="mb-0">Data Profil Ideal</h2>
                <p class="text-muted">Standar nilai yang dibutuhkan untuk setiap posisi.</p>
            </div>
            <div class="col-md-6 d-flex gap-2 justify-content-md-end">
                <input type="text" id="searchInput" class="form-control w-50" placeholder="Cari posisi...">
                <a href="profile_ideal.php" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i> Kelola Nilai
                </a>
            </div>
        </div>
        
        <div class="accordion" id="profileAccordion">
            <?php if (empty($grouped_data)): ?>
                <div class="alert alert-warning text-center">Belum ada data profil ideal yang diatur.</div>
            <?php else: $i = 0; ?>
                <?php foreach ($grouped_data as $posisi => $kriteria_list): $i++; ?>
                    <div class="accordion-item" data-search-term="<?= strtolower(htmlspecialchars($posisi)) ?>">
                        <h2 class="accordion-header" id="heading-<?= $i ?>">
                            <button class="accordion-button <?= $i > 1 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $i ?>">
                                <?= htmlspecialchars($posisi) ?>
                            </button>
                        </h2>
                        <div id="collapse-<?= $i ?>" class="accordion-collapse collapse <?= $i == 1 ? 'show' : '' ?>" data-bs-parent="#profileAccordion">
                            <div class="accordion-body">
                                <table class="table table-bordered table-hover table-ideal">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kriteria</th>
                                            <th>Nilai Ideal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($kriteria_list as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['nama_kriteria']) ?></td>
                                                <td><span class="badge bg-primary fs-6"><?= htmlspecialchars($item['nilai_ideal']) ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
    <footer class="footer"><p>&copy; <?= date('Y') ?> HRD Panel • SPK Profile Matching</p></footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const accordionItems = document.querySelectorAll('.accordion-item');

        searchInput.addEventListener('keyup', function(event) {
            const filter = event.target.value.toLowerCase();

            accordionItems.forEach(item => {
                const searchTerm = item.dataset.searchTerm;
                if (searchTerm.includes(filter)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
</script>
</body>
</html>