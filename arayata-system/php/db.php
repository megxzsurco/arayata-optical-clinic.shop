<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'arayata_db';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("<div style='color:red; font-family:sans-serif; padding:20px;'>Database Connection Failed: " . $conn->connect_error . "</div>");
}
$conn->set_charset("utf8");
?>