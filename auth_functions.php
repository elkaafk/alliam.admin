<?php
/**
 * Функции авторизации и аутентификации
 */

/**
 * Вход администратора (упрощенная версия для отладки)
 */
function admin_login($username, $password) {
    $db = get_db_connection();

    // Ищем пользователя по логину ИЛИ email
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $admin = $stmt->fetch();

    // Отладка - выводим в лог
    error_log("=== DEBUG ADMIN LOGIN ===");
    error_log("Username: $username");
    error_log("Password length: " . strlen($password));
    error_log("User found: " . ($admin ? 'YES' : 'NO'));

    if (!$admin) {
        error_log("User not found");
        return false;
    }

    error_log("User ID: " . $admin['id']);
    error_log("User active: " . $admin['is_active']);
    error_log("Password hash in DB: " . $admin['password_hash']);
    error_log("Password hash length: " . strlen($admin['password_hash']));

    // Проверяем активен ли пользователь
    if (!$admin['is_active']) {
        error_log("User is not active");
        return false;
    }

    // Проверяем пароль
    $password_verified = password_verify($password, $admin['password_hash']);
    error_log("Password verify result: " . ($password_verified ? 'TRUE' : 'FALSE'));

    if (!$password_verified) {
        // Для отладки: попробуем прямую проверку хеша
        error_log("=== PASSWORD DEBUG ===");
        error_log("Input password: $password");
        error_log("Stored hash: " . $admin['password_hash']);

        // Проверим формат хеша
        $hash_info = password_get_info($admin['password_hash']);
        error_log("Hash algo: " . $hash_info['algoName']);
        error_log("Hash options: " . print_r($hash_info['options'], true));

        return false;
    }

    // Успешная авторизация - создаем сессию
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['admin_name'] = $admin['full_name'];
    $_SESSION['admin_username'] = $admin['username'];

    // Сбрасываем счетчик неудачных попыток
    $stmt = $db->prepare("UPDATE admin_users SET failed_attempts = 0, last_active = NOW() WHERE id = ?");
    $stmt->execute([$admin['id']]);

    error_log("Login successful for user: " . $admin['username']);
    error_log("Session created with ID: " . $admin['id']);

    return true;
}

/**
 * Проверка авторизации администратора
 */
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Получение информации о текущем администраторе
 */
function get_current_admin() {
    if (!is_admin_logged_in()) {
        return null;
    }

    $db = get_db_connection();
    $stmt = $db->prepare("
        SELECT id, username, email, full_name, role, avatar, phone, 
               created_at, last_active, permissions
        FROM admin_users 
        WHERE id = ? AND is_active = 1
    ");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();

    if ($admin) {
        // Обновляем время последней активности
        $update_stmt = $db->prepare("UPDATE admin_users SET last_active = NOW() WHERE id = ?");
        $update_stmt->execute([$admin['id']]);

        return $admin;
    }

    return null;
}

/**
 * Проверка прав доступа
 */
function has_permission($required_role, $current_role = null) {
    if ($current_role === null) {
        $current_role = $_SESSION['admin_role'] ?? null;
    }

    $hierarchy = [
        ROLE_SUPERADMIN => 3,
        ROLE_LAWYER => 2,
        ROLE_MANAGER => 1
    ];

    $current_level = $hierarchy[$current_role] ?? 0;
    $required_level = $hierarchy[$required_role] ?? 0;

    return $current_level >= $required_level;
}

/**
 * Выход из системы
 */
function admin_logout() {
    session_unset();
    session_destroy();
    session_start();
}

/**
 * Логирование активности администратора
 */
function log_admin_activity($admin_id, $action, $entity_type = null, $entity_id = null, $details = []) {
    $db = get_db_connection();

    $stmt = $db->prepare("
        INSERT INTO admin_activity_log 
        (admin_id, action, entity_type, entity_id, details, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $details_json = !empty($details) ? json_encode($details, JSON_UNESCAPED_UNICODE) : null;

    return $stmt->execute([
        $admin_id,
        $action,
        $entity_type,
        $entity_id,
        $details_json,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);
}

/**
 * Простая проверка пароля (для отладки)
 */
function debug_check_password($password) {
    $db = get_db_connection();

    $stmt = $db->prepare("SELECT password_hash FROM admin_users WHERE username = 'superadmin'");
    $stmt->execute();
    $row = $stmt->fetch();

    if (!$row) {
        return "User not found";
    }

    $hash = $row['password_hash'];
    $result = password_verify($password, $hash);

    return [
        'password' => $password,
        'hash' => $hash,
        'verified' => $result,
        'hash_info' => password_get_info($hash)
    ];
}
?>