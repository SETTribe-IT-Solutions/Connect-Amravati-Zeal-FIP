<?php
require_once 'include/dbConfig.php';
$r = $conn->query("DESCRIBE audit_logs");
$cols = [];
while ($row = $r->fetch_assoc()) {
    $cols[] = $row['Field'] . ($row['Key'] === 'PRI' ? ' [PK]' : '');
}
echo json_encode($cols);
