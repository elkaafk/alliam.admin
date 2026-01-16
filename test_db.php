<?php
/**
 * Тест подключения к БД
 */

// Временно отключаем редиректы config.php
define('SKIP_AUTH_REDIRECT', true);

echo '<!DOCTYPE html><html><head><title>Тест БД</title>';
echo '<meta charset="UTF-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo '<style>';
echo 'body {font-family: "Segoe UI", Arial, sans-serif; padding: 20px; background: #f8f9fa;}';
echo 'h2 {color: #495057;}';
echo '.info-box {padding: 15px; background: #ffffff; border-radius: 8px; margin: 10px 0; border: 1px solid #dee2e6;}';
echo '.success-box {background: #d4edda; color: #155724; border-color: #c3e6cb;}';
echo '.warning-box {background: #fff3cd; color: #856404; border-color: #ffeaa7;}';
echo '.error-box {background: #f8d7da; color: #721c24; border-color: #f5c6cb;}';
echo '.info-box h4 {margin-top: 0;}';
echo 'table {width: 100%; border-collapse: collapse; margin-top: 10px;}';
echo 'th {background: #e9ecef; padding: 8px; text-align: left; border-bottom: 2px solid #dee2e6;}';
echo 'td {padding: 8px; border-bottom: 1px solid #dee2e6;}';
echo 'tr:hover {background: #f8f9fa;}';
echo '.btn {display: inline-block; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px; font-weight: 500;}';
echo '.btn-primary {background: #0d6efd; color: white;}';
echo '.btn-success {background: #198754; color: white;}';
echo '.btn-secondary {background: #6c757d; color: white;}';
echo '.spinner {border: 3px solid #f3f3f3; border-top: 3px solid #0d6efd; border-radius: 50%; width: 20px; height: 20px; animation: spin 1s linear infinite; display: inline-block; margin-right: 10px;}';
echo '@keyframes spin {0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); }}';
echo '</style>';
echo '</head><body>';

echo '<h2>🔧 Тест подключения к БД Alliam Admin</h2>';

