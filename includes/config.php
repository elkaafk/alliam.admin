<?php
/**
 * Основной конфигурационный файл админ-панели
 */

// Запуск сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Настройка временной зоны
date_default_timezone_set('Europe/Moscow');

// Режим отладки
define('DEBUG_MODE', true);
error_reporting(DEBUG_MODE ? E_ALL : 0);
ini_set('display_errors', DEBUG_MODE ? 1 : 0);

// Базовые пути
define('BASE_URL', 'http://localhost:63342/alliam-admin/');
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');

// Проверяем, есть ли файл .env
$env_file = ROOT_PATH . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        define(trim($name), trim($value));
    }
} else {
    // Значения по умолчанию для локальной разработки (ЗАМЕНИ НА СВОИ!)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'alliam_online_test'); // Имя твоей базы данных
    define('DB_USER', 'root');          // Твой пользователь MySQL
    define('DB_PASS', '');              // Твой пароль MySQL
    define('DB_CHARSET', 'utf8mb4');
}

// Константы ролей
define('ROLE_SUPERADMIN', 'superadmin');
define('ROLE_LAWYER', 'lawyer');
define('ROLE_MANAGER', 'manager');

// Цвета для разных ролей (Bootstrap классы)
$role_colors = [
    ROLE_SUPERADMIN => 'danger',
    ROLE_LAWYER => 'primary',
    ROLE_MANAGER => 'success'
];

// Подключение к базе данных
function get_db_connection() {
    static $connection = null;

    if ($connection === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $connection = new PDO($dsn, DB_USER, DB_PASS);
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            // Установка кодировки
            $connection->exec("SET NAMES '" . DB_CHARSET . "'");
            $connection->exec("SET CHARACTER SET '" . DB_CHARSET . "'");

        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Ошибка подключения к БД: " . $e->getMessage() .
                    "<br>Проверь настройки:<br>" .
                    "Хост: " . DB_HOST . "<br>" .
                    "База: " . DB_NAME . "<br>" .
                    "Пользователь: " . DB_USER . "<br>" .
                    "Пароль: " . (DB_PASS ? 'установлен' : 'не установлен'));
            } else {
                error_log("DB Connection Error: " . $e->getMessage());
                header('Location: /errors/500.php');
                exit;
            }
        }
    }

    return $connection;
}

// Тест подключения к БД (можно удалить после настройки)
function test_db_connection() {
    try {
        $db = get_db_connection();

        // Проверяем существование таблицы admin_users
        $stmt = $db->query("SHOW TABLES LIKE 'admin_users'");
        $table_exists = $stmt->fetch();

        if (!$table_exists) {
            die("Таблица admin_users не найдена. Создай таблицы из SQL скрипта.");
        }

        // Проверяем наличие тестового пользователя
        $stmt = $db->query("SELECT COUNT(*) as count FROM admin_users WHERE username = 'superadmin'");
        $admin_exists = $stmt->fetch()['count'] > 0;

        if (!$admin_exists) {
            die("Тестовый пользователь не найден. Запусти SQL для создания тестовых данных.");
        }

        return true;
    } catch (PDOException $e) {
        die("Ошибка тестирования БД: " . $e->getMessage());
    }
}

// Автозагрузка функций
require_once INCLUDES_PATH . '/auth_functions.php';
require_once INCLUDES_PATH . '/db_functions.php';
require_once INCLUDES_PATH . '/helpers.php';

// Проверка авторизации (пропускаем для test_db.php и публичных страниц)
$public_pages = ['login.php', 'logout.php', 'index.php', 'test_db.php'];
$current_page = basename($_SERVER['PHP_SELF']);

// Пропускаем проверку для test_db.php
if ($current_page === 'test_db.php') {
    // Ничего не делаем
} elseif (!in_array($current_page, $public_pages) && !is_admin_logged_in()) {
    header('Location: ../login.php');
    exit;
}

// Установка информации о текущем пользователе
if (is_admin_logged_in()) {
    $current_admin = get_current_admin();
}

// Функция для отладки
function debug($data, $exit = true) {
    if (DEBUG_MODE) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        if ($exit) exit;
    }
}
?>