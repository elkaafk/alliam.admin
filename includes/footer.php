<?php
/**
 * Подвал админ-панели
 */
?>
</main>
</div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Custom JS -->
<script src="<?= BASE_URL ?>assets/js/main.js"></script>

<!-- Инициализация DataTables -->
<script>
    $(document).ready(function() {
        // Инициализация всех таблиц с классом datatable
        $('.datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json'
            },
            pageLength: 25,
            responsive: true
        });

        // Подтверждение действий
        $('[data-confirm]').on('click', function(e) {
            e.preventDefault();
            var message = $(this).data('confirm') || 'Вы уверены?';
            var href = $(this).attr('href');

            $('#confirmMessage').text(message);
            $('#confirmAction').off('click').on('click', function() {
                window.location.href = href;
            });

            new bootstrap.Modal(document.getElementById('confirmModal')).show();
        });

        // Автоматическое скрытие алертов через 5 секунд
        setTimeout(function() {
            $('.alert:not(.alert-permanent)').fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);

        // Тогглер темы (светлая/темная)
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function() {
                const currentTheme = document.documentElement.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('admin-theme', newTheme);
            });

            // Восстановление темы
            const savedTheme = localStorage.getItem('admin-theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-bs-theme', savedTheme);
            }
        }
    });
</script>
</body>
</html>