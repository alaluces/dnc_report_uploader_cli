#!/usr/bin/env php
<?php
/*
Vicidial DNC uploader using dropbox API
20141208 - 1st
20141209 - Cleanup and log_to_db()
*/

ini_set('date.timezone', 'America/Los_Angeles');

require_once __DIR__."/db.php";
require_once __DIR__."/dropbox-sdk/Dropbox/autoload.php";

use \Dropbox as dbx;

// access token from dropbox (as XXXXXXXXX@gmail.com)
$accessToken = 'YOUR_ACCESS_TOKEN';
$dbxClient = new dbx\Client($accessToken, "PHP-Example/1.0");

// Log to DB function
function log_to_db($DBH, $path, $size) {
    $date_time = date("Y-m-d H:i:s");

    $STH = $DBH->prepare("INSERT INTO `_dropbox_log` VALUES(:date_time, :path, :size)");      
    $STH->bindParam(':date_time', $date_time);
    $STH->bindParam(':path', $path);
    $STH->bindParam(':size', $size);
    $STH->execute();                 
}

// Function that pulls the dnc numbers and write to a text file
function write_to_file($DBH, $campaign_id, $dnc_type, $file_name) {
    $date_now = date("Y-m-d");
    
    $src = __DIR__."/reports/$file_name";
    $w_file = fopen($src,"w");
    $STH = $DBH->query("SELECT phone_number FROM `vicidial_agent_log` AS al
                    INNER JOIN vicidial_list vl
                    ON al.lead_id = vl.lead_id
                    WHERE al.campaign_id = '$campaign_id' AND LEFT(event_time,10) = '$date_now'
                    AND al.status = '$dnc_type'");      
    while($row = $STH->fetch()) {
       //echo "[$row[0]]";
       fputs($w_file,$row[0] . "\r\n");     
    }     
    fclose($w_file); 
    // delete the file if it has no content
    if (filesize($src) <= 0) { exec("rm -f $src"); }               
}

$file_date = date("Ymd");

// Set the destination paths
$dst = array();
$dst[1]  = '/MSI DO NOT CALL LIST/GREAT DESTINATION ONTARIO AND ORANGE/Upload Folder/TEAM LLC/DNC';
$dst[2]  = '/MSI DO NOT CALL LIST/GREAT DESTINATION ONTARIO AND ORANGE/Upload Folder/TEAM LLC/TOL';
$dst[3]  = '/MSI DO NOT CALL LIST/great destination ontario and orange/upload folder/TEAM INFIN8/DNC';
$dst[4]  = '/MSI DO NOT CALL LIST/great destination ontario and orange/upload folder/TEAM INFIN8/TOL';
$dst[5]  = '/MSI GEVC DO NOT CALL/Upload Folder/Team Infinite8/DNC';
$dst[6]  = '/MSI GEVC DO NOT CALL/Upload Folder/Team Infinite8/TOL';
$dst[7]  = '/MSI GEVC DO NOT CALL/upload folder/team convergence/upload/DNC';
$dst[8]  = '/MSI GEVC DO NOT CALL/upload folder/team convergence/upload/TOL';
$dst[9]  = '/MSI GEVC DO NOT CALL/upload folder/team convergence/upload/DNC';
$dst[10] = '/MSI GEVC DO NOT CALL/upload folder/team convergence/upload/TOL';

// Set the filenames
$file_names = array();
$file_names[1]  = $file_date.'_GD_LLCC_DNC.txt';
$file_names[2]  = $file_date.'_GD_LLCC_TOL.txt';
$file_names[3]  = $file_date.'_GD_INFIN8_DNC.txt';
$file_names[4]  = $file_date.'_GD_INFIN8_TOL.txt';
$file_names[5]  = $file_date.'_GEVC_INFIN8_DNC.txt';
$file_names[6]  = $file_date.'_GEVC_INFIN8_TOL.txt';
$file_names[7]  = $file_date.'_GD_CONVERGENCE_DNC.txt';
$file_names[8]  = $file_date.'_GD_CONVERGENCE_TOL.txt';
$file_names[9]  = $file_date.'_GEVC_CONVERGENCE_DNC.txt';
$file_names[10] = $file_date.'_GEVC_CONVERGENCE_TOL.txt';

// call write_to_file() function per filename
write_to_file($DBH, '20056', 'DNC', "$file_names[1]");
write_to_file($DBH, '20056', 'TOL', "$file_names[2]");
write_to_file($DBH, '30001', 'DNC', "$file_names[3]");
write_to_file($DBH, '30001', 'TOL', "$file_names[4]");
write_to_file($DBH, '30002', 'DNC', "$file_names[5]");
write_to_file($DBH, '30002', 'TOL', "$file_names[6]");
write_to_file($DBH, '40001', 'DNC', "$file_names[7]");
write_to_file($DBH, '40001', 'TOL', "$file_names[8]");
write_to_file($DBH, '40003', 'DNC', "$file_names[9]");
write_to_file($DBH, '40003', 'TOL', "$file_names[10]");

// UPLOAD ALL THE FILES!
for ($i=1; $i<=10; $i++) {    
   
    $src = "reports/$file_names[$i]";
    if (file_exists($src) && filesize($src) > 0) {    
        //echo 'Size: ' . filesize($src) . ' Filename: '. $src . "\n";
        $f = fopen("$src", "rb");
        $result = $dbxClient->uploadFile("$dst[$i]/$file_names[$i]", dbx\WriteMode::add(), $f);
        fclose($f);
        //print_r($result);
        echo 'Uploaded to: ' . $result['path'] . ' Size: ' . $result['size'] . "\n";
        log_to_db($DBH, $result['path'], $result['size']);
        unset($result);
    }
}
