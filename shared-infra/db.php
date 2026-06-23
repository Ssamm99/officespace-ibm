<?php
// /shared-infra/db.php
header("Access-Control-Allow-Origin: *");

$host = "127.0.0.1"; 
$user = "root";
$pass = "Mango10<";
$dbname = "officespace_db";
$port = 3306; 

$conn = mysqli_connect($host, $user, $pass, $dbname, $port);

if (!$conn) {
    die(json_encode(["status" => "error", "message" => "Error de conexión a la BD: " . mysqli_connect_error()]));
}
?>