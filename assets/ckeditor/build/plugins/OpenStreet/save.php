<?php
$post_data = $_POST['key'];
    $filename ='./key.txt';
    $handle = fopen($filename, "w");
if (!empty($post_data)) {
    fwrite($handle,$post_data);
}
     fclose($handle);
     return 1;
?>
