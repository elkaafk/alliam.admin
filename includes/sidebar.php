<?php
/**
 * Боковое меню админ-панели
 */

// Получаем текущего администратора
$current_admin = get_current_admin();

// Определяем активную страницу
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Функция для форматирования ФИО в формате "Фамилия И.О."
function format_admin_name($full_name) {
    $parts = explode(' ', $full_name);

    if (count($parts) >= 3) {
        // Есть фамилия, имя, отчество
        $last_name = $parts[0];
        $first_name = mb_substr($parts[1], 0, 1, 'UTF-8') . '.';
        $middle_name = mb_substr($parts[2], 0, 1, 'UTF-8') . '.';

        return $last_name . ' ' . $first_name . $middle_name;
    } elseif (count($parts) == 2) {
        // Только фамилия и имя
        $last_name = $parts[0];
        $first_name = mb_substr($parts[1], 0, 1, 'UTF-8') . '.';

        return $last_name . ' ' . $first_name;
    } else {
        // Просто возвращаем как есть
        return $full_name;
    }
}

// Функция для получения инициалов для аватарки
function get_admin_initials($full_name) {
    $parts = explode(' ', $full_name);
    $initials = '';

    if (isset($parts[0])) {
        $initials .= mb_substr($parts[0], 0, 1, 'UTF-8');
    }

    if (isset($parts[1])) {
        $initials .= mb_substr($parts[1], 0, 1, 'UTF-8');
    }

    return mb_strtoupper($initials, 'UTF-8');
}

// Получаем инициалы и форматированное ФИО
$admin_initials = get_admin_initials($current_admin['full_name']);
$formatted_name = format_admin_name($current_admin['full_name']);

// Функция для определения активного пункта меню
function is_active($dir, $page = null) {
    global $current_dir, $current_page;

    if ($page) {
        return ($current_dir == $dir && $current_page == $page) ? 'active' : '';
    }

    return ($current_dir == $dir) ? 'active' : '';
}

// Функция для определения роли
function get_role_badge($role) {
    $badges = [
        'superadmin' => '<span class="badge bg-success">Администратор</span>',
        'lawyer' => '<span class="badge bg-primary">Юрист</span>',
        'manager' => '<span class="badge bg-primary">Менеджер</span>'
    ];

    return $badges[$role] ?? '';
}

// Получаем счетчики для сайдбара
$db = get_db_connection();

// Счетчик новых клиентов (сегодня)
$new_clients_stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()");
$new_clients_stmt->execute();
$new_clients_count = $new_clients_stmt->fetch()['count'];

// Счетчик непрочитанных сообщений
$unread_msg_stmt = $db->prepare("SELECT COUNT(DISTINCT client_id) as count FROM admin_messages WHERE is_read = 0 AND admin_id IS NULL");
$unread_msg_stmt->execute();
$unread_msg_count = $unread_msg_stmt->fetch()['count'];

