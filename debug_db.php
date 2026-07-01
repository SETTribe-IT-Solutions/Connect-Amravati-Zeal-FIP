<?php
// Temporary debug script - DELETE AFTER USE

// Local DB fallback constants
define('DB_HOST_LOCAL', 'localhost');
define('DB_USER_LOCAL', 'root');
define('DB_PASS_LOCAL', '');
define('DB_NAME_LOCAL', 'nmrmlatur_districtCNTDB');

echo "<pre>";
echo "=== DB Debug Script ===\n\n";

// Test remote DB
echo "1. Testing REMOTE DB connection...\n";
echo "   Host: 103.160.107.18\n";
echo "   DB:   nmrmlatur_districtCNTDB\n";
$remote = @new mysqli('103.160.107.18', 'nmrmlatur_districCNTZEAL', 'districtCNTDB@2026', 'nmrmlatur_districtCNTDB');
if ($remote->connect_errno) {
    echo "   STATUS: FAILED - " . $remote->connect_error . "\n\n";
    $useRemote = false;
} else {
    echo "   STATUS: OK\n\n";
    $useRemote = true;
}

// Test local DB
echo "2. Testing LOCAL DB connection...\n";
echo "   Host: localhost\n";
echo "   DB:   nmrmlatur_districtCNTDB\n";
$local = @new mysqli('localhost', 'root', '', 'nmrmlatur_districtCNTDB');
if ($local->connect_errno) {
    echo "   STATUS: FAILED - " . $local->connect_error . "\n\n";
    $useLocal = false;
} else {
    echo "   STATUS: OK\n\n";
    $useLocal = true;
}

// Use whichever works
$conn = null;
if ($useLocal) {
    $conn = $local;
    echo "3. Using LOCAL database.\n\n";
} elseif ($useRemote) {
    $conn = $remote;
    echo "3. Using REMOTE database.\n\n";
} else {
    echo "3. NO DATABASE CONNECTION AVAILABLE!\n\n";
    exit;
}

$conn->set_charset('utf8mb4');

// Show tables
echo "4. Tables in database:\n";
$r = $conn->query("SHOW TABLES");
if ($r) {
    while ($t = $r->fetch_row()) {
        echo "   - " . $t[0] . "\n";
    }
} else {
    echo "   ERROR: " . $conn->error . "\n";
}

echo "\n5. Checking 'users' table...\n";
$r2 = $conn->query("DESCRIBE users");
if ($r2) {
    echo "   Columns:\n";
    while ($col = $r2->fetch_assoc()) {
        echo "   - {$col['Field']} ({$col['Type']}) " . ($col['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . " " . ($col['Key'] ? "[{$col['Key']}]" : "") . "\n";
    }
} else {
    echo "   ERROR: " . $conn->error . " (table may not exist)\n";
}

echo "\n6. User count:\n";
$r3 = $conn->query("SELECT COUNT(*) as cnt FROM users");
if ($r3) {
    $row = $r3->fetch_assoc();
    echo "   Total users: " . $row['cnt'] . "\n";
} else {
    echo "   ERROR: " . $conn->error . "\n";
}

echo "\n7. Checking 'departments' table...\n";
$r4 = $conn->query("SELECT COUNT(*) as cnt FROM departments WHERE status='Active'");
if ($r4) {
    $row = $r4->fetch_assoc();
    echo "   Active departments: " . $row['cnt'] . "\n";
} else {
    echo "   ERROR: " . $conn->error . "\n";
}

echo "\n8. Checking 'roles' table...\n";
$r5 = $conn->query("SELECT COUNT(*) as cnt FROM roles WHERE status='Active'");
if ($r5) {
    $row = $r5->fetch_assoc();
    echo "   Active roles: " . $row['cnt'] . "\n";
} else {
    echo "   ERROR: " . $conn->error . "\n";
}

echo "\n9. Checking 'talukas' table...\n";
$r6 = $conn->query("SELECT COUNT(*) as cnt FROM talukas");
if ($r6) {
    $row = $r6->fetch_assoc();
    echo "   Total talukas: " . $row['cnt'] . "\n";
} else {
    echo "   ERROR: " . $conn->error . "\n";
}

echo "\n10. Checking 'villages' table...\n";
$r7 = $conn->query("SELECT COUNT(*) as cnt FROM villages");
if ($r7) {
    $row = $r7->fetch_assoc();
    echo "   Total villages: " . $row['cnt'] . "\n";
} else {
    echo "   ERROR: " . $conn->error . "\n";
}

echo "\n=== END DEBUG ===\n";
echo "</pre>";
?>
