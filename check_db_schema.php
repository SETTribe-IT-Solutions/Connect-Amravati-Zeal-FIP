<?php
require_once 'include/dbConfig.php';
header('Content-Type: text/plain');

foreach (['confidential_documents', 'shared_messages', 'message_recipients', 'confidential_document_audience'] as $t) {
    echo "--- $t ---\n";
    $res = $conn->query("DESCRIBE $t");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            echo $r['Field'] . ' (' . $r['Type'] . ')\n';
        }
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}
?>
