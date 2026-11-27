<?php

session_start();
require_once("../db.php");

print_r($_POST);
$full_name = $_POST["full_name"];
$email = $_POST["email"];
$password = $_POST["password"];
$role = "student";
$class_id = $_POST["class_id"];


$password_hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users 
        (full_name, email, password_hash, role, class_id)
        VALUES 
        ('$full_name', '$email', '$password_hash', '$role', '$class_id')";

if (mysqli_query($conn, $sql)) {

    echo "Регистрация прошла успешно!";
    session_regenerate_id(true);

    $_SESSION['email'] = $email;
    $_SESSION['uid'] = mysqli_insert_id($conn);
    $_SESSION['full_name'] = $full_name;
    $_SESSION['role'] = $role;

} else {

    echo "Ошибка: " . mysqli_error($conn);

}
?>