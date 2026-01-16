<?php
/**
 * Генератор хеша пароля для тестирования
 */
echo '<!DOCTYPE html><html><head><title>Генератор хеша</title>';
echo '<style>body {font-family: Arial; padding: 20px;}</style></head><body>';
echo '<h2>🔐 Генератор хеша пароля</h2>';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if ($password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        echo '<div style="padding: 15px; background: #d4edda; border-radius: 5px; margin: 10px 0;">';
        echo '<h4>✅ Хеш создан:</h4>';
        echo '<pre style="background: #e9ecef; padding: 10px; border-radius: 5px; overflow-x: auto;">';
        echo htmlspecialchars($hash);
        echo '</pre>';

        echo '<h4>📝 SQL для обновления:</h4>';
        echo '<pre style="background: #fff3cd; padding: 10px; border-radius: 5px;">';
        echo "UPDATE admin_users SET password_hash = '" . htmlspecialchars($hash) . "' WHERE username = 'superadmin';";
        echo '</pre>';
        echo '</div>';
    }
}

echo '<form method="POST" style="margin-top: 20px;">';
echo '<div style="margin-bottom: 10px;">';
echo '<label for="password">Введите пароль:</label><br>';
echo '<input type="text" id="password" name="password" value="Admin123!" style="padding: 8px; width: 300px; margin-top: 5px;">';
echo '</div>';
echo '<button type="submit" style="padding: 10px 20px; background: #0d6efd; color: white; border: none; border-radius: 5px;">Сгенерировать хеш</button>';
echo '</form>';

echo '</body></html>';