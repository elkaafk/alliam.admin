<?php
/**
 * Главная панель управления (Дашборд)
 */
require_once '../includes/config.php';

// Проверяем права доступа
if (!is_admin_logged_in()) {
    header('Location: ../login.php');
    exit;
}

$current_admin = get_current_admin();

// Получаем статистику
$db = get_db_connection();

// Общая статистика
$stats_stmt = $db->query("
    SELECT 
        (SELECT COUNT(*) FROM users WHERE is_active = 1) as total_clients,
        (SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()) as new_today,
        (SELECT COUNT(*) FROM admin_messages WHERE DATE(created_at) = CURDATE()) as messages_today,
        (SELECT COUNT(DISTINCT client_id) FROM admin_messages WHERE is_read = 0 AND admin_id IS NULL) as unread_messages,
        (SELECT COUNT(*) FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as active_week
");
$stats = $stats_stmt->fetch();

// Мои клиенты
$my_clients_stmt = $db->prepare("
    SELECT COUNT(DISTINCT client_id) as count
    FROM client_assignments 
    WHERE admin_id = ? AND assignment_type = 'primary'
");
$my_clients_stmt->execute([$current_admin['id']]);
$my_clients = $my_clients_stmt->fetch()['count'];

// Последние клиенты
$clients_stmt = $db->prepare("
    SELECT 
        u.id,
        CONCAT_WS(' ', u.last_name, u.first_name, u.middle_name) as full_name,
        u.phone,
        u.created_at,
        u.last_login,
        au.full_name as assigned_lawyer
    FROM users u
    LEFT JOIN client_assignments ca ON u.id = ca.client_id AND ca.assignment_type = 'primary'
    LEFT JOIN admin_users au ON ca.admin_id = au.id
    ORDER BY u.created_at DESC
    LIMIT 5
");
$clients_stmt->execute();
$recent_clients = $clients_stmt->fetchAll();

// Последние сообщения
$messages_stmt = $db->prepare("
    SELECT 
        m.*,
        CONCAT_WS(' ', u.last_name, u.first_name) as client_name,
        au.full_name as admin_name
    FROM admin_messages m
    LEFT JOIN users u ON m.client_id = u.id
    LEFT JOIN admin_users au ON m.admin_id = au.id
    ORDER BY m.created_at DESC
    LIMIT 5
");
$messages_stmt->execute();
$recent_messages = $messages_stmt->fetchAll();

// Активность по дням (последние 7 дней)
$activity_stmt = $db->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as count
    FROM admin_activity_log
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$activity_stmt->execute();
$activity_data = $activity_stmt->fetchAll();

// Установка заголовка
$page_title = 'Дашборд';
?>
<?php include '../includes/header.php'; ?>

    <!-- Основной контент -->
    <main class="main-content">
        <!-- Верхняя панель -->
        <div class="top-bar">
            <div class="page-title">
                <h1>Панель управления</h1>
                <div class="text-gray-600 small">
                    <i class="bi bi-calendar3 me-1"></i>
                    Сегодня: <?= date('d.m.Y') ?>
                </div>
            </div>

            <div class="page-actions">
                <div class="search-box">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Поиск...">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>

                <div class="dropdown ms-3">
                    <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center"
                            type="button" data-bs-toggle="dropdown">
                        <div class="user-avatar-sm me-2">
                            <?= mb_substr($current_admin['full_name'], 0, 1, 'UTF-8') ?>
                        </div>
                        <span class="d-none d-md-inline"><?= htmlspecialchars(explode(' ', $current_admin['full_name'])[0]) ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header"><?= htmlspecialchars($current_admin['full_name']) ?></h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>settings/"><i class="bi bi-gear me-2"></i> Настройки</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Выход</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Контент -->
        <div class="content-container">


            <!-- Статистика -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card border-left-primary">
                        <div class="card-body position-relative">
                            <div class="stat-number text-primary"><?= $stats['total_clients'] ?></div>
                            <div class="stat-label">Всего клиентов</div>
                            <div class="text-muted small">
                                <i class="bi bi-arrow-up text-success me-1"></i>
                                +<?= $stats['new_today'] ?> сегодня
                            </div>
                            <div class="stat-icon text-primary">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card border-left-success">
                        <div class="card-body position-relative">
                            <div class="stat-number text-success"><?= $my_clients ?></div>
                            <div class="stat-label">Мои клиенты</div>
                            <div class="text-muted small">
                                <i class="bi bi-person-check me-1"></i>
                                Назначено вам
                            </div>
                            <div class="stat-icon text-success">
                                <i class="bi bi-person-badge"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card border-left-warning">
                        <div class="card-body position-relative">
                            <div class="stat-number text-warning"><?= $stats['unread_messages'] ?></div>
                            <div class="stat-label">Непрочитанных</div>
                            <div class="text-muted small">
                                <i class="bi bi-envelope me-1"></i>
                                <?= $stats['messages_today'] ?> сегодня
                            </div>
                            <div class="stat-icon text-warning">
                                <i class="bi bi-chat-dots"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card border-left-info">
                        <div class="card-body position-relative">
                            <div class="stat-number text-info"><?= $stats['active_week'] ?></div>
                            <div class="stat-label">Активных (неделя)</div>
                            <div class="text-muted small">
                                <i class="bi bi-activity me-1"></i>
                                За последние 7 дней
                            </div>
                            <div class="stat-icon text-info">
                                <i class="bi bi-graph-up"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Последние клиенты -->
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-people me-2"></i> Последние клиенты
                            </h5>
                            <a href="<?= BASE_URL ?>clients/" class="btn btn-sm btn-outline-dark">
                                Все клиенты <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                    <tr>
                                        <th>Клиент</th>
                                        <th>Телефон</th>
                                        <th>Дата регистрации</th>
                                        <th>Ответственный</th>
                                        <th>Действия</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($recent_clients as $client): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-2" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                                        <?= mb_substr($client['full_name'] ?: 'Н', 0, 1, 'UTF-8') ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium"><?= htmlspecialchars($client['full_name'] ?: 'Не указано') ?></div>
                                                        <small class="text-muted">ID: <?= $client['id'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= format_phone($client['phone']) ?></td>
                                            <td>
                                                <div><?= format_date($client['created_at'], 'd.m.Y') ?></div>
                                                <small class="text-muted"><?= format_date($client['created_at'], 'H:i') ?></small>
                                            </td>
                                            <td>
                                                <?php if ($client['assigned_lawyer']): ?>
                                                    <span class="badge bg-gray-600"><?= htmlspecialchars($client['assigned_lawyer']) ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-gray-500">Не назначен</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= BASE_URL ?>clients/view.php?id=<?= $client['id'] ?>"
                                                       class="btn btn-outline-dark"
                                                       title="Просмотр">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="<?= BASE_URL ?>chats/?client=<?= $client['id'] ?>"
                                                       class="btn btn-outline-dark"
                                                       title="Чат">
                                                        <i class="bi bi-chat"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Быстрые действия -->
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-lightning me-2"></i> Быстрые действия
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="<?= BASE_URL ?>clients/?new=true" class="btn btn-dark">
                                    <i class="bi bi-person-plus me-2"></i> Добавить клиента
                                </a>
                                <a href="<?= BASE_URL ?>clients/" class="btn btn-outline-dark">
                                    <i class="bi bi-search me-2"></i> Поиск клиента
                                </a>
                                <a href="<?= BASE_URL ?>chats/" class="btn btn-outline-dark">
                                    <i class="bi bi-chat-left-text me-2"></i> Перейти в чаты
                                </a>

                                <?php if (has_permission(ROLE_LAWYER)): ?>
                                    <a href="#" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#reportModal">
                                        <i class="bi bi-file-earmark-text me-2"></i> Создать отчет
                                    </a>
                                <?php endif; ?>
                            </div>

                            <hr class="my-3">


                        </div>
                    </div>
                </div>
            </div>

            <!-- Последние сообщения -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-chat-left-text me-2"></i> Последние сообщения
                            </h5>
                            <a href="<?= BASE_URL ?>chats/" class="btn btn-sm btn-outline-dark">
                                Все сообщения <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                    <tr>
                                        <th>Клиент</th>
                                        <th>Сообщение</th>
                                        <th>Отправитель</th>
                                        <th>Время</th>
                                        <th>Статус</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($recent_messages as $message): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-medium">
                                                    <?= htmlspecialchars($message['client_name'] ?: 'Клиент #' . $message['client_id']) ?>
                                                </div>
                                                <small class="text-muted">ID: <?= $message['client_id'] ?></small>
                                            </td>
                                            <td class="text-truncate" style="max-width: 200px;">
                                                <?= htmlspecialchars(truncate_text($message['content'], 60)) ?>
                                            </td>
                                            <td>
                                                <?php if ($message['admin_id']): ?>
                                                    <span class="badge bg-primary"><?= htmlspecialchars($message['admin_name']) ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-gray-600">Клиент</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div><?= format_date($message['created_at'], 'd.m.Y') ?></div>
                                                <small class="text-muted"><?= format_date($message['created_at'], 'H:i') ?></small>
                                            </td>
                                            <td>
                                                <?php if ($message['is_read']): ?>
                                                    <span class="badge bg-success">Прочитано</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Новое</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>





<?php include '../includes/footer.php'; ?>