<?php
// config/db_irigasi.php
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "caiwarav2";

$db_irigasi = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($db_irigasi->connect_error) {
    die("Koneksi ke DB Irigasi gagal: " . $db_irigasi->connect_error);
}
$db_irigasi->set_charset("utf8mb4");
?>
