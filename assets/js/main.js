/**
 * Основные JavaScript функции админ-панели
 */

// Показать уведомление
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

// Подтверждение действия
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

// Форматирование даты
function formatDate(dateString, format = 'datetime') {
    const date = new Date(dateString);

    if (isNaN(date.getTime())) {
        return 'Не указано';
    }

    const options = {
        'date': { day: '2-digit', month: '2-digit', year: 'numeric' },
        'time': { hour: '2-digit', minute: '2-digit' },
        'datetime': {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }
    };

    const formatOptions = options[format] || options.datetime;
    return date.toLocaleDateString('ru-RU', formatOptions);
}

// Форматирование телефона
function formatPhone(phone) {
    if (!phone) return '';

    // Убираем все нецифровые символы
    const cleaned = phone.replace(/\D/g, '');

    // Проверяем длину и форматируем
    if (cleaned.length === 11 && (cleaned.startsWith('7') || cleaned.startsWith('8'))) {
        return `+7 ${cleaned.substring(1, 4)} ${cleaned.substring(4, 7)}-${cleaned.substring(7, 9)}-${cleaned.substring(9, 11)}`;
    } else if (cleaned.length === 10) {
        return `+7 ${cleaned.substring(0, 3)} ${cleaned.substring(3, 6)}-${cleaned.substring(6, 8)}-${cleaned.substring(8, 10)}`;
    }

    return phone;
}

// Копирование в буфер обмена
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Скопировано в буфер обмена', 'success', 2000);
    }).catch(err => {
        console.error('Ошибка копирования:', err);
        showNotification('Ошибка копирования', 'error');
    });
}

