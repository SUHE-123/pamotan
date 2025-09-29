<?php
include("config.php"); // pastikan file ini berisi koneksi ke $conn

// Periksa apakah parameter dikirim (GET atau POST)
if (isset($_REQUEST["soilMoisture"]) && isset($_REQUEST["pH"]) && isset($_REQUEST["pompa"])) {
    $soil = floatval($_REQUEST["soilMoisture"]);
    $ph   = floatval($_REQUEST["pH"]);
    $pompa = strtoupper(trim($_REQUEST["pompa"])) === "ON" ? "ON" : "OFF"; // Validasi input

    // Gunakan prepared statement
    $stmt = $conn->prepare("INSERT INTO sensor_data (soil_moisture, ph, pompa_status, waktu) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("dds", $soil, $ph, $pompa); // "d" = double, "s" = string

    if ($stmt->execute()) {
        echo "✅ Data berhasil disimpan.";
    } else {
        echo "❌ Error saat menyimpan: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "❌ Parameter tidak lengkap.";
}

$conn->close();
?>
