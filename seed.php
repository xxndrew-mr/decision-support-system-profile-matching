<?php
include "koneksi.php";

$pass_hrd   = password_hash("12345", PASSWORD_DEFAULT);
$pass_owner = password_hash("12345", PASSWORD_DEFAULT);

mysqli_query($koneksi, "INSERT INTO users (username, password, role) VALUES
('hrd', '$pass_hrd', 'HRD'),
('owner', '$pass_owner', 'Owner')");

echo "User HRD & Owner berhasil dibuat!";
