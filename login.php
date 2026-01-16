<?php
/**
 * Страница входа в админ-панель
 */
require_once 'includes/config.php';

// Если уже авторизован - редирект на дашборд
if (is_admin_logged_in()) {
    header('Location: dashboard/');
    exit;
}

$error = '';
$username = '';

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Пожалуйста, заполните все поля';
    } elseif (admin_login($username, $password)) {
        header('Location: dashboard/');
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}
?>
<!DOCTYPE html>
<html lang="ru" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админ-панель | Alliam.Online</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link href="assets/css/auth.css" rel="stylesheet">
</head>
<body class="auth-body">
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <?php if (file_exists('assets/img/logo.png')): ?>
                <img src="assets/img/logo.png" alt="Alliam.Online" class="auth-logo">
            <?php else: ?>
                <div class="auth-logo-container">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 22px; margin: 0 auto;">
                        A
                    </div>
                </div>
            <?php endif; ?>
            <h1 class="auth-title">Админ-панель</h1>

        </div>

        <div class="auth-body-container">
            <?php if ($error): ?>
                <div class="auth-error">
                    <i class="bi bi-exclamation-triangle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form" id="loginForm" novalidate>
                <div class="mb-4">
                    <label for="username" class="form-label">
                        <i class="bi bi-person-circle me-1"></i> Логин или Email
                    </label>
                    <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person"></i>
                            </span>
                        <input type="text"
                               class="form-control"
                               id="username"
                               name="username"
                               value="<?= htmlspecialchars($username) ?>"
                               required
                               autofocus
                               placeholder="Введите логин или email"
                               autocomplete="username">
                    </div>

                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="bi bi-shield-lock me-1"></i> Пароль
                    </label>
                    <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                        <input type="password"
                               class="form-control"
                               id="password"
                               name="password"
                               required
                               placeholder="Введите пароль"
                               autocomplete="current-password">
                        <button class="btn password-toggle" type="button" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                </div>

                <div class="mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Запомнить меня на этом устройстве
                        </label>
                    </div>
                </div>

                <div class="d-grid gap-2 mb-4">
                    <button type="submit" class="btn btn-auth" id="submitBtn">
                            <span id="submitText">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Войти в систему
                            </span>
                        <span id="submitLoading" style="display: none;">
                                <span class="auth-spinner"></span> Вход...
                            </span>
                    </button>
                </div>




            </form>
            <small class="text-muted">
                &copy; <?= date('Y') ?> Alliam.Online. Все права защищены.<br>
                <span class="small">Версия 1.0.0</span>
            </small>
        </div>


    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Переключение видимости пароля
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
            this.setAttribute('title', 'Скрыть пароль');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
            this.setAttribute('title', 'Показать пароль');
        }
    });

    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        // Фокус на поле ввода логина
        const usernameField = document.getElementById('username');
        if (usernameField && !usernameField.value) {
            usernameField.focus();
        }

        // Добавляем title для кнопки переключения пароля
        document.getElementById('togglePassword').setAttribute('title', 'Показать пароль');

        // Обработка формы на Enter
        document.getElementById('loginForm').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                document.getElementById('submitBtn').click();
            }
        });

        // === РАСКОММЕНТИРУЙ ДЛЯ ТЕСТИРОВАНИЯ ===
        // Автоматическое заполнение для тестирования
        document.getElementById('username').value = 'superadmin';
        document.getElementById('password').value = 'Admin123!';
        // ======================================
    });

    // Валидация формы перед отправкой
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitLoading = document.getElementById('submitLoading');

        if (!username || !password) {
            e.preventDefault();
            alert('Пожалуйста, заполните все поля');
            return false;
        }

        // Блокируем кнопку и показываем индикатор загрузки
        submitBtn.disabled = true;
        submitText.style.display = 'none';
        submitLoading.style.display = 'inline';

        return true;
    });

    // Проверка поддержки современных возможностей браузера
    window.addEventListener('load', function() {
        if (!('Promise' in window)) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'auth-error';
            errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Ваш браузер устарел. Пожалуйста, обновите его для корректной работы.';
            document.querySelector('.auth-body-container').prepend(errorDiv);
        }
    });
</script>
</body>
</html>