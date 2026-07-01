<?php
require_once 'include/dbConfig.php';
$r = $conn->query("SELECT department_id, department_name FROM departments LIMIT 20");
$out = [];
while ($row = $r->fetch_assoc()) $out[] = $row;
echo json_encode($out);
