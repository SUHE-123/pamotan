<?php
// config/db_hama.php
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "speha";

$db_hama = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($db_hama->connect_error) {
    die("Koneksi ke DB Hama gagal: " . $db_hama->connect_error);
}
$db_hama->set_charset("utf8mb4");
?>
