<?php
/**
 * Боковое меню админ-панели
 */
$current_admin = get_current_admin();
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<!-- Sidebar -->
<nav class="sidebar bg-dark text-white vh-100 position-fixed">
    <div class="sidebar-header p-3 border-bottom">
        <a href="<?= BASE_URL ?>dashboard/" class="d-flex align-items-center text-white text-decoration-none">
            <img src="<?= BASE_URL ?>assets/img/logo.png" alt="Logo" height="32" class="me-2">
            <span class="fs-5">Alliam Admin</span>
        </a>
    </div>

    <div class="sidebar-user p-3 border-bottom">
        <div class="d-flex align-items-center">
            <div class="me-2">
                <?php if ($current_admin['avatar']): ?>
                    <img src="<?= BASE_URL ?>uploads/avatars/<?= $current_admin['avatar'] ?>" alt="<?= htmlspecialchars($current_admin['full_name']) ?>" class="rounded-circle" width="40" height="40">
                <?php else: ?>
                    <?= generate_avatar($current_admin['full_name'], 40) ?>
                <?php endif; ?>
            </div>
            <div>
                <div class="fw-bold"><?= htmlspecialchars($current_admin['full_name']) ?></div>
                <div class="small">
                    <span class="badge bg-<?= $role_colors[$current_admin['role']] ?? 'secondary' ?>">
                        <?= $current_admin['role'] === 'superadmin' ? 'Супер-админ' : ($current_admin['role'] === 'lawyer' ? 'Юрист' : 'Менеджер') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="sidebar-menu p-3">
        <ul class="nav nav-pills flex-column">
            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>dashboard/" class="nav-link <?= ($current_dir == 'dashboard') ? 'active' : 'text-white' ?>">
                    <i class="bi bi-speedometer2 me-2"></i> Дашборд
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>clients/" class="nav-link <?= ($current_dir == 'clients') ? 'active' : 'text-white' ?>">
                    <i class="bi bi-people me-2"></i> Клиенты
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>chats/" class="nav-link <?= ($current_dir == 'chats') ? 'active' : 'text-white' ?>">
                    <i class="bi bi-chat-dots me-2"></i> Чаты
                    <span class="badge bg-danger rounded-pill float-end" id="unreadMessagesCount">0</span>
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>payments/" class="nav-link <?= ($current_dir == 'payments') ? 'active' : 'text-white' ?>">
                    <i class="bi bi-credit-card me-2"></i> Оплаты
                </a>
            </li>

            <?php if (has_permission(ROLE_SUPERADMIN)): ?>
                <li class="nav-item mb-2">
                    <a href="<?= BASE_URL ?>users/" class="nav-link <?= ($current_dir == 'users') ? 'active' : 'text-white' ?>">
                        <i class="bi bi-person-badge me-2"></i> Сотрудники
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>settings/" class="nav-link <?= ($current_dir == 'settings') ? 'active' : 'text-white' ?>">
                    <i class="bi bi-gear me-2"></i> Настройки
                </a>
            </li>
        </ul>

        <hr class="border-secondary my-4">

        <div class="mt-auto">
            <a href="<?= BASE_URL ?>logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-right me-2"></i> Выход
            </a>
        </div>
    </div>
</nav>

<style>
    .sidebar {
        width: 250px;
    }
    .sidebar .nav-link {
        border-radius: 4px;
        transition: all 0.2s;
    }
    .sidebar .nav-link:hover {
        background-color: rgba(255,255,255,0.1);
    }
    .sidebar .nav-link.active {
        background-color: #0d6efd;
    }
</style>

<script>
    // Функция для проверки непрочитанных сообщений
    function checkUnreadMessages() {
        $.ajax({
            url: '<?= BASE_URL ?>includes/ajax.php?action=get_unread_count',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.count > 0) {
                    $('#unreadMessagesCount').text(response.count).show();
                } else {
                    $('#unreadMessagesCount').hide();
                }
            }
        });
    }

    // Проверяем каждые 30 секунд
    $(document).ready(function() {
        checkUnreadMessages();
        setInterval(checkUnreadMessages, 30000);
    });
</script>