<?php

session_start();
require_once('db_local.php');

$student_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 2;

$history = [];
$query = "SELECT gr.lesson_date, gr.grade, gr.comment, g.name AS group_name FROM grades gr JOIN `groups` g ON g.id = gr.group_id
          WHERE gr.student_id = ? ORDER BY gr.lesson_date DESC";
$stmt = mysqli_prepare($conn, $query) or die("Ошибка: " . mysqli_error($conn));
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $history[] = $row;
}
mysqli_stmt_close($stmt);
?>

<div>
    <h2>История оценок</h2>
    <table>
      <thead>
        <tr>
          <th>Дата</th>
          <th>Группа</th>
          <th>Оценка</th>
          <th>Комментарий</th>
        </tr>
      </thead>
        <tbody>
        <?php if (count($history) === 0) { ?>
            <tr>
              <td colspan="4">История пуста</td>
            </tr>
        <?php } else { ?>
            <?php foreach ($history as $row) { ?>
                <tr>
                  <td><?php echo date('d.m.Y', strtotime($row['lesson_date'])); ?></td>
                  <td><?php echo htmlspecialchars($row['group_name']); ?></td>
                  <td><strong><?php echo (int) $row['grade']; ?></strong></td>
                  <td><?php echo htmlspecialchars($row['comment'] ?? '—'); ?></td>
                </tr>
            <?php } ?>
            <?php } ?>
        </tbody>
    </table>
</div>