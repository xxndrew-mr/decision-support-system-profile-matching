<?php
include "koneksi.php";
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'HRD'){
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
    <title>Data Profile Ideal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --light-bg: #f0f2f5;
            --card-bg: #fff;
            --shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            --gradient-blue: linear-gradient(135deg, #0d6efd, #0056b3);
        }
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        .card {
            border: none;
            border-radius: 1.5rem;
            box-shadow: var(--shadow);
            background-color: var(--card-bg);
            padding: 2.5rem;
        }
        h2 {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }
        .nav-link.active {
            font-weight: bold;
            color: #0d6efd !important;
        }
        .nav-link {
            color: #495057;
        }
        .nav-link:hover {
            color: #0d6efd;
        }
        .btn-link {
            text-decoration: none;
            color: #0d6efd;
        }
        .btn-link:hover {
            text-decoration: underline;
        }
        .btn-custom {
            border-radius: 2rem;
            font-weight: 600;
            padding: 0.75rem 2rem;
            transition: transform 0.2s, box-shadow 0.2s;
            background: var(--gradient-blue);
            border: none;
            color: #fff;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }
        .form-control {
            border-radius: 1rem;
            padding: 0.75rem 1.25rem;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            border-color: var(--primary-color);
        }
        .table-custom {
            border-radius: 1rem;
            overflow: hidden;
            border: 1px solid #dee2e6;
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
        .btn-back {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
        }
        .btn-back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Navigation Menu -->
    <ul class="nav nav-tabs justify-content-center mb-4">
        <li class="nav-item">
            <a class="nav-link" href="hrd/calon.php">
                <i class="fas fa-user-tie"></i> Calon Karyawan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="hrd/penilaian.php">
                <i class="fas fa-chart-bar"></i> Penilaian
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="hrd/ranking.php">
                <i class="fas fa-trophy"></i> Proses Ranking
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-danger" href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="hrd/dashboard.php">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </li>
    </ul>

    <div class="card">
        <h2 class="text-center">Data Profile Ideal</h2>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="profile_ideal.php" class="btn btn-custom">
                <i class="fas fa-plus-circle"></i> Tambah/Update
            </a>
            <div class="col-md-4">
                <input type="text" id="searchInput" class="form-control" placeholder="Cari data..." aria-label="Search">
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-custom" id="profileTable">
                <thead class="table-dark">
                    <tr>
                        <th>Posisi</th>
                        <th>Kriteria</th>
                        <th>Nilai Ideal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grouped_data as $posisi => $kriteria_list): ?>
                        <tr>
                            <td rowspan="<?= count($kriteria_list) ?>"><?= htmlspecialchars($posisi) ?></td>
                            <td><?= htmlspecialchars($kriteria_list[0]['nama_kriteria']) ?></td>
                            <td><?= htmlspecialchars($kriteria_list[0]['nilai_ideal']) ?></td>
                        </tr>
                        <?php for ($i = 1; $i < count($kriteria_list); $i++): ?>
                            <tr>
                                <td><?= htmlspecialchars($kriteria_list[$i]['nama_kriteria']) ?></td>
                                <td><?= htmlspecialchars($kriteria_list[$i]['nilai_ideal']) ?></td>
                            </tr>
                        <?php endfor; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const table = document.getElementById('profileTable');
        const rows = table.getElementsByTagName('tr');

        searchInput.addEventListener('keyup', function(event) {
            const filter = event.target.value.toLowerCase();
            const parentRows = Array.from(table.querySelectorAll('tbody tr'));
            
            parentRows.forEach(row => {
                const rowText = row.innerText.toLowerCase();
                if (rowText.indexOf(filter) > -1) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });

            // Adjust rowspan for visibility after filtering
            const visibleRows = parentRows.filter(row => row.style.display !== "none");
            const visiblePositions = {};
            
            visibleRows.forEach(row => {
                const positionCell = row.querySelector('td[rowspan]');
                if (positionCell) {
                    const positionName = positionCell.textContent.trim();
                    if (visiblePositions[positionName]) {
                        visiblePositions[positionName].count++;
                    } else {
                        visiblePositions[positionName] = { element: positionCell, count: 1 };
                    }
                }
            });

            // Reset all rowspans and then set them correctly
            table.querySelectorAll('td[rowspan]').forEach(cell => cell.removeAttribute('rowspan'));
            for (const pos in visiblePositions) {
                visiblePositions[pos].element.setAttribute('rowspan', visiblePositions[pos].count);
            }
        });
    });
</script>
</body>
</html>
