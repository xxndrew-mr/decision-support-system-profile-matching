<?php
// partials/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login() {
    if (!isset($_SESSION['id_user'])) {
        header("Location: /spk_karyawan/index.php");
        exit;
    }
}

function require_role($role) {
    require_login();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        http_response_code(403);
        echo "<h3>Akses ditolak.</h3>";
        exit;
    }
}
