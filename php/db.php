<?php
$host = "sql313.infinityfree.com";
$user = "if0_41723941";
$pass = "soundvibe123";
$db = "if0_41723941_db_soundvibe";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>