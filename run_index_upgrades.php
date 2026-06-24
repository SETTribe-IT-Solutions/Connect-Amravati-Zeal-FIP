<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/include/dbConfig.php';

if (!isset($conn) || !$conn instanceof mysqli) {
    die("Database connection not established in dbConfig.php\n");
}

echo "Starting database index upgrades...\n";

/**
 * Safely add an index to a table if it does not already exist.
 *
 * @param mysqli $conn      Database connection
 * @param string $table     Table name
 * @param string $indexName Index name
 * @param string $columns   Comma-separated columns to index
 */
function add_index_if_not_exists(mysqli $conn, string $table, string $indexName, string $columns) {
    echo "Checking index '$indexName' on table '$table'...\n";
    
    // Check if index exists
    $stmt = $conn->prepare("SHOW INDEX FROM `$table` WHERE Key_name = ?");
    if ($stmt) {
        $stmt->bind_param("s", $indexName);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            echo " -> Index '$indexName' already exists. Skipping.\n";
            $stmt->close();
            return;
        }
        $stmt->close();
    } else {
        // Fallback if statement preparation failed (e.g. table doesn't exist yet)
        echo " -> Warning: Could not check index existence (table might not exist: " . $conn->error . ")\n";
    }
    
    // Create the index
    echo " -> Creating index '$indexName' on ($columns)...\n";
    $sql = "ALTER TABLE `$table` ADD INDEX `$indexName` ($columns)";
    if ($conn->query($sql)) {
        echo " -> Success!\n";
    } else {
        echo " -> Error creating index: " . $conn->error . "\n";
    }
}

// 1. Upgrades for audit_logs
add_index_if_not_exists($conn, 'audit_logs', 'idx_audit_logs_user_id', '`user_id`');
add_index_if_not_exists($conn, 'audit_logs', 'idx_audit_logs_module_name', '`module_name`');
add_index_if_not_exists($conn, 'audit_logs', 'idx_audit_logs_record_id', '`record_id`');

// 2. Upgrades for document_access_logs
add_index_if_not_exists($conn, 'document_access_logs', 'idx_doc_access_user_id', '`user_id`');
add_index_if_not_exists($conn, 'document_access_logs', 'idx_doc_access_time', '`access_time`');

// 3. Upgrades for task_activity_logs
add_index_if_not_exists($conn, 'task_activity_logs', 'idx_task_activity_user_id', '`user_id`');
add_index_if_not_exists($conn, 'task_activity_logs', 'idx_task_activity_time', '`activity_time`');

// 4. Upgrades for core tables status & statistics queries
add_index_if_not_exists($conn, 'announcements', 'idx_announcements_status', '`status`');
add_index_if_not_exists($conn, 'meetings', 'idx_meetings_status', '`status`');
add_index_if_not_exists($conn, 'message_recipients', 'idx_msg_recipients_user_read', '`user_id`, `is_read`');
add_index_if_not_exists($conn, 'notifications', 'idx_notifications_recv_status', '`receiver_id`, `status`');

echo "Database index upgrades completed.\n";
?>
