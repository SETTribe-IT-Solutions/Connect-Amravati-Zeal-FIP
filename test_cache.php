<?php
echo "Disk Hash: " . md5_file('task_tracking.php') . "\n";
echo "File line 179: " . trim(explode("\n", file_get_contents('task_tracking.php'))[178]) . "\n";

try {
    require 'task_tracking.php'; // this might throw the same warning or execute code!
} catch (Exception $e) {}
?>
