<?php
$files = scandir('3rd/icons');
$n = [];
foreach($files as $file) {
    if($file == '.' || $file == '..') continue;
    $n[] = $file;
}
$icon = $n[array_rand($n)];
header('Content-Type: image/svg+xml');
readfile("3rd/icons/".$icon);
?>