try {
    // Подключаем конфиг с временным отключением редиректов
    $original_get = $_GET;
    $original_server = $_SERVER;

    // Временно меняем PHP_SELF чтобы config.php не редиректил
    $_SERVER['PHP_SELF'] = 'test_db.php';

    // Подключаем конфиг
    require_once 'includes/config.php';

    echo '<div class="info-box">';
    echo '<h4>📋 Настройки подключения:</h4>';
    echo '<pre style="background: #e9ecef; padding: 10px; border-radius: 5px;">';
    echo "DB_HOST: " . DB_HOST . "\n";
    echo "DB_NAME: " . DB_NAME . "\n";
    echo "DB_USER: " . DB_USER . "\n";
    echo "DB_PASS: " . (DB_PASS ? '*** (установлен)' : '(пусто)') . "\n";
    echo "DB_CHARSET: " . DB_CHARSET . "\n";
    echo "BASE_URL: " . BASE_URL . "\n";
    echo '</pre>';
    echo '</div>';

    // Пробуем подключиться
    echo '<div class="info-box">';
    echo '<h4><span class="spinner"></span> Подключаемся к базе данных...</h4>';

    $db = get_db_connection();

    echo '<div class="success-box" style="margin-top: 10px;">';
    echo '<h4>✅ Подключение успешно!</h4>';
    echo '<p>Соединение с базой данных установлено.</p>';
    echo '</div>';
    echo '</div>';

    // Проверяем таблицы
    echo '<div class="info-box">';
    echo '<h4>🔍 Проверка структуры базы данных...</h4>';

    $stmt = $db->query("SHOW TABLES LIKE 'admin_users'");
    $admin_table = $stmt->fetch();

    if ($admin_table) {
        echo '<div class="success-box" style="margin-top: 10px;">';
        echo '<h4>✅ Таблица admin_users найдена</h4>';

        // Проверяем наличие пользователей
        $stmt = $db->query("SELECT COUNT(*) as count FROM admin_users");
        $count = $stmt->fetch()['count'];

        echo "<p>👥 Пользователей в таблице: <strong>$count</strong></p>";

        // Показываем список пользователей
        $stmt = $db->query("SELECT id, username, email, role, is_active, created_at FROM admin_users ORDER BY id LIMIT 10");
        $users = $stmt->fetchAll();

        if ($users) {
            echo '<table>';
            echo '<tr><th>ID</th><th>Логин</th><th>Email</th><th>Роль</th><th>Активен</th><th>Создан</th></tr>';
            foreach ($users as $user) {
                echo '<tr>';
                echo '<td>' . $user['id'] . '</td>';
                echo '<td>' . htmlspecialchars($user['username']) . '</td>';
                echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                echo '<td><span style="padding: 3px 8px; border-radius: 12px; font-size: 12px; background: ';
                echo $user['role'] === 'superadmin' ? '#dc3545' : ($user['role'] === 'lawyer' ? '#0d6efd' : '#198754');
                echo '; color: white;">' . htmlspecialchars($user['role']) . '</span></td>';
                echo '<td>' . ($user['is_active'] ? '✅ Да' : '❌ Нет') . '</td>';
                echo '<td>' . date('d.m.Y', strtotime($user['created_at'])) . '</td>';
                echo '</tr>';
            }
            echo '</table>';

            // Проверяем возможность входа
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM admin_users WHERE username = 'superadmin' AND is_active = 1");
            $stmt->execute();
            $superadmin_exists = $stmt->fetch()['count'] > 0;

            if ($superadmin_exists) {
                echo '<div class="success-box" style="margin-top: 10px;">';
                echo '<h4>👑 Пользователь superadmin доступен для входа</h4>';
                echo '<p>Пароль по умолчанию: <code>Admin123!</code></p>';
                echo '</div>';
            }
        }
        echo '</div>';
    } else {
        echo '<div class="error-box" style="margin-top: 10px;">';
        echo '<h4>❌ Таблица admin_users не найдена!</h4>';
        echo '<p>Выполни SQL для создания таблиц:</p>';
        echo '<ol>';
        echo '<li>Открой PHPMyAdmin</li>';
        echo '<li>Выбери базу данных "' . DB_NAME . '"</li>';
        echo '<li>Перейди во вкладку "SQL"</li>';
        echo '<li>Вставь SQL из файла README.md или setup.sql</li>';
        echo '</ol>';
        echo '</div>';
    }
    echo '</div>';

    // Проверяем другие таблицы
    $required_tables = ['client_assignments', 'admin_messages', 'admin_activity_log', 'users'];
    echo '<div class="info-box">';
    echo '<h4>📊 Проверка дополнительных таблиц:</h4>';

    foreach ($required_tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->fetch();

        echo '<p>' . ($exists ? '✅' : '❌') . ' Таблица <strong>' . $table . '</strong>: '
            . ($exists ? 'найдена' : 'не найдена') . '</p>';
    }
    echo '</div>';

    echo '<div style="margin-top: 20px; padding: 20px; background: white; border-radius: 8px; border: 1px solid #dee2e6;">';
    echo '<h4>🚀 Быстрые ссылки:</h4>';
    echo '<a href="login.php" class="btn btn-primary">Перейти к странице входа</a>';
    echo '<a href="dashboard/" class="btn btn-success">Перейти в дашборд (если авторизован)</a>';
    echo '<a href="index.php" class="btn btn-secondary">На главную</a>';
    echo '</div>';

} catch (Exception $e) {
    echo '<div class="error-box">';
    echo '<h4>❌ Ошибка подключения!</h4>';
    echo '<p><strong>' . htmlspecialchars($e->getMessage()) . '</strong></p>';

    echo '<h5>🛠️ Возможные причины и решения:</h5>';
    echo '<ol>';
    echo '<li><strong>MySQL не запущен</strong> - Запусти MySQL сервер через XAMPP, WAMP или MAMP</li>';
    echo '<li><strong>Неверные данные подключения</strong> - Проверь includes/config.php</li>';
    echo '<li><strong>База данных не существует</strong> - Создай БД "' . DB_NAME . '" в PHPMyAdmin</li>';
    echo '<li><strong>Нет прав у пользователя</strong> - Проверь права для пользователя "' . DB_USER . '"</li>';
    echo '<li><strong>Неверный пароль</strong> - Проверь пароль MySQL</li>';
    echo '</ol>';

    echo '<div style="margin-top: 15px; padding: 10px; background: #fff3cd; border-radius: 5px;">';
    echo '<h5>📝 Действия для проверки:</h5>';
    echo '<p>1. Открой PHPMyAdmin: <a href="http://localhost/phpmyadmin" target="_blank">http://localhost/phpmyadmin</a></p>';
    echo '<p>2. Попробуй подключиться с теми же данными</p>';
    echo '<p>3. Если успешно - данные верные, проблема в коде</p>';
    echo '<p>4. Если нет - исправь данные в includes/config.php</p>';
    echo '</div>';

    echo '</div>';

    echo '<div style="margin-top: 20px;">';
    echo '<a href="login.php" class="btn btn-secondary">Перейти к логину</a>';
    echo '</div>';
}

// Восстанавливаем оригинальные переменные
$_GET = $original_get;
$_SERVER = $original_server;

echo '</body></html>';