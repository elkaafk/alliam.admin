<?php
/**
 * Выход из системы
 */
require_once 'includes/config.php';

admin_logout();

// Редирект на страницу входа
header('Location: login.php');
exit;