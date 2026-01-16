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

// Статистика по этапам банкротства
$stages_stmt = $db->query("
    SELECT 
        bs.stage_name,
        COUNT(DISTINCT u.id) as client_count
    FROM bankruptcy_stages bs
    LEFT JOIN users u ON 1=1 -- Здесь будет логика определения этапа клиента
    WHERE bs.is_active = 1
    GROUP BY bs.id
    ORDER BY bs.stage_order
");
$stages_data = $stages_stmt->fetchAll();

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
    LIMIT 10
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
    LIMIT 10
");
$messages_stmt->execute();
$recent_messages = $messages_stmt->fetchAll();

// Установка заголовка
$page_title = 'Дашборд';
?>
<?php include '../includes/header.php'; ?>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Панель управления</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-calendar me-1"></i> Сегодня: <?= date('d.m.Y') ?>
                </button>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshBtn">
                <i class="bi bi-arrow-clockwise"></i> Обновить
            </button>
        </div>
    </div>

    <!-- Флеш-сообщения -->
<?= display_flash_message() ?>

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle text-muted">Всего клиентов</h6>
                            <h2 class="card-title"><?= $stats['total_clients'] ?></h2>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-people display-6"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                    <span class="badge bg-success">
                        <i class="bi bi-arrow-up"></i> +<?= $stats['new_today'] ?> сегодня
                    </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle text-muted">Непрочитанных</h6>
                            <h2 class="card-title"><?= $stats['unread_messages'] ?></h2>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-chat-dots display-6"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                    <span class="badge bg-info">
                        <i class="bi bi-envelope"></i> <?= $stats['messages_today'] ?> сегодня
                    </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle text-muted">Активных (неделя)</h6>
                            <h2 class="card-title"><?= $stats['active_week'] ?></h2>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-activity display-6"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="clients/?filter=active" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-eye me-1"></i> Просмотр
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle text-muted">Мои клиенты</h6>
                            <h2 class="card-title">
                                <?php
                                $my_clients_stmt = $db->prepare("
                                SELECT COUNT(DISTINCT client_id) 
                                FROM client_assignments 
                                WHERE admin_id = ? AND assignment_type = 'primary'
                            ");
                                $my_clients_stmt->execute([$current_admin['id']]);
                                echo $my_clients_stmt->fetchColumn();
                                ?>
                            </h2>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-person-badge display-6"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="clients/?assigned_to=<?= $current_admin['id'] ?>" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-list me-1"></i> Список
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- График и недавние действия -->
    <div class="row">
        <!-- График распределения по этапам -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-bar-chart me-2"></i> Распределение клиентов по этапам банкротства
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="stagesChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Быстрые действия -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i> Быстрые действия
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="../clients/?new=true" class="btn btn-primary">
                            <i class="bi bi-person-plus me-2"></i> Добавить клиента
                        </a>
                        <a href="../clients/" class="btn btn-outline-primary">
                            <i class="bi bi-search me-2"></i> Поиск клиента
                        </a>
                        <a href="../chats/" class="btn btn-outline-info">
                            <i class="bi bi-chat-left-text me-2"></i> Перейти в чаты
                        </a>
                        <?php if (has_permission(ROLE_LAWYER)): ?>
                            <a href="#" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#reportModal">
                                <i class="bi bi-file-earmark-text me-2"></i> Создать отчет
                            </a>
                        <?php endif; ?>
                    </div>

                    <hr class="my-3">

                    <h6 class="mb-3">Статус системы</h6>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>База данных</span>
                            <span class="badge bg-success">Online</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Сервер</span>
                            <span class="badge bg-success">Стабильный</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Обновления</span>
                            <span class="badge bg-info">Доступны</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Последние клиенты -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i> Последние клиенты
                    </h5>
                    <a href="../clients/" class="btn btn-sm btn-outline-primary">Все клиенты</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th>Имя</th>
                                <th>Телефон</th>
                                <th>Дата регистрации</th>
                                <th>Последний вход</th>
                                <th>Ответственный</th>
                                <th>Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($recent_clients as $client): ?>
                                <tr>
                                    <td>
                                        <a href="../clients/view.php?id=<?= $client['id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($client['full_name'] ?: 'Не указано') ?>
                                        </a>
                                    </td>
                                    <td><?= format_phone($client['phone']) ?></td>
                                    <td><?= format_date($client['created_at']) ?></td>
                                    <td>
                                        <?php if ($client['last_login']): ?>
                                            <span class="badge bg-info"><?= format_date($client['last_login'], 'd.m.Y') ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Нет входа</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($client['assigned_lawyer']): ?>
                                            <span class="badge bg-primary"><?= htmlspecialchars($client['assigned_lawyer']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Не назначен</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="../clients/view.php?id=<?= $client['id'] ?>" class="btn btn-outline-info" title="Просмотр">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="../chats/?client=<?= $client['id'] ?>" class="btn btn-outline-success" title="Чат">
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
    </div>

    <!-- Последние сообщения -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-chat-left-text me-2"></i> Последние сообщения
                    </h5>
                    <a href="../chats/" class="btn btn-sm btn-outline-info">Все сообщения</a>
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
                                        <a href="../clients/view.php?id=<?= $message['client_id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($message['client_name'] ?: 'Клиент #' . $message['client_id']) ?>
                                        </a>
                                    </td>
                                    <td><?= truncate_text($message['content'], 50) ?></td>
                                    <td>
                                        <?php if ($message['admin_id']): ?>
                                            <span class="badge bg-primary"><?= htmlspecialchars($message['admin_name']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Клиент</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= format_date($message['created_at'], 'H:i d.m') ?></td>
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

    <!-- Модальное окно отчета -->
    <div class="modal fade" id="reportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Создание отчета</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="reportForm">
                        <div class="mb-3">
                            <label for="reportType" class="form-label">Тип отчета</label>
                            <select class="form-select" id="reportType" required>
                                <option value="">Выберите тип отчета</option>
                                <option value="clients">Клиенты</option>
                                <option value="messages">Сообщения</option>
                                <option value="payments">Оплаты</option>
                                <option value="activity">Активность</option>
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label for="dateFrom" class="form-label">Дата с</label>
                                <input type="date" class="form-control" id="dateFrom" value="<?= date('Y-m-d', strtotime('-1 month')) ?>">
                            </div>
                            <div class="col">
                                <label for="dateTo" class="form-label">Дата по</label>
                                <input type="date" class="form-control" id="dateTo" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="format" class="form-label">Формат</label>
                            <select class="form-select" id="format">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" id="generateReport">
                        <i class="bi bi-download me-2"></i> Сгенерировать
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // График распределения по этапам
        const stagesCtx = document.getElementById('stagesChart').getContext('2d');
        const stagesChart = new Chart(stagesCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($stages_data, 'stage_name')) ?>,
                datasets: [{
                    label: 'Количество клиентов',
                    data: <?= json_encode(array_column($stages_data, 'client_count')) ?>,
                    backgroundColor: [
                        '#007bff', '#28a745', '#dc3545', '#ffc107',
                        '#17a2b8', '#6c757d', '#6610f2', '#e83e8c'
                    ],
                    borderColor: [
                        '#0069d9', '#218838', '#c82333', '#e0a800',
                        '#138496', '#5a6268', '#520dc2', '#d91a72'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Кнопка обновления
        document.getElementById('refreshBtn').addEventListener('click', function() {
            location.reload();
        });

        // Генерация отчета
        document.getElementById('generateReport').addEventListener('click', function() {
            const type = document.getElementById('reportType').value;
            const from = document.getElementById('dateFrom').value;
            const to = document.getElementById('dateTo').value;
            const format = document.getElementById('format').value;

            if (!type) {
                alert('Пожалуйста, выберите тип отчета');
                return;
            }

            // Здесь будет AJAX запрос для генерации отчета
            alert('Отчет будет сгенерирован в формате ' + format + ' за период с ' + from + ' по ' + to);

            // Закрываем модальное окно
            bootstrap.Modal.getInstance(document.getElementById('reportModal')).hide();
        });

        // Обновляем время на странице каждую минуту
        function updateTime() {
            const now = new Date();
            document.querySelector('[class*="calendar"]').innerHTML =
                '<i class="bi bi-calendar me-1"></i> Сегодня: ' +
                now.toLocaleDateString('ru-RU') + ' ' +
                now.toLocaleTimeString('ru-RU', {hour: '2-digit', minute:'2-digit'});
        }

        setInterval(updateTime, 60000);
    </script>

<?php include '../includes/footer.php'; ?>