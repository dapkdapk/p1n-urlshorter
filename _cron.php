<?php
/**
 * dapkdapk
 * this cron script delets all data urls older than 24 hours
 */
$dir = getcwd() . "/data/"; //dir absolute path
$interval = strtotime('-24 hours'); //files older than 24hours

foreach (glob($dir . "*") as $file) {
    //delete if older
    if (filemtime($file) <= $interval) {
        if (is_dir($file)) {
            deleteDirectory($file);
        }
    }
}

function deleteDirectory($dirPath)
{
    if (is_dir($dirPath)) {
        $objects = scandir($dirPath);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
                    deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
                } else {
                    unlink($dirPath . DIRECTORY_SEPARATOR . $object);
                }
            }
        }
        reset($objects);
        rmdir($dirPath);
    }
}
?>