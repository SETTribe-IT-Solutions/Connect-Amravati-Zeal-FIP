<?php\
$dir = "C:/xampp/mailoutput";\
if (is_dir($dir)) {\
    $files = scandir($dir);\
    echo "Files in mailoutput:\\n";\
    foreach ($files as $file) {\
        if ($file !== '.' && $file !== '..') {\
            echo "=== $file ===\\n";\
            echo file_get_contents("$dir/$file") . "\\n";\
        }\
    }\
} else {\
    echo "Directory $dir does not exist\\n";\
}\
?>
