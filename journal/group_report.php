<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'teacher';
}

require_once('db_local.php');
require_once('../layout/header.php');

$query = "SELECT id, name FROM `groups` ORDER BY name";
$groups = mysqli_query($conn, $query) or die("Ошибка: " . mysqli_error($conn));

$group_id = isset($_GET['group_id']) ? (int) $_GET['group_id'] : 0;

$students = [];
$dates = [];
$records = [];

if ($group_id > 0) {
    $query = "SELECT u.id, u.full_name FROM group_students gs JOIN users u ON u.id = gs.student_id WHERE gs.group_id = ?
              ORDER BY u.full_name";
    $stmt = mysqli_prepare($conn, $query) or die("Ошибка: " . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt, "i", $group_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $students[] = $row;
    }
    mysqli_stmt_close($stmt);

    $query = "SELECT DISTINCT lesson_date FROM attendance WHERE group_id = ? UNION SELECT DISTINCT lesson_date FROM grades WHERE group_id = ?
              ORDER BY lesson_date ASC";
    $stmt = mysqli_prepare($conn, $query) or die("Ошибка: " . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt, "ii", $group_id, $group_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $dates[] = $row['lesson_date'];
    }
    mysqli_stmt_close($stmt);

    $query = "SELECT student_id, lesson_date, status, NULL AS grade FROM attendance WHERE group_id = ? UNION ALL
              SELECT student_id, lesson_date, NULL AS status, grade
              FROM grades
              WHERE group_id = ?";
    $stmt = mysqli_prepare($conn, $query) or die("Ошибка: " . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt, "ii", $group_id, $group_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $s_id = $row['student_id'];
        $date = $row['lesson_date'];
        if ($row['status'] !== null) {
            $records[$s_id][$date]['status'] = $row['status'];
        }
        if ($row['grade'] !== null) {
            $records[$s_id][$date]['grade'] = $row['grade'];
        }
    }
    mysqli_stmt_close($stmt);
}
?>

<div>
    <h1>Сводный журнал группы</h1>
</div>

<div>
    <form action="group_report.php" method="get">
        <div>
            <label>Группа</label>
            <select name="group_id" onchange="this.form.submit()" required>
                <option value="">Выберите группу</option>
                <?php while ($row = mysqli_fetch_assoc($groups)) { ?>
                    <option value="<?php echo $row['id']; ?>"
                        <?php echo $row['id'] == $group_id ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['name']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div>
            <button type="submit">Показать</button>
        </div>
    </form>
</div>

<?php if ($group_id > 0) { ?>

<div>
    <table>
        <thead>
            <tr>
                <th>№</th>
                <th>ФИО ученика</th>
                <?php if (count($dates) === 0) { ?>
                    <th>Дата занятий</th>
                <?php } else { ?>
                    <?php foreach ($dates as $date) { ?>
                        <th><?php echo date('d.m.Y', strtotime($date)); ?></th>
                    <?php } ?>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php if (count($students) === 0) { ?>
                <tr>
                    <td colspan="<?php echo 2 + max(1, count($dates)); ?>">В группе нет учеников</td>
                </tr>
            <?php } else { ?>
                <?php foreach ($students as $i => $student) { ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td>
                            <?php echo htmlspecialchars($student['full_name']); ?>
                        </td>
                        <?php if (count($dates) === 0) { ?>
                            <td>—</td>
                        <?php } else { ?>
                            <?php foreach ($dates as $date) { 
                                $cell = $records[$student['id']][$date] ?? [];
                                $status = $cell['status'] ?? null;
                                $grade = $cell['grade'] ?? null;
                            ?>
                                <td>
                                    <?php if ($status !== null) { ?>
                                        <span>
                                            <?php echo $status === 'present' ? 'Был' : 'Не был'; ?>
                                        </span>
                                    <?php } else { ?>
                                        <span>—</span>
                                    <?php } ?>
                                    <br>
                                    <strong><?php echo $grade !== null ? $grade : '—'; ?></strong>
                                </td>
                            <?php } ?>
                        <?php } ?>
                    </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php } ?>

<?php
require_once('../layout/footer.php');
?>