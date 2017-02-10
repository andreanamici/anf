<?php

/**
 * Format bytes to kilobytes, megabytes, gigabytes
 * 
 * @param int $bytes Number of bytes (eg. 25907)
 * @param int $precision [optional] Number of digits after the decimal point (eg. 1)
 * 
 * @return string Value converted with unit (eg. 25.3KB)
 * 
 * @link http://stackoverflow.com/questions/2510434/format-bytes-to-kilobytes-megabytes-gigabytes
 */
function formatBytes($bytes, $precision = 2) 
{
    $unit = ["B", "KB", "MB", "GB"];
    $exp = floor(log($bytes, 1024)) | 0;
    return round($bytes / (pow(1024, $exp)), $precision).$unit[$exp];
}