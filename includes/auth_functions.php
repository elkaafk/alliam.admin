<?php
/**
 * Функции авторизации и аутентификации
 */

/**
 * Вход администратора
 */
function admin_login($username, $password) {
    $db = get_db_connection();

    $stmt = $db->prepare("
        SELECT id, username, email, password_hash, full_name, role, is_active, 
               avatar, failed_attempts, locked_until
        FROM admin_users 
        WHERE username = ? OR email = ?
    ");
    $stmt->execute([$username, $username]);
    $admin = $stmt->fetch();

    if (!$admin) {
        return false;
    }

    // Проверяем заблокирован ли аккаунт
    if ($admin['locked_until'] && strtotime($admin['locked_until']) > time()) {
        return false;
    }

    // Проверяем пароль
    if (password_verify($password, $admin['password_hash'])) {
        // Сбрасываем счетчик неудачных попыток
        $reset_stmt = $db->prepare("
            UPDATE admin_users 
            SET failed_attempts = 0, locked_until = NULL, last_active = NOW() 
            WHERE id = ?
        ");
        $reset_stmt->execute([$admin['id']]);

        // Создаем сессию
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_username'] = $admin['username'];

        // Логируем вход
        log_admin_activity($admin['id'], 'login', 'system', null, [
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ]);

        return true;
    } else {
        // Увеличиваем счетчик неудачных попыток
        $failed_attempts = $admin['failed_attempts'] + 1;

        if ($failed_attempts >= 5) {
            // Блокируем на 30 минут
            $lock_until = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $stmt = $db->prepare("
                UPDATE admin_users 
                SET failed_attempts = ?, locked_until = ? 
                WHERE id = ?
            ");
            $stmt->execute([$failed_attempts, $lock_until, $admin['id']]);
        } else {
            $stmt = $db->prepare("UPDATE admin_users SET failed_attempts = ? WHERE id = ?");
            $stmt->execute([$failed_attempts, $admin['id']]);
        }

        return false;
    }
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
    if (isset($_SESSION['admin_id'])) {
        log_admin_activity($_SESSION['admin_id'], 'logout', 'system');
    }

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
?>