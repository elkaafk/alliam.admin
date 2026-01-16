<?php
/**
 * Функции для работы с базой данных
 */

/**
 * Получение списка клиентов
 */
function get_clients($filters = [], $limit = 50, $offset = 0) {
    $db = get_db_connection();

    $where = [];
    $params = [];

    // Фильтр по статусу
    if (isset($filters['status'])) {
        $where[] = "u.is_active = ?";
        $params[] = $filters['status'] === 'active' ? 1 : 0;
    }

    // Фильтр по дате регистрации
    if (isset($filters['date_from'])) {
        $where[] = "u.created_at >= ?";
        $params[] = $filters['date_from'];
    }

    if (isset($filters['date_to'])) {
        $where[] = "u.created_at <= ?";
        $params[] = $filters['date_to'];
    }

    // Поиск по ФИО или телефону
    if (isset($filters['search']) && !empty($filters['search'])) {
        $search = "%{$filters['search']}%";
        $where[] = "(u.last_name LIKE ? OR u.first_name LIKE ? OR u.phone LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }

    // Фильтр по назначенному юристу
    if (isset($filters['assigned_to'])) {
        $where[] = "ca.admin_id = ?";
        $params[] = $filters['assigned_to'];
    }

    $where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    $query = "
        SELECT 
            u.id,
            u.phone,
            u.email,
            CONCAT_WS(' ', u.last_name, u.first_name, u.middle_name) as full_name,
            u.birth_date,
            u.created_at,
            u.last_login,
            u.is_active,
            ca.admin_id as assigned_to,
            au.full_name as assigned_lawyer,
            COUNT(DISTINCT am.id) as message_count,
            MAX(am.created_at) as last_message_date
        FROM users u
        LEFT JOIN client_assignments ca ON u.id = ca.client_id AND ca.assignment_type = 'primary'
        LEFT JOIN admin_users au ON ca.admin_id = au.id
        LEFT JOIN admin_messages am ON u.id = am.client_id
        $where_sql
        GROUP BY u.id
        ORDER BY u.created_at DESC
        LIMIT ? OFFSET ?
    ";

    $params[] = $limit;
    $params[] = $offset;

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $clients = $stmt->fetchAll();

    // Получаем общее количество для пагинации
    $count_query = "
        SELECT COUNT(DISTINCT u.id) as total
        FROM users u
        LEFT JOIN client_assignments ca ON u.id = ca.client_id
        $where_sql
    ";

    $count_stmt = $db->prepare(preg_replace('/LIMIT \? OFFSET \?/', '', $count_query));
    $count_params = array_slice($params, 0, count($params) - 2);
    $count_stmt->execute($count_params);
    $total = $count_stmt->fetch()['total'];

    return [
        'clients' => $clients,
        'total' => $total
    ];
}

/**
 * Получение информации о клиенте
 */
function get_client($client_id) {
    $db = get_db_connection();

    $stmt = $db->prepare("
        SELECT 
            u.*,
            ca.admin_id as assigned_lawyer_id,
            au.full_name as assigned_lawyer_name,
            au.role as assigned_lawyer_role
        FROM users u
        LEFT JOIN client_assignments ca ON u.id = ca.client_id AND ca.assignment_type = 'primary'
        LEFT JOIN admin_users au ON ca.admin_id = au.id
        WHERE u.id = ?
    ");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch();

    if (!$client) {
        return null;
    }

    // Получаем этап банкротства
    $stage_stmt = $db->prepare("
        SELECT stage_key, stage_name 
        FROM bankruptcy_stages 
        WHERE is_active = 1 
        ORDER BY stage_order ASC
    ");
    $stage_stmt->execute();
    $stages = $stage_stmt->fetchAll();

    // TODO: Здесь будет логика определения текущего этапа клиента

    return [
        'client' => $client,
        'stages' => $stages
    ];
}

/**
 * Получение сообщений чата с клиентом
 */
function get_client_messages($client_id, $limit = 100, $offset = 0) {
    $db = get_db_connection();

    $stmt = $db->prepare("
        SELECT 
            m.*,
            CASE 
                WHEN m.admin_id IS NOT NULL THEN 'admin'
                ELSE 'client'
            END as sender_type,
            au.full_name as admin_name,
            au.avatar as admin_avatar
        FROM admin_messages m
        LEFT JOIN admin_users au ON m.admin_id = au.id
        WHERE m.client_id = ?
        ORDER BY m.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$client_id, $limit, $offset]);
    $messages = $stmt->fetchAll();

    // Помечаем сообщения как прочитанные
    $update_stmt = $db->prepare("
        UPDATE admin_messages 
        SET is_read = 1, read_at = NOW() 
        WHERE client_id = ? AND admin_id IS NULL AND is_read = 0
    ");
    $update_stmt->execute([$client_id]);

    return array_reverse($messages); // Переворачиваем для отображения от старых к новым
}

/**
 * Отправка сообщения клиенту
 */
function send_message_to_client($client_id, $admin_id, $content, $attachments = []) {
    $db = get_db_connection();

    $stmt = $db->prepare("
        INSERT INTO admin_messages 
        (client_id, admin_id, message_type, content, attachments) 
        VALUES (?, ?, 'text', ?, ?)
    ");

    $attachments_json = !empty($attachments) ? json_encode($attachments, JSON_UNESCAPED_UNICODE) : null;

    return $stmt->execute([$client_id, $admin_id, $content, $attachments_json]);
}

/**
 * Получение списка администраторов по роли
 */
function get_admins_by_role($role = null) {
    $db = get_db_connection();

    $query = "SELECT id, full_name, role, email, phone FROM admin_users WHERE is_active = 1";
    $params = [];

    if ($role) {
        $query .= " AND role = ?";
        $params[] = $role;
    }

    $query .= " ORDER BY full_name ASC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

/**
 * Назначение клиента юристу
 */
function assign_client_to_lawyer($client_id, $admin_id, $assigned_by, $notes = '') {
    $db = get_db_connection();

    // Проверяем, есть ли уже назначение
    $check_stmt = $db->prepare("
        SELECT id FROM client_assignments 
        WHERE client_id = ? AND assignment_type = 'primary'
    ");
    $check_stmt->execute([$client_id]);

    if ($check_stmt->fetch()) {
        // Обновляем существующее назначение
        $stmt = $db->prepare("
            UPDATE client_assignments 
            SET admin_id = ?, assigned_by = ?, notes = ?, assigned_at = NOW() 
            WHERE client_id = ? AND assignment_type = 'primary'
        ");
        return $stmt->execute([$admin_id, $assigned_by, $notes, $client_id]);
    } else {
        // Создаем новое назначение
        $stmt = $db->prepare("
            INSERT INTO client_assignments 
            (client_id, admin_id, assignment_type, assigned_by, notes) 
            VALUES (?, ?, 'primary', ?, ?)
        ");
        return $stmt->execute([$client_id, $admin_id, $assigned_by, $notes]);
    }
}
?>