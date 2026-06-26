<?php
$output = shell_exec('git checkout -- settings.php 2>&1');
echo "Restored settings.php. Output: " . $output;
?>
