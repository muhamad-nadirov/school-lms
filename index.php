<?php

require_once 'config.php';
require_once 'db.php';     
require_once 'auth.php';

require_login();            // правило безопасности №4

$user_id   = (int) ($_SESSION['user_id'] ?? 0);
$role      = $_SESSION['role'] ?? 'student';
$full_name = $_SESSION['full_name'] ?? '';

/* ---------- Помощники ---------- */


function fetch_value(mysqli $conn, string $sql, string $types = '', array $params = [])
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $row = $stmt->get_result()->fetch_row();
    $stmt->close();
    return $row[0] ?? 0;
}

/** Возвращает все строки запроса массивом. */
function fetch_rows(mysqli $conn, string $sql, string $types = '', array $params = []): array
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

/** Выводит одну карточку статистики. */
function stat_card(string $title, $value, string $icon, string $color = 'blue', string $link = ''): void
{
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $value = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    $icon  = htmlspecialchars($icon, ENT_QUOTES, 'UTF-8');
    $color = htmlspecialchars($color, ENT_QUOTES, 'UTF-8');
    ?>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-<?= $color ?> text-white avatar">
                            <i class="ti ti-<?= $icon ?>"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium fs-2"><?= $value ?></div>
                        <div class="text-secondary"><?= $title ?></div>
                    </div>
                </div>
            </div>
            <?php if ($link !== ''): ?>
                <a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>" class="stretched-link" aria-label="<?= $title ?>"></a>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/* ---------- Данные для карточек ---------- */
$cards = [];

if ($role === 'admin') {
    $cards = [
        ['Пользователей', fetch_value($conn, 'SELECT COUNT(*) FROM users'),   'users',    'blue',   'users/index.php'],
        ['Групп',         fetch_value($conn, 'SELECT COUNT(*) FROM `groups`'), 'category', 'green',  'groups/index.php'],
        ['Курсов',        fetch_value($conn, 'SELECT COUNT(*) FROM courses'),  'book',     'orange', 'courses/index.php'],
        ['Тестов',        fetch_value($conn, 'SELECT COUNT(*) FROM tests'),    'checklist', 'purple', 'tests/index.php'],
    ];

} elseif ($role === 'teacher') {
    $my_groups = fetch_value(
        $conn,
        'SELECT COUNT(*) FROM `groups` WHERE teacher_id = ?',
        'i', [$user_id]
    );
    $my_students = fetch_value(
        $conn,
        'SELECT COUNT(DISTINCT gs.student_id)
           FROM group_students gs
           JOIN `groups` g ON g.id = gs.group_id
          WHERE g.teacher_id = ?',
        'i', [$user_id]
    );
    $to_check = fetch_value(
        $conn,
        'SELECT COUNT(*)
           FROM submissions s
           JOIN assignments a ON a.id = s.assignment_id
           JOIN `groups` g    ON g.id = a.group_id
          WHERE g.teacher_id = ? AND s.score IS NULL',
        'i', [$user_id]
    );
    $deadlines = fetch_value(
        $conn,
        'SELECT COUNT(*)
           FROM assignments a
           JOIN `groups` g ON g.id = a.group_id
          WHERE g.teacher_id = ? AND a.deadline >= CURDATE()',
        'i', [$user_id]
    );

    $cards = [
        ['Мои группы',            $my_groups,   'category',   'blue',   'groups/index.php'],
        ['Мои ученики',           $my_students, 'users',      'green',  'groups/index.php'],
        ['Ждут проверки',         $to_check,    'file-check', 'red',    'assignments/index.php'],
        ['Активные дедлайны',     $deadlines,   'calendar-time', 'orange', 'assignments/index.php'],
    ];

} else { // student
    $lessons_done = fetch_value(
        $conn,
        'SELECT COUNT(*) FROM lesson_done WHERE student_id = ?',
        'i', [$user_id]
    );
    $active_tasks = fetch_value(
        $conn,
        'SELECT COUNT(*)
           FROM assignments a
           JOIN group_students gs ON gs.group_id = a.group_id
          WHERE gs.student_id = ?
            AND a.deadline >= CURDATE()
            AND NOT EXISTS (
                SELECT 1 FROM submissions s
                 WHERE s.assignment_id = a.id AND s.student_id = ?
            )',
        'ii', [$user_id, $user_id]
    );
    $avg_grade = fetch_value(
        $conn,
        'SELECT ROUND(AVG(value), 1) FROM grades WHERE student_id = ?',
        'i', [$user_id]
    );
    $attendance = fetch_value(
        $conn,
        'SELECT ROUND(100 * SUM(present) / COUNT(*))
           FROM attendance WHERE student_id = ?',
        'i', [$user_id]
    );

    $cards = [
        ['Пройдено уроков',  $lessons_done,                         'book',          'blue',   'courses/index.php'],
        ['Активные задания', $active_tasks,                         'clipboard-list', 'orange', 'assignments/index.php'],
        ['Средняя оценка',   $avg_grade !== null ? $avg_grade : '—', 'star',          'green',  'journal/index.php'],
        ['Посещаемость',     $attendance !== null ? $attendance . '%' : '—', 'calendar-check', 'purple', 'journal/index.php'],
    ];
}

/* ---------- Последние объявления ---------- */
if ($role === 'admin') {
    $news = fetch_rows(
        $conn,
        'SELECT n.title, n.text, n.created_at, u.full_name AS author
           FROM announcements n
           JOIN users u ON u.id = n.author_id
          ORDER BY n.created_at DESC
          LIMIT 5'
    );
} elseif ($role === 'teacher') {
    $news = fetch_rows(
        $conn,
        'SELECT n.title, n.text, n.created_at, u.full_name AS author
           FROM announcements n
           JOIN users u    ON u.id = n.author_id
           JOIN `groups` g ON g.id = n.group_id
          WHERE g.teacher_id = ?
          ORDER BY n.created_at DESC
          LIMIT 5',
        'i', [$user_id]
    );
} else {
    $news = fetch_rows(
        $conn,
        'SELECT n.title, n.text, n.created_at, u.full_name AS author
           FROM announcements n
           JOIN users u ON u.id = n.author_id
           JOIN group_students gs ON gs.group_id = n.group_id
          WHERE gs.student_id = ?
          ORDER BY n.created_at DESC
          LIMIT 5',
        'i', [$user_id]
    );
}

$role_names = ['admin' => 'Администратор', 'teacher' => 'Преподаватель', 'student' => 'Ученик'];
$page_title = 'Главная';

include 'layout/header.php';
include 'layout/sidebar.php';
?>

<div class="page-wrapper">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        <?= htmlspecialchars($role_names[$role] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <h2 class="page-title">
                        Добро пожаловать<?= $full_name !== '' ? ', ' . htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8') : '' ?>!
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">

            <!-- Карточки статистики -->
            <div class="row row-deck row-cards mb-3">
                <?php foreach ($cards as $c): ?>
                    <?php stat_card($c[0], $c[1], $c[2], $c[3], $c[4] ?? ''); ?>
                <?php endforeach; ?>
            </div>

            <!-- Последние объявления -->
            <div class="row row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Последние объявления</h3>
                        </div>
                        <?php if (empty($news)): ?>
                            <div class="card-body text-secondary">
                                Объявлений пока нет.
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($news as $n): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <strong><?= htmlspecialchars($n['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <span class="text-secondary">
                                                <?= htmlspecialchars(date('d.m.Y', strtotime($n['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </div>
                                        <div class="text-secondary mt-1">
                                            <?= htmlspecialchars(mb_strimwidth($n['text'], 0, 160, '…', 'UTF-8'), ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                        <div class="text-secondary small mt-1">
                                            <?= htmlspecialchars($n['author'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php include 'layout/footer.php'; ?>
</div>