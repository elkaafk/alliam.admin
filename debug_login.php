<?php
session_start();

// Подключаем конфиг без редиректов
require_once 'includes/config.php';

echo '<!DOCTYPE html>';
echo '<html><head><title>Диагностика авторизации</title>';
echo '<style>';
echo 'body {font-family: Arial, sans-serif; padding: 20px; background: #f8f9fa;}';
echo '.box {background: white; padding: 20px; border-radius: 8px; margin: 10px 0; border: 1px solid #dee2e6;}';
echo '.success {background: #d4edda; border-color: #c3e6cb;}';
echo '.error {background: #f8d7da; border-color: #f5c6cb;}';
echo '.warning {background: #fff3cd; border-color: #ffeaa7;}';
echo 'pre {background: #e9ecef; padding: 10px; border-radius: 5px; overflow-x: auto;}';
echo '</style>';
echo '</head><body>';
echo '<h2>🔍 Диагностика авторизации</h2>';

// Получаем соединение с БД
try {
    $db = get_db_connection();

    echo '<div class="box success">';
    echo '<h3>✅ Соединение с БД установлено</h3>';
    echo '</div>';

    // Проверяем таблицу admin_users
    $stmt = $db->query("SELECT COUNT(*) as count FROM admin_users");
    $count = $stmt->fetch()['count'];

    echo '<div class="box">';
    echo "<h3>📊 Таблица admin_users: $count пользователей</h3>";

    // Показываем всех пользователей
    $stmt = $db->query("SELECT id, username, email, role, is_active, password_hash FROM admin_users");
    $users = $stmt->fetchAll();

    echo '<table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse: collapse; margin-top: 10px;">';
    echo '<tr style="background:#e9ecef;">';
    echo '<th>ID</th><th>Логин</th><th>Email</th><th>Роль</th><th>Активен</th><th>Длина хеша</th>';
    echo '</tr>';

    foreach ($users as $user) {
        echo '<tr>';
        echo '<td>' . $user['id'] . '</td>';
        echo '<td>' . htmlspecialchars($user['username']) . '</td>';
        echo '<td>' . htmlspecialchars($user['email']) . '</td>';
        echo '<td>' . htmlspecialchars($user['role']) . '</td>';
        echo '<td>' . ($user['is_active'] ? '✅ Да' : '❌ Нет') . '</td>';
        echo '<td>' . strlen($user['password_hash']) . ' символов</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>';

    // Проверяем конкретного пользователя superadmin
    echo '<div class="box">';
    echo '<h3>🔐 Проверка пользователя superadmin</h3>';

    $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = 'superadmin'");
    $stmt->execute();
    $superadmin = $stmt->fetch();

    if ($superadmin) {
        echo '<p><strong>Найден пользователь superadmin:</strong></p>';
        echo '<pre>';
        echo "ID: " . $superadmin['id'] . "\n";
        echo "Логин: " . $superadmin['username'] . "\n";
        echo "Email: " . $superadmin['email'] . "\n";
        echo "Роль: " . $superadmin['role'] . "\n";
        echo "Активен: " . ($superadmin['is_active'] ? 'Да' : 'Нет') . "\n";
        echo "Хеш пароля: " . $superadmin['password_hash'] . "\n";
        echo "Длина хеша: " . strlen($superadmin['password_hash']) . " символов\n";
        echo '</pre>';

        // Проверяем пароль
        $test_password = 'Admin123!';
        echo '<p><strong>Тестируем пароль "Admin123!":</strong></p>';

        $is_valid = password_verify($test_password, $superadmin['password_hash']);

        if ($is_valid) {
            echo '<div class="success" style="padding: 10px; border-radius: 5px; margin: 10px 0;">';
            echo '✅ Пароль ВЕРНЫЙ! Хеш соответствует паролю "Admin123!"';
            echo '</div>';
        } else {
            echo '<div class="error" style="padding: 10px; border-radius: 5px; margin: 10px 0;">';
            echo '❌ Пароль НЕВЕРНЫЙ! Хеш не соответствует паролю "Admin123!"';
            echo '</div>';

            // Генерируем новый хеш
            $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
            echo '<p><strong>Новый хеш для "Admin123!":</strong></p>';
            echo '<pre>' . $new_hash . '</pre>';

            echo '<p><strong>SQL для обновления:</strong></p>';
            echo '<pre style="background:#fff3cd;">';
            echo "UPDATE admin_users SET password_hash = '" . $new_hash . "' WHERE id = " . $superadmin['id'] . ";";
            echo '</pre>';
        }

    } else {
        echo '<div class="error">';
        echo '❌ Пользователь superadmin не найден в БД!';
        echo '</div>';
    }
    echo '</div>';

    // Проверяем функцию admin_login
    echo '<div class="box">';
    echo '<h3>🧪 Тест функции admin_login()</h3>';

    // Вызываем функцию с выводом отладки
    echo '<p><strong>Вызываем admin_login("superadmin", "Admin123!"):</strong></p>';

    // Временно включаем вывод ошибок
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Вызываем функцию
    $result = admin_login('superadmin', 'Admin123!');

    echo '<p>Результат: ' . ($result ? '✅ УСПЕХ' : '❌ ОШИБКА') . '</p>';

    // Проверяем сессию
    echo '<p><strong>Данные сессии:</strong></p>';
    echo '<pre>';
    print_r($_SESSION);
    echo '</pre>';

    echo '</div>';

} catch (Exception $e) {
    echo '<div class="box error">';
    echo '<h3>❌ Ошибка при диагностике</h3>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
}

echo '</body></html>';