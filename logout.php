<?php
/**
 * Выход из системы
 */

// Стартуем сессию В САМОМ НАЧАЛЕ, до любого вывода
session_start();

// Подключаем конфиг БЕЗ вывода
require_once 'includes/config.php';

// Вызываем функцию выхода
admin_logout();

// Редирект на страницу входа
header('Location: login.php');
exit;
?>