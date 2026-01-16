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

// Установка заголовка страницы
$page_title = 'Вход в админ-панель';
$hide_sidebar = true;
?>
<?php include 'includes/header.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <div class="d-flex justify-content-center align-items-center">
                            <img src="<?= BASE_URL ?>assets/img/logo.png" alt="Alliam.Online" height="40" class="me-2">
                            <h4 class="mb-0">Админ-панель</h4>
                        </div>
                        <p class="mb-0 mt-2 small opacity-75">Только для сотрудников</p>
                    </div>

                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="loginForm">
                            <div class="mb-3">
                                <label for="username" class="form-label">Логин или Email</label>
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
                                           placeholder="Введите логин или email">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Пароль</label>
                                <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                    <input type="password"
                                           class="form-control"
                                           id="password"
                                           name="password"
                                           required
                                           placeholder="Введите пароль">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Запомнить меня</label>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Войти
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="text-muted small mb-2">Проблемы со входом?</p>
                            <a href="#" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-key me-1"></i> Забыли пароль?
                            </a>
                        </div>
                    </div>

                    <div class="card-footer text-center py-3">
                        <small class="text-muted">
                            &copy; <?= date('Y') ?> Alliam.Online. Все права защищены.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .card {
            border-radius: 15px;
            overflow: hidden;
        }
    </style>

    <script>
        // Переключение видимости пароля
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });

        // Фокус на поле ввода при загрузке
        document.getElementById('username').focus();

        // Обработка формы на Enter
        document.getElementById('loginForm').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                if (e.target.type !== 'submit') {
                    document.getElementById('loginForm').submit();
                }
            }
        });
    </script>

<?php include 'includes/footer.php'; ?>