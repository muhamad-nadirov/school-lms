<?php

require_once (__DIR__ . 'config.php');
$conn = mysqli_conect($host, $user, $password, $db_name);

if(!$conn) {
    die("Ошибка: " . mysqli_conect_error());
}

mysqli_set_charset($conn, "utf8mb4"); //это что б кириллица работала как надо

?>