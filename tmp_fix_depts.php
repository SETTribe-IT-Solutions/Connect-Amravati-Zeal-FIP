<?php
require_once 'include/dbConfig.php';

// Update departments table with proper government department names
$updates = [
    1 => 'General Administration Department',
    2 => 'Revenue & District Administration',
    3 => 'Revenue & District Administration',
    4 => 'Sub-Divisional Administration',
    5 => 'Revenue (Taluka)',
    6 => 'Rural Development & Panchayati Raj',
    7 => 'Land Records & Revenue',
    8 => 'Village Development (Gram Panchayat)',
];

$success = 0;
foreach ($updates as $id => $name) {
    $stmt = $conn->prepare("UPDATE departments SET department_name = ? WHERE department_id = ?");
    $stmt->bind_param("si", $name, $id);
    if ($stmt->execute()) $success++;
    $stmt->close();
}

// Also add a status column if not exist
$conn->query("ALTER TABLE departments ADD COLUMN IF NOT EXISTS status VARCHAR(10) DEFAULT 'Active'");

echo json_encode(['updated' => $success, 'total' => count($updates)]);
