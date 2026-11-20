<?php
// Получаем роль пользователя из сессии
$role = $_SESSION['role'] ?? 'student';

// Текущая страница
$current_page = basename($_SERVER['PHP_SELF']);

// Функция активного пункта меню
function activeMenu($page, $current_page) {
    return $page === $current_page ? 'active' : '';
}
?>

<aside class="navbar navbar-vertical navbar-expand-lg navbar-dark">
    <div class="container-fluid">

        <!-- Название проекта -->
        <h1 class="navbar-brand navbar-brand-autodark">
            <a href="/lms/index.php" style="color: white; text-decoration: none;">
                LMS SYSTEM
            </a>
        </h1>

        <!-- Меню -->
        <div class="navbar-nav flex-column">

            <!-- Главная -->
            <div class="nav-item">
                <a class="nav-link <?= activeMenu('index.php', $current_page) ?>"
                   href="/lms/index.php">
                    <span class="nav-link-icon">
                        <i class="ti ti-home"></i>
                    </span>
                    <span class="nav-link-title">Главная</span>
                </a>
            </div>

            <?php if ($role === 'admin'): ?>

                <div class="nav-item">
                    <a class="nav-link" href="/lms/users/list.php">
                        <span class="nav-link-icon"><i class="ti ti-users"></i></span>
                        <span class="nav-link-title">Пользователи</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a class="nav-link" href="/lms/groups/list.php">
                        <span class="nav-link-icon"><i class="ti ti-users-group"></i></span>
                        <span class="nav-link-title">Группы</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a class="nav-link" href="/lms/courses/list.php">
                        <span class="nav-link-icon"><i class="ti ti-book"></i></span>
                        <span class="nav-link-title">Курсы</span>
                    </a>
                </div>

            <?php elseif ($role === 'teacher'): ?>

                <div class="nav-item">
                    <a class="nav-link" href="/lms/courses/list.php">
                        <span class="nav-link-icon"><i class="ti ti-book"></i></span>
                        <span class="nav-link-title">Мои курсы</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a class="nav-link" href="/lms/assignments/list.php">
                        <span class="nav-link-icon"><i class="ti ti-file-text"></i></span>
                        <span class="nav-link-title">Задания</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a class="nav-link" href="/lms/tests/list.php">
                        <span class="nav-link-icon"><i class="ti ti-clipboard-check"></i></span>
                        <span class="nav-link-title">Тесты</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a class="nav-link" href="/lms/journal/group.php">
                        <span class="nav-link-icon"><i class="ti ti-notebook"></i></span>
                        <span class="nav-link-title">Журнал</span>
                    </a>
                </div>

            <?php elseif ($role === 'student'): ?>

                <div class="nav-item">
                    <a class="nav-link" href="/lms/courses/list.php">
                        <span class="nav-link-icon"><i class="ti ti-book"></i></span>
                        <span class="nav-link-title">Мои курсы</span>
                    </a>
                </div>

                <div class="nav-item">
<a class="nav-link" href="/lms/assignments/list.php">
                        <span class="nav-link-icon"><i class="ti ti-file-text"></i></span>
                        <span class="nav-link-title">Мои задания</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a class="nav-link" href="/lms/tests/list.php">
                        <span class="nav-link-icon"><i class="ti ti-clipboard-check"></i></span>
                        <span class="nav-link-title">Тесты</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a class="nav-link" href="/lms/journal/my.php">
                        <span class="nav-link-icon"><i class="ti ti-report"></i></span>
                        <span class="nav-link-title">Мои оценки</span>
                    </a>
                </div>

            <?php endif; ?>

            <!-- Общие пункты -->
            <div class="nav-item">
                <a class="nav-link" href="/lms/news/announcements.php">
                    <span class="nav-link-icon"><i class="ti ti-speakerphone"></i></span>
                    <span class="nav-link-title">Объявления</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link" href="/lms/profile.php">
                    <span class="nav-link-icon"><i class="ti ti-user"></i></span>
                    <span class="nav-link-title">Профиль</span>
                </a>
            </div>

            <div class="nav-item">
                <a class="nav-link" href="/lms/logout.php">
                    <span class="nav-link-icon"><i class="ti ti-logout"></i></span>
                    <span class="nav-link-title">Выйти</span>
                </a>
            </div>

        </div>
    </div>
</aside>
