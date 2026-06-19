<?php
include "config/configs.php";

echo "=== force_channels table ===\n";
$query = "SELECT * FROM `force_channels`";
$stmt = $pdo->query($query);
$channels = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($channels)) {
    echo "EMPTY - no rows\n";
} else {
    foreach ($channels as $row) {
        echo json_encode($row) . "\n";
    }
}

echo "\n=== settings table ===\n";
$query2 = "SELECT * FROM `settings`";
$stmt2 = $pdo->query($query2);
$settings = $stmt2->fetchAll(PDO::FETCH_ASSOC);

if (empty($settings)) {
    echo "EMPTY - no rows\n";
} else {
    foreach ($settings as $row) {
        echo json_encode($row) . "\n";
    }
}
