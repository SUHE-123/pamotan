<?php
require 'config.php';

// Set header untuk JSON
header('Content-Type: application/json');

// Inisialisasi response dengan nilai default
$response = [
  "mode" => "AUTO",
  "jadwal_air" => [0, 0, 0, 0], // [jam1, menit1, jam2, menit2]
  "pemupukan" => []             // List data pemupukan
];

// Ambil data penyiraman (AUTO / MANUAL + jadwal)
$queryPenyiraman = $conn->query("SELECT * FROM penyiraman_air LIMIT 1");
if ($dataPenyiraman = $queryPenyiraman->fetch_assoc()) {
  $response['mode'] = strtoupper(trim($dataPenyiraman['mode'] ?? 'AUTO'));

  $response['jadwal_air'] = [
    intval($dataPenyiraman['jam1'] ?? 0),
    intval($dataPenyiraman['menit1'] ?? 0),
    intval($dataPenyiraman['jam2'] ?? 0),
    intval($dataPenyiraman['menit2'] ?? 0)
  ];
}

// Ambil data pemupukan (jika ada)
$queryPupuk = $conn->query("SELECT * FROM pemupukan");
while ($dataPupuk = $queryPupuk->fetch_assoc()) {
  $response['pemupukan'][] = [
    "jenis"   => strtoupper(trim($dataPupuk['jenis'] ?? 'N')),
    "tanggal" => $dataPupuk['tanggal'] ?? '1970-01-01',
    "jam"     => intval($dataPupuk['jam'] ?? 0),
    "menit"   => intval($dataPupuk['menit'] ?? 0)
  ];
}

// Outputkan JSON
echo json_encode($response);
