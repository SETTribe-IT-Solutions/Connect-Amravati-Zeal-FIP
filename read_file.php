<?php
if (isset($_GET['file'])) {
    echo file_get_contents($_GET['file']);
}
?>
