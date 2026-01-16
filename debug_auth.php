<?php
require_once 'includes/config.php';

echo '<pre>';

// Проверяем существование пользователя
$db = get_db_connection();
$stmt = $db->prepare("SELECT * FROM admin_users WHERE username = 'superadmin'");
$stmt->execute();
$admin = $stmt->fetch();

echo "=== Информация о пользователе ===\n";
print_r($admin);

echo "\n=== Проверка пароля ===\n";
$password = 'Admin123!';
echo "Пароль для проверки: $password\n";
echo "Хеш в БД: " . $admin['password_hash'] . "\n";
echo "Проверка: " . (password_verify($password, $admin['password_hash']) ? 'УСПЕХ' : 'ОШИБКА') . "\n";

echo "\n=== Генерация нового хеша ===\n";
$new_hash = password_hash($password, PASSWORD_DEFAULT);
echo "Новый хеш: $new_hash\n";

echo "\n=== SQL для обновления ===\n";
echo "UPDATE admin_users SET password_hash = '$new_hash' WHERE id = {$admin['id']};\n";