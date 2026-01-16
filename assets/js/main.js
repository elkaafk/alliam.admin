/**
 * Основные JavaScript функции админ-панели
 */

/**
 * Показать уведомление
 */
function showNotification(message, type = 'info', duration = 5000) {
    const notificationArea = document.getElementById('notificationArea');

    if (!notificationArea) {
        console.warn('Notification area not found');
        return;
    }

    const alertTypes = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    };

    const alertClass = alertTypes[type] || 'alert-info';

    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    notificationArea.appendChild(notification);

    // Автоматическое скрытие
    if (duration > 0) {
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, duration);
    }

    return notification;
}

/**
 * Загрузка контента через AJAX
 */
function loadContent(url, containerId, callback) {
    const container = document.getElementById(containerId);

    if (!container) {
        console.error(`Container #${containerId} not found`);
        return;
    }

    container.classList.add('loading');

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            container.innerHTML = html;
            container.classList.remove('loading');

            if (typeof callback === 'function') {
                callback();
            }
        })
        .catch(error => {
            console.error('Error loading content:', error);
            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Ошибка загрузки контента: ${error.message}
                </div>
            `;
            container.classList.remove('loading');
        });
}

/**
 * Форматирование даты
 */
function formatDate(dateString, format = 'datetime') {
    const date = new Date(dateString);

    if (isNaN(date.getTime())) {
        return 'Не указано';
    }

    const formats = {
        'date': {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        },
        'time': {
            hour: '2-digit',
            minute: '2-digit'
        },
        'datetime': {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }
    };

    const options = formats[format] || formats.datetime;
    return date.toLocaleDateString('ru-RU', options);
}

/**
 * Форматирование номера телефона
 */
function formatPhone(phone) {
    if (!phone) return '';

    // Убираем все нецифровые символы
    const cleaned = phone.replace(/\D/g, '');

    // Проверяем длину и форматируем
    if (cleaned.length === 11 && cleaned.startsWith('7') || cleaned.startsWith('8')) {
        return `+7 ${cleaned.substring(1, 4)} ${cleaned.substring(4, 7)}-${cleaned.substring(7, 9)}-${cleaned.substring(9, 11)}`;
    } else if (cleaned.length === 10) {
        return `+7 ${cleaned.substring(0, 3)} ${cleaned.substring(3, 6)}-${cleaned.substring(6, 8)}-${cleaned.substring(8, 10)}`;
    }

    return phone;
}

/**
 * Подтверждение действия
 */
function confirmAction(message, callback) {
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    const messageElement = document.getElementById('confirmMessage');
    const actionButton = document.getElementById('confirmAction');

    if (!messageElement || !actionButton) {
        console.error('Confirm modal elements not found');
        if (typeof callback === 'function') {
            callback();
        }
        return;
    }

    messageElement.textContent = message || 'Вы уверены?';

    // Очищаем предыдущие обработчики
    actionButton.replaceWith(actionButton.cloneNode(true));
    const newActionButton = document.getElementById('confirmAction');

    newActionButton.addEventListener('click', function() {
        modal.hide();
        if (typeof callback === 'function') {
            callback();
        }
    });

    modal.show();
}

/**
 * Копирование в буфер обмена
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Скопировано в буфер обмена', 'success', 2000);
    }).catch(err => {
        console.error('Ошибка копирования:', err);
        showNotification('Ошибка копирования', 'error');
    });
}

/**
 * Обрезка текста с многоточием
 */
function truncateText(text, maxLength = 100) {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

/**
 * Проверка email
 */
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Форматирование суммы
 */
function formatAmount(amount, currency = '₽') {
    return new Intl.NumberFormat('ru-RU', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount) + ' ' + currency;
}

/**
 * Загрузка файла
 */
function downloadFile(url, filename) {
    const link = document.createElement('a');
    link.href = url;
    link.download = filename || 'download';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Инициализация всех компонентов при загрузке страницы
 */
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация тултипов
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Инициализация поповеров
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Обработка форм с подтверждением
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });

    // Автоматическая фокусировка на первых полях форм
    document.querySelectorAll('form').forEach(form => {
        const firstInput = form.querySelector('input[type="text"], input[type="email"], input[type="password"]');
        if (firstInput && !firstInput.value) {
            firstInput.focus();
        }
    });
});

/**
 * Экспорт данных в CSV
 */
function exportToCSV(data, filename) {
    if (!data || !data.length) {
        showNotification('Нет данных для экспорта', 'warning');
        return;
    }

    const headers = Object.keys(data[0]);
    const csvContent = [
        headers.join(','),
        ...data.map(row =>
            headers.map(header =>
                JSON.stringify(row[header] || '')
            ).join(',')
        )
    ].join('\n');

    const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    downloadFile(url, filename || 'export.csv');
}

/**
 * Получение CSRF токена
 */
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

/**
 * AJAX запрос с обработкой ошибок
 */
function ajaxRequest(url, options = {}) {
    const defaults = {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    };

    const config = { ...defaults, ...options };

    return fetch(url, config)
        .then(async response => {
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }

            return data;
        });
}