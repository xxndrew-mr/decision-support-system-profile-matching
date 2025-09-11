<?php
include "koneksi.php";
session_start();

// Hanya HRD yang bisa akses halaman ini
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'HRD'){
    header("Location: login.php");
    exit;
}

// Ambil semua posisi dan kriteria
$posisi = mysqli_query($koneksi, "SELECT * FROM posisi ORDER BY nama_posisi");
$kriteria = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria");

// Bagian untuk menangani permintaan AJAX (JS)
if(isset($_GET['id_posisi'])) {
    $id_posisi = $_GET['id_posisi'];
    $ideal_data = array();
    $query_ideal = mysqli_query($koneksi, "SELECT id_kriteria, nilai_ideal FROM profile_ideal WHERE id_posisi='$id_posisi'");
    while($row = mysqli_fetch_assoc($query_ideal)) {
        $ideal_data[$row['id_kriteria']] = $row['nilai_ideal'];
    }
    header('Content-Type: application/json');
    echo json_encode($ideal_data);
    exit;
}

// Logika untuk menyimpan data ke database
if(isset($_POST['simpan'])){
    $id_posisi = $_POST['id_posisi'];
    foreach($_POST['nilai'] as $id_kriteria => $nilai){
        // Cek apakah data sudah ada
        $cek = mysqli_query($koneksi, "SELECT * FROM profile_ideal 
            WHERE id_posisi='$id_posisi' AND id_kriteria='$id_kriteria'");
        if(mysqli_num_rows($cek) > 0){
            // Jika sudah ada, update
            mysqli_query($koneksi, "UPDATE profile_ideal 
                SET nilai_ideal='$nilai' 
                WHERE id_posisi='$id_posisi' AND id_kriteria='$id_kriteria'");
        } else {
            // Jika belum ada, insert baru
            mysqli_query($koneksi, "INSERT INTO profile_ideal(id_posisi,id_kriteria,nilai_ideal) 
                VALUES('$id_posisi','$id_kriteria','$nilai')");
        }
    }
    echo "<script>alert('Profile ideal berhasil disimpan!'); window.location='ideal.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Profile Ideal</title>
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
            align-items: flex-start;
            min-height: 100vh;
            margin: 0;
            padding: 2rem;
        }
        .form-card {
            background-color: var(--card-bg);
            border-radius: 1.5rem;
            box-shadow: var(--shadow);
            padding: 2.5rem;
            width: 100%;
            max-width: 800px;
        }
        .form-card h3 {
            font-weight: 700;
            color: #333;
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 0.5rem;
        }
        .table-custom {
            border-radius: 1rem;
            overflow: hidden;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        .table-custom thead {
            background-color: var(--primary-color);
            color: white;
        }
        .table-custom th, .table-custom td {
            padding: 1.25rem;
            vertical-align: middle;
            border: none;
        }
        .table-custom tbody tr {
            transition: background-color 0.2s;
        }
        .table-custom tbody tr:hover {
            background-color: #f8f9fa;
        }
        .btn-group-footer {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2.5rem;
        }
        .btn-custom {
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
        /* Custom Dropdown Styles */
        .custom-dropdown-container {
            position: relative;
            width: 100%;
        }
        .custom-dropdown-button {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
            border-radius: 0.75rem;
            background-color: #fff;
            cursor: pointer;
            transition: border-color 0.3s, box-shadow 0.3s;
            font-size: 1rem;
        }
        .custom-dropdown-button:focus, .custom-dropdown-button:hover {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .custom-dropdown-list {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.75rem;
            margin-top: 0.5rem;
            box-shadow: var(--shadow);
            z-index: 1000;
            display: none;
        }
        .custom-dropdown-list.show {
            display: block;
        }
        .custom-dropdown-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .custom-dropdown-item:hover {
            background-color: var(--bg-color);
        }
        .custom-dropdown-item.active {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
        }
        /* Style untuk loading state */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    <div class="form-card position-relative">
        <div class="loading-overlay" id="loading-overlay">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        
        <h3>Kelola Profile Ideal</h3>
        <form method="post" id="ideal-form">
            <div class="mb-4">
                <label class="form-label">Pilih Posisi:</label>
                <div class="custom-dropdown-container">
                    <button type="button" class="custom-dropdown-button" id="posisi-dropdown-btn">
                        <span id="posisi-dropdown-text">-- Pilih Posisi --</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <ul class="custom-dropdown-list" id="posisi-dropdown-list">
                        <?php mysqli_data_seek($posisi, 0); 
                        while($p = mysqli_fetch_assoc($posisi)){ ?>
                            <li class="custom-dropdown-item" data-value="<?= $p['id_posisi'] ?>"><?= $p['nama_posisi'] ?></li>
                        <?php } ?>
                    </ul>
                    <select name="id_posisi" id="id_posisi" class="d-none" required>
                        <option value="">-- Pilih Posisi --</option>
                        <?php mysqli_data_seek($posisi, 0);
                        while($p = mysqli_fetch_assoc($posisi)){ ?>
                            <option value="<?= $p['id_posisi'] ?>"><?= $p['nama_posisi'] ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <table class="table table-bordered table-custom" id="kriteria-table">
                <thead>
                    <tr>
                        <th>Kriteria</th>
                        <th>Nilai Ideal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($k = mysqli_fetch_assoc($kriteria)){ ?>
                    <tr>
                        <td><?= $k['nama_kriteria'] ?></td>
                        <td>
                            <!-- Custom Dropdown untuk Nilai Ideal -->
                            <div class="custom-dropdown-container">
                                <button type="button" class="custom-dropdown-button nilai-dropdown-btn">
                                    <span>Pilih Nilai</span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <ul class="custom-dropdown-list nilai-dropdown-list">
                                    <?php for ($i = 1; $i <= 5; $i++) { ?>
                                        <li class="custom-dropdown-item" data-value="<?= $i ?>"><?= $i ?></li>
                                    <?php } ?>
                                </ul>
                                <input type="hidden" name="nilai[<?= $k['id_kriteria'] ?>]" class="nilai-input" required>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <div class="btn-group-footer">
                <button type="submit" name="simpan" class="btn btn-primary btn-custom">
                    <i class="fas fa-save me-2"></i>Simpan
                </button>
                <a href="ideal.php" class="btn btn-secondary btn-custom">
                    <i class="fas fa-eye me-2"></i>Lihat Data
                </a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const posisiSelect = document.getElementById('id_posisi');
            const posisiDropdownBtn = document.getElementById('posisi-dropdown-btn');
            const posisiDropdownList = document.getElementById('posisi-dropdown-list');
            const posisiDropdownText = document.getElementById('posisi-dropdown-text');
            const posisiDropdownItems = posisiDropdownList.querySelectorAll('.custom-dropdown-item');
            
            const tableRows = document.querySelectorAll('#kriteria-table tbody tr');
            const loadingOverlay = document.getElementById('loading-overlay');

            // --- Fungsionalitas Dropdown Posisi ---
            posisiDropdownBtn.addEventListener('click', function() {
                posisiDropdownList.classList.toggle('show');
            });

            posisiDropdownItems.forEach(item => {
                item.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    const text = this.textContent;

                    posisiDropdownText.textContent = text;
                    posisiDropdownList.classList.remove('show');
                    
                    posisiSelect.value = value;
                    posisiSelect.dispatchEvent(new Event('change'));

                    posisiDropdownItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // --- Fungsionalitas Dropdown Nilai Ideal ---
            tableRows.forEach(row => {
                const nilaiDropdownBtn = row.querySelector('.nilai-dropdown-btn');
                const nilaiDropdownList = row.querySelector('.nilai-dropdown-list');
                const nilaiDropdownItems = nilaiDropdownList.querySelectorAll('.custom-dropdown-item');
                const nilaiInput = row.querySelector('.nilai-input');
                const nilaiText = nilaiDropdownBtn.querySelector('span');

                nilaiDropdownBtn.addEventListener('click', function(event) {
                    // Tutup dropdown lain sebelum membuka yang baru
                    document.querySelectorAll('.custom-dropdown-list').forEach(list => {
                        if (list !== nilaiDropdownList) {
                            list.classList.remove('show');
                        }
                    });
                    nilaiDropdownList.classList.toggle('show');
                    event.stopPropagation();
                });

                nilaiDropdownItems.forEach(item => {
                    item.addEventListener('click', function(event) {
                        const value = this.getAttribute('data-value');
                        nilaiInput.value = value;
                        nilaiText.textContent = value;
                        nilaiDropdownList.classList.remove('show');
                        
                        nilaiDropdownItems.forEach(i => i.classList.remove('active'));
                        this.classList.add('active');
                        event.stopPropagation();
                    });
                });
            });

            // --- Tutup semua dropdown saat klik di luar area ---
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.custom-dropdown-container')) {
                    document.querySelectorAll('.custom-dropdown-list').forEach(list => {
                        list.classList.remove('show');
                    });
                }
            });

            // --- Logika Pengambilan Data AJAX ---
            posisiSelect.addEventListener('change', function() {
                const id_posisi = this.value;
                loadingOverlay.style.display = 'flex';
                
                // Reset semua nilai ke default
                tableRows.forEach(row => {
                    row.querySelector('.nilai-input').value = '';
                    row.querySelector('.nilai-dropdown-btn span').textContent = 'Pilih Nilai';
                    row.querySelectorAll('.custom-dropdown-item').forEach(i => i.classList.remove('active'));
                });

                if (id_posisi) {
                    fetch(`ideal.php?id_posisi=${id_posisi}`)
                        .then(response => response.json())
                        .then(data => {
                            tableRows.forEach(row => {
                                const input = row.querySelector('.nilai-input');
                                const id_kriteria = input.name.match(/\[(.*?)\]/)[1];
                                if (data[id_kriteria]) {
                                    input.value = data[id_kriteria];
                                    const dropdownItem = row.querySelector(`.custom-dropdown-item[data-value="${data[id_kriteria]}"]`);
                                    if (dropdownItem) {
                                        row.querySelector('.nilai-dropdown-btn span').textContent = dropdownItem.textContent;
                                        dropdownItem.classList.add('active');
                                    }
                                }
                            });
                        })
                        .catch(error => {
                            console.error('Error fetching data:', error);
                            // alert('Gagal mengambil data. Silakan coba lagi.');
                        })
                        .finally(() => {
                            loadingOverlay.style.display = 'none';
                        });
                } else {
                    loadingOverlay.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
