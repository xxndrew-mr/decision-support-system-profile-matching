<?php
require_once __DIR__ . "/../koneksi.php";
require_once __DIR__ . "/../partials/auth.php";
require_role('HRD');

function map_gap($gap) {
    $map = [
        0 => 5.0,
        1 => 4.5, -1 => 4.0,
        2 => 3.5, -2 => 3.0,
        3 => 2.5, -3 => 2.0,
        4 => 1.5, -4 => 1.0,
    ];
    return $map[$gap] ?? 1.0;
}

// Cari batch (rekrutmen) aktif berdasarkan tanggal hari ini
$rekrutmen_q = mysqli_query($koneksi, "SELECT id_rekrutmen 
    FROM rekrutmen 
    WHERE CURDATE() BETWEEN tanggal_mulai AND tanggal_selesai
    ORDER BY id_rekrutmen DESC LIMIT 1");
$rekrutmen = mysqli_fetch_assoc($rekrutmen_q);
$id_rekrutmen = $rekrutmen ? (int)$rekrutmen['id_rekrutmen'] : "NULL";

// Ambil semua posisi yang sudah ada penilaian
$posisi_q = mysqli_query($koneksi, "SELECT DISTINCT p.id_posisi 
    FROM posisi p 
    JOIN penilaian pn ON pn.id_posisi=p.id_posisi");

while ($p = mysqli_fetch_assoc($posisi_q)) {
    $id_posisi = (int)$p['id_posisi'];

    // Ambil kriteria
    $kriteria = [];
    $qk = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria");
    while ($row = mysqli_fetch_assoc($qk)) $kriteria[$row['id_kriteria']] = $row;

    // Ambil nilai ideal
    $ideal = [];
    $qi = mysqli_query($koneksi, "SELECT id_kriteria, nilai_ideal 
        FROM profile_ideal WHERE id_posisi=$id_posisi");
    while ($r = mysqli_fetch_assoc($qi)) $ideal[(int)$r['id_kriteria']] = (int)$r['nilai_ideal'];

    // Semua calon untuk posisi ini
    $qc = mysqli_query($koneksi, "SELECT DISTINCT c.* FROM calon_karyawan c
        JOIN penilaian pn ON pn.id_calon=c.id_calon 
        WHERE pn.id_posisi=$id_posisi");

    $hasil = [];
    while ($c = mysqli_fetch_assoc($qc)) {
        $id_calon = (int)$c['id_calon'];

        // Ambil nilai calon
        $nilai = [];
        $qn = mysqli_query($koneksi, "SELECT id_kriteria, nilai 
            FROM penilaian WHERE id_calon=$id_calon AND id_posisi=$id_posisi");
        while ($n = mysqli_fetch_assoc($qn)) $nilai[(int)$n['id_kriteria']] = (int)$n['nilai'];

        // Hitung CF & SF
        $cf_scores = [];
        $sf_scores = [];
        foreach ($kriteria as $idk => $kr) {
            if (!isset($ideal[$idk]) || !isset($nilai[$idk])) continue;
            $gap = $nilai[$idk] - $ideal[$idk];
            $score = map_gap($gap);

            if (strtolower($kr['factor']) === 'core') $cf_scores[] = $score;
            else $sf_scores[] = $score;
        }

        $cf_avg = $cf_scores ? array_sum($cf_scores)/count($cf_scores) : 0;
        $sf_avg = $sf_scores ? array_sum($sf_scores)/count($sf_scores) : 0;
        $total  = (0.60 * $cf_avg) + (0.40 * $sf_avg);

        $hasil[] = [ 'id_calon'=>$id_calon, 'total'=>$total ];
    }

    // Urutkan dari yang tertinggi
    usort($hasil, fn($a,$b) => $b['total'] <=> $a['total']);
    
    // Hapus data lama untuk posisi ini saja
    mysqli_query($koneksi, "DELETE FROM hasil_ranking WHERE id_posisi=$id_posisi AND is_history=0");
    
    $rank = 1;
    foreach ($hasil as $h) {
        mysqli_query($koneksi, "INSERT INTO hasil_ranking
            (id_calon,id_posisi,id_rekrutmen,total_nilai,peringkat,validasi_owner,is_history,created_at) 
            VALUES({$h['id_calon']},$id_posisi,$id_rekrutmen,{$h['total']},$rank,'Pending',0,NOW())");
        $rank++;
    }
}

// Redirect kembali ke halaman ranking setelah selesai
header("Location: ranking.php?msg=Ranking berhasil diproses.");
exit;
?>
