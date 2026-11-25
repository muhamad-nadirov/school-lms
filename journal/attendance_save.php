<?php

session_start();

require_once('db_local.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;$_SESSION['role'] = 'teacher';
}
if (!in_array($_SESSION['role'], ['teacher', 'admin'])) {
    die("Нет доступа");
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Ошибка: данные не получены");
}

$group_id = (int) $_POST['group_id'];
$lesson_date = $_POST['lesson_date'];
$statuses = $_POST['status'] ?? [];
$marked_by = (int) $_SESSION['user_id'];

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $lesson_date)) {
    die("Ошибка: неверная дата");
}

$allowed = [];
$query = "SELECT student_id FROM group_students WHERE group_id = ?";
$stmt = mysqli_prepare($conn, $query) or die("Ошибка: " . mysqli_error($conn));
mysqli_stmt_bind_param($stmt, "i", $group_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $allowed[] = (int) $row['student_id'];
}
mysqli_stmt_close($stmt);

$query = "INSERT INTO attendance (group_id, student_id, lesson_date, status, marked_by) VALUES (?, ?, ?, ?, ?)
          ON DUPLICATE KEY UPDATE status = VALUES(status), marked_by = VALUES(marked_by)";
$stmt = mysqli_prepare($conn, $query) or die("Ошибка: " . mysqli_error($conn));

foreach ($statuses as $student_id => $status) {
    $student_id = (int) $student_id;

    if (!in_array($student_id, $allowed) || !in_array($status, ['present', 'absent'])) {
        continue;
    }

    mysqli_stmt_bind_param($stmt, "iissi", $group_id, $student_id, $lesson_date, $status, $marked_by);
    mysqli_stmt_execute($stmt) or die("Ошибка: " . mysqli_stmt_error($stmt));
}
mysqli_stmt_close($stmt);

header("Location: group.php?group_id=$group_id&lesson_date=$lesson_date");
exit();