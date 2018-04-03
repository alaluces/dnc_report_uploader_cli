<?php
// note: use new password hash for mysql/pdo
$host   = '192.168.1.5';
$dbname = 'asterisk';
$user   = 'msi';
$pass   = '1234';
$DBH    = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass); 

?>
