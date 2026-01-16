<?php
/**
 * Подвал админ-панели
 */
?>
<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables (оставляем, пригодится для таблиц) -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<!-- Custom JS -->
<script src="<?= BASE_URL ?>assets/js/main.js"></script>

<script>
    $(document).ready(function() {
        // Инициализация DataTables для таблиц с классом datatable
        if ($('.datatable').length) {
            $('.datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json'
                },
                pageLength: 25,
                responsive: true,
                order: [],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
            });
        }

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
    });
</script>
</body>
</html>