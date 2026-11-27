
<?php

require_once('../db.php');
session_start();

if (
    isset($_POST['email'], $_POST['password']) &&
    !empty($_POST['email']) &&
    !empty($_POST['password'])
) {

    $email = $_POST['email'];
    $password = $_POST['password'];
    $query = "SELECT * FROM users WHERE email = ?";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if ($row) {

        if (password_verify($password, $row['password_hash'])) {

            session_regenerate_id(true);

            $_SESSION['email'] = $row['email'];
            $_SESSION['uid'] = $row['id'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['role'] = $row['role'];

            header("Location: auth.php");
            exit();

        } else {
            echo "Invalid password.";
        }
    } else {
        echo "User not found.";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Please fill in all fields.";
}
?>