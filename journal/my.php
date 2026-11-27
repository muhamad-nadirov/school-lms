<?php
session_start();

$student_id = 2;

require_once('db_local.php');
require_once('../layout/header.php');

$query = "SELECT full_name FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query) or die("Ошибка: " . mysqli_error($conn));
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$student) {
    die("Ученик не найден");
}

$attendance = [];
$query = "SELECT a.lesson_date, a.status, g.name AS group_name FROM attendance a JOIN `groups` g ON g.id = a.group_id WHERE a.student_id = ?
          ORDER BY a.lesson_date DESC";
$stmt = mysqli_prepare($conn, $query) or die("Ошибка: " . mysqli_error($conn));
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $attendance[] = $row;
}
mysqli_stmt_close($stmt);

$grades = [];
$query = "SELECT gr.lesson_date, gr.grade, gr.comment, g.name AS group_name FROM grades gr JOIN `groups` g ON g.id = gr.group_id WHERE gr.student_id = ?
          ORDER BY gr.lesson_date DESC";
$stmt = mysqli_prepare($conn, $query) or die("Ошибка: " . mysqli_error($conn));
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $grades[] = $row;
}
mysqli_stmt_close($stmt);

$present = 0;
foreach ($attendance as $a) {
    if ($a['status'] === 'present') {
        $present++;
    }
}
$percent = count($attendance) > 0 ? round(100 * $present / count($attendance)) : 0;

$sum = 0;
foreach ($grades as $g) {
    $sum += $g['grade'];
}
$average = count($grades) > 0 ? round($sum / count($grades), 1) : 0;
?>
<div class="page-header">
  <h1 class="page-header-title">Мой журнал</h1>
  <p><?php echo htmlspecialchars($student['full_name']); ?></p>
</div>

<div class="row mb-3">
  <div class="col-sm-6 mb-3">
    <div class="card">
      <div class="card-body">
        <h6 class="card-subtitle">Посещаемость</h6>
        <h2 class="card-title"><?php echo $percent; ?>%</h2>
      </div>
    </div>
  </div>
  <div class="col-sm-6 mb-3">
    <div class="card">
      <div class="card-body">
        <h6 class="card-subtitle">Средний балл</h6>
        <h2 class="card-title"><?php echo $average; ?></h2>
      </div>
    </div>
  </div>
</div>

<h3>Оценки</h3>
<div class="card mb-4">
  <table class="table table-align-middle card-table">
    <thead class="thead-light">
      <tr>
        <th>Дата</th>
        <th>Группа</th>
        <th>Оценка</th>
        <th>Комментарий</th>
      </tr>
    </thead>
<tbody>
  <?php if (count($grades) === 0) { ?>
    <tr><td colspan="4">Оценок пока нет</td></tr>
  <?php } ?>
    <?php foreach ($grades as $g) { ?>
      <tr>
        <td><?php echo date('d.m.Y', strtotime($g['lesson_date'])); ?></td>
        <td><?php echo htmlspecialchars($g['group_name']); ?></td>
        <td><?php echo $g['grade']; ?></td>
        <td><?php echo htmlspecialchars($g['comment'] ?? ''); ?></td>
        </tr>
    <?php } ?>
  </tbody>
</table>
</div>

<h3>Посещаемость</h3>
<div class="card">
  <table class="table table-align-middle card-table">
    <thead class="thead-light">
      <tr>
        <th>Дата</th>
        <th>Группа</th>
        <th>Статус</th>
      </tr>
    </thead>
<tbody>
  <?php if (count($attendance) === 0) { ?>
    <tr><td colspan="3">Отметок пока нет</td></tr>
    <?php } ?>
    <?php foreach ($attendance as $a) { ?>
      <tr>
        <td><?php echo date('d.m.Y', strtotime($a['lesson_date'])); ?></td>
        <td><?php echo htmlspecialchars($a['group_name']); ?></td>
        <td><?php echo $a['status'] === 'present' ? 'Был' : 'Не был'; ?></td>
      </tr>
    <?php } ?>
  </tbody>
</table>
</div>

<?php
require_once('../layout/footer.php');
?>