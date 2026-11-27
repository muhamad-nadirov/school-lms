<?php

require_once('../db.php');

session_start();
if(isset($_SESSION['uid']) && !empty($_SESSION['uid']) &&
    isset($_SESSION['email']) && !empty($_SESSION['email']) &&
    isset($_SESSION['role']) && !empty($_SESSION['role'])){
        
        $role = $_SESSION['role'];

        if($role=='admin'){
            header('Location:../users/admin/');
            exit();
        }else if($role=='teacher'){
            header('Location:../users/teachers/');
            exit();
        }else if($role=='student'){
            header('Location:../users/users.php');
            exit();
        }
}else{
    header('Location:login.php');
    exit();
}