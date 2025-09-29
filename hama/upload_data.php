<?php
include 'koneksi.php';

$hujan = $_POST['curah_hujan'] ?? '';
$suhu = $_POST['suhu'] ?? '';
$kelembaban = $_POST['kelembaban'] ?? '';
$angin = $_POST['angin'] ?? '';

// Simpan curah hujan
if ($hujan !== '') {
    $stmt = $koneksi->prepare("INSERT INTO curah_hujan (nilai, waktu) VALUES (?, NOW())");
    $stmt->bind_param("d", $hujan);
    $stmt->execute();
}

// Simpan DHT22 (suhu dan kelembaban)
if ($suhu !== '' && $kelembaban !== '') {
    $stmt = $koneksi->prepare("INSERT INTO dht_data (suhu, kelembaban, waktu) VALUES (?, ?, NOW())");
    $stmt->bind_param("dd", $suhu, $kelembaban);
    $stmt->execute();
}

// Simpan kecepatan angin dari anemometer
if ($angin !== '') {
    $stmt = $koneksi->prepare("INSERT INTO anemometer (kecepatan, waktu) VALUES (?, NOW())");
    $stmt->bind_param("d", $angin);
    $stmt->execute();
}

echo "Data berhasil disimpan";
?>
