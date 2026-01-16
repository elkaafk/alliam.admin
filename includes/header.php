<?php
/**
 * Шапка админ-панели
 */
if (!isset($page_title)) {
    $page_title = 'Админ-панель';
}
?>
    <!DOCTYPE html>
    <html lang="ru" data-bs-theme="light">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($page_title) ?> | Alliam.Online Admin</title>

        <!-- Bootstrap 5 -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

        <!-- DataTables -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

        <!-- Custom CSS -->
        <link href="<?= BASE_URL ?>assets/css/main.css" rel="stylesheet">

        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>assets/img/favicon.ico">

        <!-- Scripts в head для некоторых библиотек -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </head>
<body>
    <!-- Модальное окно для подтверждения действий -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Подтверждение</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmMessage">Вы уверены?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-danger" id="confirmAction">Подтвердить</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Уведомления -->
    <div id="notificationArea" class="position-fixed top-0 end-0 p-3" style="z-index: 9999"></div>

    <!-- Основной контейнер -->
<div class="container-fluid">
    <div class="row">
    <!-- Sidebar будет подключаться отдельно -->
<?php if (!isset($hide_sidebar) || !$hide_sidebar): ?>
    <div class="col-md-3 col-lg-2 px-0">
        <?php include INCLUDES_PATH . '/sidebar.php'; ?>
    </div>
    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4">
<?php else: ?>
    <main class="col-12 px-0">
<?php endif; ?>