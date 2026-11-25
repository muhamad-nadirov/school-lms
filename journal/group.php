<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['role']    = 'teacher';
}

require_once('db_local.php');

require_once('../layout/header.php');

$query = "SELECT id, name FROM `groups` ORDER BY name";
$groups = mysqli_query($conn, $query) or die("Ошибка: " . mysqli_error($conn));

$group_id = isset($_GET['group_id']) ? (int) $_GET['group_id'] : 0;

$lesson_date = $_GET['lesson_date'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $lesson_date)) {
    $lesson_date = date('Y-m-d');
}

$students = [];
$marks = [];
$grades = [];

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

    $query = "SELECT student_id, status FROM attendance WHERE group_id = ? AND lesson_date = ?";
    $stmt = mysqli_prepare($conn, $query) or die("Ошибка: " . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt, "is", $group_id, $lesson_date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $marks[$row['student_id']] = $row['status'];
    }
    mysqli_stmt_close($stmt);

    $query = "SELECT student_id, grade, comment FROM grades WHERE group_id = ? AND lesson_date = ?";
    $stmt = mysqli_prepare($conn, $query) or die("Ошибка: " . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt, "is", $group_id, $lesson_date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $grades[$row['student_id']] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>
<div class="page-header">
  <h1 class="page-header-title">Журнал</h1>
</div>

<div class="card mb-3">
  <div class="card-body">
    <form action="group.php" method="get">
      <div class="row">
        <div class="col-5">
          <label class="form-label">Группа</label>
          <select class="form-select" name="group_id" required>
            <option value="">Выберите группу</option>
            <?php
            while ($row = mysqli_fetch_assoc($groups)) {
            ?>
              <option value="<?php echo $row['id']; ?>" <?php echo $row['id'] == $group_id ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($row['name']); ?>
              </option>
            <?php
            }
            ?>
          </select>
        </div>

        <div class="col-5">
          <label class="form-label">Дата</label>
          <input type="date" class="form-control" name="lesson_date" value="<?php echo htmlspecialchars($lesson_date); ?>" required>
        </div>

        <div class="col-2 d-flex align-items-end">
          <input type="submit" class="btn btn-primary" value="Показать">
        </div>
      </div>
    </form>
  </div>
</div>

<?php if ($group_id > 0) { ?>

  <form action="attendance_save.php" method="post">
    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
    <input type="hidden" name="lesson_date" value="<?php echo htmlspecialchars($lesson_date); ?>">

    <div class="card mb-3">
      <table class="table table-align-middle card-table">
        <thead class="thead-light">
          <tr>
            <th>#</th>
            <th>ФИО</th>
            <th>Посещаемость</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($students) === 0) { ?>
            <tr><td colspan="3">В этой группе нет учеников</td></tr>
          <?php } ?>

          <?php foreach ($students as $i => $student) {
            $current = $marks[$student['id']] ?? 'present';
          ?>
            <tr>
              <td><?php echo $i + 1; ?></td>
              <td><?php echo htmlspecialchars($student['full_name']); ?></td>
              <td>
                <label class="me-3">
                  <input type="radio" name="status[<?php echo $student['id']; ?>]" value="present" <?php echo $current === 'present' ? 'checked' : ''; ?>> Был
                </label>
                <label>
                  <input type="radio" name="status[<?php echo $student['id']; ?>]" value="absent" <?php echo $current === 'absent' ? 'checked' : ''; ?>> Не был
                </label>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>

    <?php if (count($students) > 0) { ?>
      <input type="submit" class="btn btn-primary" value="Сохранить посещаемость">
    <?php } ?>
  </form>

  <form action="grades_save.php" method="post" class="mt-4">
    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
    <input type="hidden" name="lesson_date" value="<?php echo htmlspecialchars($lesson_date); ?>">

    <div class="card mb-3">
      <table class="table table-align-middle card-table">
        <thead class="thead-light">
          <tr>
            <th>#</th>
            <th>ФИО</th>
            <th>Оценка</th>
            <th>Комментарий</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $i => $student) {
            $current_grade   = $grades[$student['id']]['grade'] ?? '';
            $current_comment = $grades[$student['id']]['comment'] ?? '';
          ?>
            <tr>
              <td><?php echo $i + 1; ?></td>
              <td><?php echo htmlspecialchars($student['full_name']); ?></td>
              <td>
                <select class="form-select" name="grade[<?php echo $student['id']; ?>]">
                  <option value="">—</option>
                  <?php for ($mark = 1; $mark <= 10; $mark++) { ?>
                    <option value="<?php echo $mark; ?>" <?php echo $current_grade == $mark ? 'selected' : ''; ?>><?php echo $mark; ?></option>
                  <?php } ?>
                </select>
              </td>
              <td>
                <input type="text" class="form-control" maxlength="255"
                       name="comment[<?php echo $student['id']; ?>]"
                       value="<?php echo htmlspecialchars($current_comment); ?>">
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>

    <?php if (count($students) > 0) { ?>
      <input type="submit" class="btn btn-primary" value="Сохранить оценки">
    <?php } ?>
  </form>

<?php } ?>

<?php
require_once('../layout/footer.php');
?>