// Счетчик ожидающих оплат (заглушка)
$pending_payments = 0;
?>
<!-- Непрокручиваемый сайдбар -->
<div class="sidebar-container">
    <div class="sidebar-fixed">
        <!-- Логотип и название -->
        <div class="sidebar-header">
            <a href="<?= BASE_URL ?>dashboard/" class="d-flex flex-column align-items-center text-white text-decoration-none text-center">

                    <img src="<?= BASE_URL ?>assets/img/logo.png"

                         class="sidebar-logo mb-2"
                         style="max-height: 90px;">

                <div class="text-center">
                    <div class="text-white-50" style="font-size: 0.8rem;">Панель управления</div>
                    <small class="d-block mt-1">
                        <?= get_role_badge($current_admin['role']) ?>
                    </small>

                </div>
            </a>
        </div>

        <!-- Основное меню -->
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>dashboard/"
                   class="nav-link <?= is_active('dashboard') ?> text-white"
                   aria-current="page">
                    <i class="bi bi-speedometer2"></i>
                    <span class="text-white">Дашборд</span>
                </a>
            </li>

            <li class="nav-item mb-2">
                <a <href="<?= BASE_URL ?>clients/"
                   class="nav-link <?= is_active('clients') ?> text-white">
                    <i class="bi bi-people"></i>
                    <span class="text-white" onclick="window.notify.info('Открыть раздел Клиенты', 5000)">Клиенты</span>
                    <?php if ($new_clients_count > 0): ?>
                        <span class="badge bg-warning ms-2"><?= $new_clients_count ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>chats/"
                   class="nav-link <?= is_active('chats') ?> text-white">
                    <i class="bi bi-chat-dots"></i>
                    <span class="text-white" onclick="window.notify.info('Открыть раздел Чаты', 5000)">Чаты</span>
                    <?php if ($unread_msg_count > 0): ?>
                        <span class="badge bg-success ms-2"><?= $unread_msg_count ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>payments/"
                   class="nav-link <?= is_active('payments') ?> text-white">
                    <i class="bi bi-credit-card"></i>
                    <span class="text-white">Оплаты</span>
                    <?php if ($pending_payments > 0): ?>
                        <span class="badge bg-info ms-2"><?= $pending_payments ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <?php if (has_permission(ROLE_SUPERADMIN)): ?>
                <li class="nav-item mb-2">
                    <a href="<?= BASE_URL ?>users/"
                       class="nav-link <?= is_active('users') ?> text-white">
                        <i class="bi bi-person-badge"></i>
                        <span class="text-white">Сотрудники</span>
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-item mb-4">
                <a href="<?= BASE_URL ?>settings/"
                   class="nav-link <?= is_active('settings') ?> text-white">
                    <i class="bi bi-gear"></i>
                    <span class="text-white">Настройки</span>
                </a>
            </li>
        </ul>

        <!-- Информация о пользователе -->
        <div class="user-info">
            <div class="d-flex align-items-center">

                <div class="flex-grow-1">
                    <small class="text-white-50 d-block mb-1" style="font-size: 0.75rem;">
                        Вы вошли как:
                    </small>
                    <div class="fw-semibold text-white text-truncate" style="max-width: 180px;">
                        <?= htmlspecialchars($formatted_name) ?>
                    </div>

                </div>
            </div>
        </div>

        <!-- Кнопка выхода -->
        <button class="btn btn-logout" onclick="window.location.href='<?= BASE_URL ?>logout.php'">
            <i class="bi bi-box-arrow-right me-2 text-white"></i>
            <span class="text-white">Выйти</span>
        </button>
    </div>
</div>

