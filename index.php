asdasd<?php
/**
 * Главная точка входа - редирект на dashboard или login
 */
require_once 'includes/config.php';

if (is_admin_logged_in()) {
    header('Location: dashboard/');
} else {
    header('Location: login.php');
}
exit;