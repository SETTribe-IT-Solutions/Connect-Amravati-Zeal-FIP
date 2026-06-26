<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/Connect-Amravati-Zeal-FIP/task_tracking.php?ajax=timeline&task_id=1");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
echo "RESPONSE:\n" . $response;
?>