// Обрезка текста с многоточием
function truncateText(text, maxLength = 100) {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

// Инициализация при загрузке
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

    // Автоматическое скрытие алертов через 5 секунд
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
            alert.classList.add('fade');
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 500);
        });
    }, 5000);

    // Автоматическая фокусировка на первых полях форм
    document.querySelectorAll('form').forEach(form => {
        const firstInput = form.querySelector('input[type="text"], input[type="email"], input[type="password"]');
        if (firstInput && !firstInput.value) {
            firstInput.focus();
        }
    });
});





    /**
    * Менеджер уведомлений
    * Управляет отображением нескольких уведомлений без перекрытия
    */
    window.NotificationManager = {
    notifications: [],
    container: null,
    defaultPosition: 'top-right',
    notificationGap: 10, // Расстояние между уведомлениями

    /**
     * Инициализация менеджера
     */
    init: function() {
    // Создаем контейнер для уведомлений
    this.container = document.createElement('div');
    this.container.id = 'notifications-container';
    this.container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: ${this.notificationGap}px;
                max-width: 400px;
                pointer-events: none;
            `;
    document.body.appendChild(this.container);

    // Стили для адаптивности
    this.addStyles();
},

    /**
     * Добавление стилей
     */
    addStyles: function() {
    const style = document.createElement('style');
    style.textContent = `
                .notification-item {
                    position: relative;
                    min-width: 300px;
                    max-width: 400px;
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    overflow: hidden;
                    pointer-events: auto;
                    transform: translateY(0);
                }

                .notification-item.new {
                    animation: slideInRight 0.4s ease;
                }

                .notification-item.exiting {
                    animation: slideOutRight 0.2s ease forwards;
                }

                @keyframes slideInRight {
                    from {
                        opacity: 0;
                        transform: translateX(100%);
                    }
                    to {
                        opacity: 1;
                        transform: translateX(0);
                    }
                }

                @keyframes slideOutRight {
                    from {
                        opacity: 1;
                        transform: translateX(0);
                    }
                    to {
                        opacity: 0;
                        transform: translateX(100%);
                    }
                }

                .notification-header {
                    display: flex;
                    align-items: center;
                    padding: 12px 16px;
                    border-bottom: 1px solid rgba(0,0,0,0.1);
                }

                .notification-icon {
                    margin-right: 12px;
                    font-size: 1.2rem;
                }

                .notification-title {
                    font-weight: 600;
                    flex-grow: 1;
                    margin: 0;
                    font-size: 0.95rem;
                }

                .notification-close {
                    background: none;
                    border: none;
                    padding: 4px;
                    cursor: pointer;
                    opacity: 0.6;
                    transition: opacity 0.2s;
                }

                .notification-close:hover {
                    opacity: 1;
                }

                .notification-body {
                    padding: 16px;
                    font-size: 0.9rem;
                    line-height: 1.4;
                }

                .notification-success {
                    border-left: 4px solid #10b981;
                }

                .notification-error {
                    border-left: 4px solid #dc2626;
                }

                .notification-warning {
                    border-left: 4px solid #f59e0b;
                }

                .notification-info {
                    border-left: 4px solid #3b82f6;
                }

                .notification-progress {
                    height: 3px;
                    background: rgba(0,0,0,0.1);
                    width: 100%;
                    position: absolute;
                    bottom: 0;
                    left: 0;
                }

                .notification-progress-bar {
                    height: 100%;
                    width: 100%;
                    transition: width linear;
                }

                /* Адаптивность */
                @media (max-width: 576px) {
                    #notifications-container {
                        left: 10px;
                        right: 10px;
                        top: 10px;
                        max-width: none;
                    }

                    .notification-item {
                        min-width: auto;
                        max-width: none;
                        width: 100%;
                    }
                }
            `;
    document.head.appendChild(style);
},

    /**
     * Показать уведомление
     */
    show: function(message, type = 'info', duration = 5000, options = {}) {
    // Инициализируем если нужно
    if (!this.container) {
    this.init();
}

    // Настройки
    const config = {
    title: options.title || this.getTitleByType(type),
    icon: options.icon || this.getIconByType(type),
    position: options.position || this.defaultPosition,
    onClose: options.onClose || null,
    onClick: options.onClick || null,
    duration: duration
};

    // Создаем ID уведомления
    const notificationId = 'notification-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);

    // Рассчитываем позицию создания элемента
    const totalHeight = this.calculateTotalHeight();

    // Создаем DOM элемент
    const notificationEl = this.createNotificationElement(notificationId, message, type, config, duration);

    // Добавляем класс для анимации появления
    notificationEl.classList.add('new');

    // Устанавливаем позицию сразу ДО добавления в DOM
    notificationEl.style.transform = `translateY(0px)`;

    // Добавляем в контейнер
    this.container.appendChild(notificationEl);

    // Сохраняем в массив
    const notification = {
    id: notificationId,
    element: notificationEl,
    type: type,
    duration: duration,
    config: config,
    createdAt: Date.now(),
    position: totalHeight
};

    this.notifications.push(notification);

    // После того как элемент отрисовался, убираем класс анимации
    setTimeout(() => {
    notificationEl.classList.remove('new');
}, 300);

    // Настраиваем автоскрытие
    if (duration > 0) {
    this.setupAutoClose(notificationId, duration);
}

    return notificationId;
},

    /**
     * Рассчитать общую высоту всех уведомлений (используем записанные высоты)
     */
    calculateTotalHeight: function() {
    let totalHeight = 0;

    // Используем сохраненные высоты из элементов которые уже отрисованы
    for (let i = 0; i < this.notifications.length; i++) {
    const notification = this.notifications[i];
    if (notification.element && notification.element.offsetHeight > 0) {
    totalHeight += notification.element.offsetHeight + this.notificationGap;
}
}

    return totalHeight;
},

    /**
     * Создание DOM элемента уведомления
     */
    createNotificationElement: function(id, message, type, config, duration) {
    const el = document.createElement('div');
    el.id = id;
    el.className = `notification-item notification-${type}`;

    // Фиксируем высоту для предотвращения скачков
    el.style.height = 'auto';
    el.style.minHeight = '50px'; // Примерная минимальная высота

    // Обработчик клика
    if (config.onClick) {
    el.style.cursor = 'pointer';
    el.addEventListener('click', config.onClick);
}

    // Шаблон уведомления
    el.innerHTML = `
                <div class="notification-header">
                    <div class="notification-icon">
                        <i class="bi ${config.icon}"></i>
                    </div>
                    <div class="notification-title">${message}</div>
                    <button class="notification-close" aria-label="Закрыть">
                        <i class="bi bi-x"></i>
                    </button>
                </div>

                ${duration > 0 ? '<div class="notification-progress"><div class="notification-progress-bar" style="background: rgba(0,0,0,0.1);"></div></div>' : ''}
            `;

    // Обработчик закрытия
    const closeBtn = el.querySelector('.notification-close');
    closeBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    this.close(id);
});

    return el;
},

    /**
     * Настройка автоскрытия
     */
    setupAutoClose: function(notificationId, duration) {
    const notification = this.notifications.find(n => n.id === notificationId);
    if (!notification || !notification.element) return;

    // Полоса прогресса
    const progressBar = notification.element.querySelector('.notification-progress-bar');
    if (progressBar) {
    progressBar.style.transition = `width ${duration}ms linear`;
    progressBar.style.width = '0%';

    // Запускаем анимацию прогресса после небольшой задержки
    setTimeout(() => {
    if (progressBar && progressBar.parentNode) {
    progressBar.style.width = '100%';
}
}, 50);
}

    // Таймер закрытия
    notification.closeTimeout = setTimeout(() => {
    this.close(notificationId);
}, duration);
},

    /**
     * Закрыть уведомление
     */
    close: function(notificationId) {
    const index = this.notifications.findIndex(n => n.id === notificationId);
    if (index === -1) return;

    const notification = this.notifications[index];

    // Очищаем таймер
    if (notification.closeTimeout) {
    clearTimeout(notification.closeTimeout);
}

    // Анимация закрытия
    if (notification.element) {
    notification.element.classList.add('exiting');

    // Удаляем после анимации
    setTimeout(() => {
    if (notification.element && notification.element.parentNode) {
    notification.element.parentNode.removeChild(notification.element);
}

    // Удаляем из массива
    this.notifications.splice(index, 1);

    // Вызываем callback если есть
    if (notification.config.onClose) {
    notification.config.onClose();
}

    // Обновляем позиции оставшихся уведомлений БЕЗ АНИМАЦИИ
    this.updateNotificationsPositions();

}, 300);
} else {
    // Если элемент уже удален
    this.notifications.splice(index, 1);
    this.updateNotificationsPositions();
}
},

    /**
     * Обновление позиций всех уведомлений (после удаления)
     */
    updateNotificationsPositions: function() {
    let currentPosition = 0;

    // Проходим по всем уведомлениям и устанавливаем новые позиции
    this.notifications.forEach(notification => {
    if (notification.element && notification.element.parentNode) {
    // Устанавливаем новую позицию сразу (без анимации)
    notification.element.style.transform = `translateY(0px)`;
    notification.position = currentPosition;
    currentPosition += notification.element.offsetHeight + this.notificationGap;
}
});
},

    /**
     * Закрыть все уведомления
     */
    closeAll: function() {
    // Копируем массив чтобы избежать проблем с индексами при удалении
    const notificationsToClose = [...this.notifications];

    notificationsToClose.forEach(notification => {
    this.close(notification.id);
});
},

    /**
     * Получить заголовок по типу
     */
    getTitleByType: function(type) {
    const titles = {
    'success': 'Успешно',
    'error': 'Ошибка',
    'warning': 'Внимание',
    'info': 'Информация'
};
    return titles[type] || 'Уведомление';
},

    /**
     * Получить иконку по типу
     */
    getIconByType: function(type) {
    const icons = {
    'success': 'bi-check-circle-fill',
    'error': 'bi-exclamation-circle-fill',
    'warning': 'bi-exclamation-triangle-fill',
    'info': 'bi-info-circle-fill'
};
    return icons[type] || 'bi-info-circle-fill';
},

    /**
     * Получить количество активных уведомлений
     */
    getCount: function() {
    return this.notifications.length;
}
};

    /**
    * Универсальная функция для показа уведомлений (обратная совместимость)
    */
    function showNotification(message, type = 'info', duration = 5000, position = 'top-right') {
    return window.NotificationManager.show(message, type, duration, { position: position });
}

    /**
    * Быстрые методы для разных типов уведомлений
    */
    window.notify = {
    success: (message, duration = 5000, options = {}) =>
    window.NotificationManager.show(message, 'success', duration, options),

    error: (message, duration = 5000, options = {}) =>
    window.NotificationManager.show(message, 'error', duration, options),

    warning: (message, duration = 5000, options = {}) =>
    window.NotificationManager.show(message, 'warning', duration, options),

    info: (message, duration = 5000, options = {}) =>
    window.NotificationManager.show(message, 'info', duration, options),

    close: (id) => window.NotificationManager.close(id),

    closeAll: () => window.NotificationManager.closeAll(),

    count: () => window.NotificationManager.getCount()
};

    // Автоскрытие уведомлений (старых alert)
    document.addEventListener('DOMContentLoaded', function() {
    // Автоскрытие статических alert через 5 секунд
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent):not(.notification-item)');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Активация тултипов
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl, {
    trigger: 'hover focus'
});
});

    // Подсветка активной ссылки в сайдбаре
    const currentPage = '<?php echo basename($_SERVER["PHP_SELF"]); ?>';
    document.querySelectorAll('.sidebar a.nav-link').forEach(link => {
    if (link.getAttribute('href') === currentPage ||
    link.getAttribute('href').includes(currentPage.replace('.php', ''))) {
    link.classList.add('active');
}
});

    // Инициализация popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
});

    // Тестовое уведомление для проверки работы
    setTimeout(function() {
    if (window.notify && window.notify.info) {
    /*window.notify.info('Система уведомлений - информационное сообщение', 10000);
     window.notify.success('Система уведомлений - успешное сообщение', 8000);
     window.notify.error('Система уведомлений - ошибка сообщение', 6000);
     window.notify.warning('Система уведомлений - предупреждение сообщение', 5000);*/
}
}, 1000);
});


