<?php
/**
 * Вспомогательные функции
 */

/**
 * Форматирование даты
 */
function format_date($date, $format = 'd.m.Y H:i') {
    if (empty($date) || $date === '0000-00-00 00:00:00') {
        return '-';
    }
    return date($format, strtotime($date));
}

/**
 * Форматирование телефона
 */
function format_phone($phone) {
    if (empty($phone)) {
        return '-';
    }

    // Убираем все нецифровые символы
    $phone = preg_replace('/\D/', '', $phone);

    // Проверяем длину
    if (strlen($phone) === 11) {
        return '+7 ' . substr($phone, 1, 3) . ' ' . substr($phone, 4, 3) . '-' . substr($phone, 7, 2) . '-' . substr($phone, 9, 2);
    }

    return $phone;
}

/**
 * Обрезка текста
 */
function truncate_text($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) > $length) {
        return mb_substr($text, 0, $length) . $suffix;
    }
    return $text;
}

/**
 * Получение инициалов
 */
function get_initials($last_name, $first_name, $middle_name = '') {
    $initials = '';

    if (!empty($last_name)) {
        $initials .= mb_substr($last_name, 0, 1) . '.';
    }

    if (!empty($first_name)) {
        $initials .= mb_substr($first_name, 0, 1) . '.';
    }

    if (!empty($middle_name)) {
        $initials .= mb_substr($middle_name, 0, 1) . '.';
    }

    return $initials;
}

/**
 * Генерация аватарки по умолчанию
 */
function generate_avatar($name, $size = 40) {
    // Простая генерация цветного круга с инициалами
    $colors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6c757d'];
    $color = $colors[crc32($name) % count($colors)];

    $initials = get_initials(...explode(' ', $name));

    return '<div class="avatar-circle" style="width: ' . $size . 'px; height: ' . $size . 'px; background-color: ' . $color . '; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: ' . ($size / 2.5) . 'px;">' . $initials . '</div>';
}

/**
 * Проверка формата email
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Редирект с сообщением
 */
function redirect_with_message($url, $type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'text' => $message
    ];
    header('Location: ' . $url);
    exit;
}

/**
 * Отображение флеш-сообщения
 */
function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);

        $alert_class = 'alert-' . $message['type'];
        return '<div class="alert ' . $alert_class . ' alert-dismissible fade show" role="alert">' . htmlspecialchars($message['text']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
    return '';
}

/**
 * Получение текущего URL
 */
function current_url() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Проверка AJAX запроса
 */
function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Генерация токена CSRF
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Проверка токена CSRF
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>