<!-- Мобильное меню -->
<div class="mobile-menu d-lg-none">
    <nav class="navbar navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="<?= BASE_URL ?>dashboard/">
                <?php if (file_exists(ROOT_PATH . '/assets/img/logo.png')): ?>
                    <img src="<?= BASE_URL ?>assets/img/logo.png"
                         alt="Alliam Admin"
                         height="30"
                         class="me-2">
                <?php endif; ?>
                <span class="text-white">Alliam Admin</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasNavbar">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title text-white">
                        <i class="bi bi-person-circle me-2"></i>
                        <?= htmlspecialchars(explode(' ', $current_admin['full_name'])[0]) ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body">
                    <div class="user-info-mobile mb-4 p-3 rounded bg-dark">
                        <div class="text-center">
                            <div class="avatar-circle mx-auto mb-2" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <?= $admin_initials ?>
                            </div>
                            <div class="fw-semibold text-white"><?= htmlspecialchars($formatted_name) ?></div>
                            <small class="mt-2 d-block"><?= get_role_badge($current_admin['role']) ?></small>
                        </div>
                    </div>

                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('dashboard') ?> text-white"
                               href="<?= BASE_URL ?>dashboard/">
                                <i class="bi bi-speedometer2 me-2"></i>Дашборд
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('clients') ?> text-white"
                               href="<?= BASE_URL ?>clients/">
                                <i class="bi bi-people me-2"></i>Клиенты
                                <?php if ($new_clients_count > 0): ?>
                                    <span class="badge bg-warning ms-2"><?= $new_clients_count ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('chats') ?> text-white"
                               href="<?= BASE_URL ?>chats/">
                                <i class="bi bi-chat-dots me-2"></i>Чаты
                                <?php if ($unread_msg_count > 0): ?>
                                    <span class="badge bg-success ms-2"><?= $unread_msg_count ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= is_active('payments') ?> text-white"
                               href="<?= BASE_URL ?>payments/">
                                <i class="bi bi-credit-card me-2"></i>Оплаты
                                <?php if ($pending_payments > 0): ?>
                                    <span class="badge bg-info ms-2"><?= $pending_payments ?></span>
                                <?php endif; ?>
                            </a>
                        </li>

                        <?php if (has_permission(ROLE_SUPERADMIN)): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= is_active('users') ?> text-white"
                                   href="<?= BASE_URL ?>users/">
                                    <i class="bi bi-person-badge me-2"></i>Сотрудники
                                </a>
                            </li>
                        <?php endif; ?>

                        <li class="nav-item">
                            <a class="nav-link <?= is_active('settings') ?> text-white"
                               href="<?= BASE_URL ?>settings/">
                                <i class="bi bi-gear me-2"></i>Настройки
                            </a>
                        </li>

                        <li class="nav-item mt-4 pt-3 border-top border-secondary">
                            <a href="<?= BASE_URL ?>logout.php" class="btn btn-outline-light w-100">
                                <i class="bi bi-box-arrow-right me-2"></i>Выйти
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</div>

<style>
    /* Дополнительные стили для мобильного меню */
    .user-info-mobile {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .offcanvas {
        width: 280px !important;
    }

    @media (max-width: 576px) {
        .offcanvas {
            width: 100% !important;
        }
    }

    /* Стили для сайдбара */
    .sidebar-fixed {
        background: linear-gradient(180deg, #111827 0%, #1f2937 100%);
    }

    /* Белый текст для всех элементов сайдбара */
    .sidebar-header span,
    .sidebar-header div,
    .nav-link,
    .nav-link span,
    .user-info .text-white,
    .user-info .text-white-50,
    .btn-logout span {
        color: white !important;
    }

    /* Активный пункт меню */
    .nav-link.active {
        background: rgba(255, 255, 255, 0.15) !important;
        border-left: 3px solid #0d6efd;
    }

    /* Иконки в сайдбаре */
    .nav-link i {
        color: rgba(255, 255, 255, 0.8) !important;
    }

    .nav-link:hover i,
    .nav-link.active i {
        color: white !important;
    }

    /* Стили для логотипа */
    .sidebar-logo {
        max-height: 60px;
        width: auto;
        object-fit: contain;
    }

    /* Светло-серый текст для подсказок */
    .text-white-50 {
        color: rgba(255, 255, 255, 0.5) !important;
    }

    /* Ховер эффекты */
    .nav-link:hover {
        background: rgba(255, 255, 255, 0.1) !important;
    }

    /* Кнопка выхода */
    .btn-logout {
        border-color: rgba(255, 255, 255, 0.3) !important;
    }

    .btn-logout:hover {
        background: rgba(220, 53, 69, 0.2) !important;
        border-color: rgba(220, 53, 69, 0.5) !important;
    }
</style>

<script>
    // Автоматическое закрытие мобильного меню при клике на ссылку
    document.addEventListener('DOMContentLoaded', function() {
        const mobileLinks = document.querySelectorAll('.mobile-menu .nav-link');
        mobileLinks.forEach(link => {
            link.addEventListener('click', function() {
                const offcanvas = document.getElementById('offcanvasNavbar');
                if (offcanvas) {
                    const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvas);
                    if (bsOffcanvas) {
                        bsOffcanvas.hide();
                    }
                }
            });
        });
    });
</script>