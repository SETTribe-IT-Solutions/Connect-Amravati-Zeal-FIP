<?php
/**
 * Task Tracking Page — Admin Panel
 * Amravati Connect | Government Workflow Platform
 *
 * Features:
 *  • Search tasks by Task ID (task_no e.g. TASK_001) or Task Name
 *  • View complete task details with assigned user, department, dates, priority
 *  • Full journey timeline merging ALL activity sources:
 *      - task_status_history  (old_status → new_status changes)
 *      - task_activity_logs   (activity_type, description, activity_time)
 *      - task_escalations     (escalation events with reason & level)
 *      - task_assignments     (assignment/reassignment history)
 *      - task_remarks         (remarks added by users)
 *      - task_tracking        (manual admin tracking entries)
 *  • Admin can manually add a new tracking/status entry
 *  • Overdue detection
 *  • Dark-mode + responsive sidebar layout
 */

session_start();
require_once 'include/dbConfig.php';

/* ─── Map login session keys to dashboard variables ────────────────── */
if (isset($_SESSION['role_name'])) {
    $_SESSION['user_role']       = $_SESSION['role_name'];
    $_SESSION['user_name']       = $_SESSION['full_name'];
    $_SESSION['user_taluka_id']  = $_SESSION['taluka_id'] ?? 1;
    $_SESSION['user_village_id'] = $_SESSION['village_id'] ?? 1;
}

/* ─── Session defaults (dev preview) ───────────────────────── */
if (empty($_SESSION['user_role'])) {
    $_SESSION['user_role']       = 'Collector';
    $_SESSION['user_name']       = 'Hon. Collector';
    $_SESSION['user_taluka_id']  = 1;
    $_SESSION['user_village_id'] = 1;
}

$sRole      = $_SESSION['user_role'];
$sName      = $_SESSION['user_name'];

/* Avatar initials */
$parts    = array_filter(explode(' ', trim($sName)));
$first    = $parts[0] ?? 'U';
$second   = isset($parts[1]) ? $parts[1] : '';
$initials = strtoupper(substr($first, 0, 1) . substr($second, 0, 1));


// ═══════════════════════════════════════════════════════════════════
// AJAX: Return task timeline as JSON (for modal panel)
// Called via: task_tracking.php?ajax=timeline&task_id=123
// ═══════════════════════════════════════════════════════════════════
if (isset($_GET['ajax']) && $_GET['ajax'] === 'timeline') {
    header('Content-Type: application/json; charset=utf-8');
    $tid = (int)($_GET['task_id'] ?? 0);
    if ($tid <= 0) { echo json_encode(['error' => 'Invalid task ID']); exit; }

    // ── Task details ─────────────────────────────────────────────
    $task_r = $conn->query("
        SELECT t.*,
               ua.full_name AS assigned_employee, ua.designation AS assigned_designation,
               r.role_name, d.department_name, uc.full_name AS created_by_name
          FROM tasks t
          LEFT JOIN users ua       ON ua.user_id  = t.assigned_user_id
          LEFT JOIN roles r        ON r.role_id   = t.assigned_role_id
          LEFT JOIN departments d  ON d.department_id = t.department_id
          LEFT JOIN users uc       ON uc.user_id   = t.created_by
         WHERE t.task_id = $tid LIMIT 1");
    $tsk = ($task_r && $task_r->num_rows > 0) ? $task_r->fetch_assoc() : null;
    if (!$tsk) { echo json_encode(['error' => 'Task not found']); exit; }

    // ── Documents ────────────────────────────────────────────────
    $docs = [];
    $dr = $conn->query("
        SELECT td.*, u.full_name AS uploaded_by_name
          FROM task_documents td
          LEFT JOIN users u ON u.user_id = td.uploaded_by
         WHERE td.task_id = $tid ORDER BY td.uploaded_at DESC");
    if ($dr) while ($row = $dr->fetch_assoc()) $docs[] = $row;

    // ── Build unified timeline events ────────────────────────────
    $events = [];

    // 1. Created
    $events[] = [
        'event_type'    => 'created',
        'title'         => 'Task Created',
        'description'   => '"' . ($tsk['task_title'] ?? '') . '" was created and added to the system.',
        'old_status'    => null,
        'new_status'    => 'Pending',
        'actor_name'    => $tsk['created_by_name'] ?? 'Admin',
        'actor_desig'   => 'Administrator',
        'remarks'       => null,
        'attachment'    => null,
        'event_date'    => $tsk['created_at'] ?? '',
    ];

    // 2. Status history (old → new)
    $shr = $conn->query("
        SELECT tsh.*, u.full_name AS actor_name, u.designation AS actor_desig
          FROM task_status_history tsh
          LEFT JOIN users u ON u.user_id = tsh.changed_by
         WHERE tsh.task_id = $tid ORDER BY tsh.change_date ASC");
    if ($shr) {
        while ($row = $shr->fetch_assoc()) {
            $new_s = strtolower($row['new_status'] ?? '');
            if ($new_s === 'rejected') {
                $et = 'rejected';
                $title = 'Task Rejected';
            } elseif ($new_s === 'completed') {
                $et = 'completed';
                $title = 'Task Completed';
            } elseif (str_contains($new_s, 'in progress') || $new_s === 'started') {
                $et = 'started';
                $title = 'Task Started';
            } elseif ($new_s === 'assigned') {
                $et = 'assigned';
                $title = 'Task Assigned';
            } elseif ($new_s === 'acknowledged') {
                $et = 'acknowledged';
                $title = 'Task Acknowledged';
            } else {
                $et = 'status_change';
                $title = 'Status Changed';
            }
            $events[] = [
                'event_type'  => $et,
                'title'       => $title,
                'description' => $row['remarks'] ?? "Status changed to {$row['new_status']}",
                'old_status'  => $row['old_status'],
                'new_status'  => $row['new_status'],
                'actor_name'  => $row['actor_name']  ?? 'System',
                'actor_desig' => $row['actor_desig'] ?? '',
                'remarks'     => $row['remarks'],
                'attachment'  => null,
                'event_date'  => $row['change_date'] ?? '',
            ];
        }
    }

    // 3. Activity logs
    $ar = $conn->query("
        SELECT tal.*, u.full_name AS actor_name, u.designation AS actor_desig
          FROM task_activity_logs tal
          LEFT JOIN users u ON u.user_id = tal.user_id
         WHERE tal.task_id = $tid ORDER BY COALESCE(tal.activity_time, tal.log_id) ASC");
    if ($ar) {
        while ($row = $ar->fetch_assoc()) {
            $at = strtolower($row['activity_type'] ?? '');
            if (str_contains($at, 'created'))      { $et = 'created';      $title = 'Task Created'; }
            elseif (str_contains($at, 'assign'))   { $et = 'assigned';     $title = 'Task Assigned'; }
            elseif (str_contains($at, 'acknowledge')){ $et = 'acknowledged'; $title = 'Task Acknowledged'; }
            elseif (str_contains($at, 'start'))    { $et = 'started';      $title = 'Task Started'; }
            elseif (str_contains($at, 'reject'))   { $et = 'rejected';     $title = 'Task Rejected'; }
            elseif (str_contains($at, 'complet'))  { $et = 'completed';    $title = 'Task Completed'; }
            elseif (str_contains($at, 'upload') || str_contains($at,'document')) { $et = 'document'; $title = 'Document Uploaded'; }
            else { $et = $row['activity_type'] ?? 'activity'; $title = ucwords(str_replace('_', ' ', $et)); }
            $events[] = [
                'event_type'  => $et,
                'title'       => $title,
                'description' => $row['description'] ?? '',
                'old_status'  => null,
                'new_status'  => $row['activity_type'] ?? '',
                'actor_name'  => $row['actor_name']  ?? 'System',
                'actor_desig' => $row['actor_desig'] ?? '',
                'remarks'     => $row['description'],
                'attachment'  => null,
                'event_date'  => $row['activity_time'] ?? '',
            ];
        }
    }

    // 4. Manual tracking entries
    $tr = $conn->query("
        SELECT tt.*, u.full_name AS actor_name, u.designation AS actor_desig
          FROM task_tracking tt
          LEFT JOIN users u ON u.user_id = tt.updated_by
         WHERE tt.task_id = $tid ORDER BY COALESCE(tt.updated_date, tt.created_date) ASC");
    if ($tr) {
        while ($row = $tr->fetch_assoc()) {
            $events[] = [
                'event_type'  => 'tracking',
                'title'       => 'Admin Update: ' . ($row['status'] ?? ''),
                'description' => $row['remarks'] ?? ('Status tracked as ' . $row['status']),
                'old_status'  => null,
                'new_status'  => $row['status'],
                'actor_name'  => $row['actor_name']  ?? 'Admin',
                'actor_desig' => $row['actor_desig'] ?? '',
                'remarks'     => $row['remarks'],
                'attachment'  => null,
                'event_date'  => $row['updated_date'] ?? $row['created_date'] ?? '',
            ];
        }
    }

    // 5. Escalations
    $er = $conn->query("
        SELECT te.*, u_to.full_name AS escalated_to_name
          FROM task_escalations te
          LEFT JOIN users u_to ON u_to.user_id = te.escalated_to
         WHERE te.task_id = $tid ORDER BY te.escalation_date ASC");
    if ($er) {
        while ($row = $er->fetch_assoc()) {
            $events[] = [
                'event_type'  => 'escalation',
                'title'       => 'Task Escalated (Level ' . ($row['escalation_level'] ?? '1') . ')',
                'description' => $row['reason'] ?? 'Task was escalated.',
                'old_status'  => null,
                'new_status'  => $row['status'] ?? 'Escalated',
                'actor_name'  => $row['escalated_to_name'] ?? 'Higher Authority',
                'actor_desig' => '',
                'remarks'     => $row['reason'],
                'attachment'  => null,
                'event_date'  => $row['escalation_date'] ?? '',
            ];
        }
    }

    // 6. Remarks
    $remr = $conn->query("
        SELECT tr.*, u.full_name AS user_name, u.designation
          FROM task_remarks tr
          LEFT JOIN users u ON u.user_id = tr.user_id
         WHERE tr.task_id = $tid ORDER BY tr.created_at ASC");
    if ($remr) {
        while ($row = $remr->fetch_assoc()) {
            $events[] = [
                'event_type'  => 'remark',
                'title'       => 'Remark Added',
                'description' => $row['remark_text'] ?? '',
                'old_status'  => null,
                'new_status'  => $row['status_after_remark'] ?? '',
                'actor_name'  => $row['user_name']   ?? 'Unknown',
                'actor_desig' => $row['designation'] ?? '',
                'remarks'     => $row['remark_text'],
                'attachment'  => null,
                'event_date'  => $row['created_at'] ?? '',
            ];
        }
    }

    // Sort chronologically
    usort($events, fn($a, $b) => strtotime($a['event_date'] ?: '0') <=> strtotime($b['event_date'] ?: '0'));

    // Deduplicate
    $seen_k = [];
    $events = array_values(array_filter($events, function($e) use (&$seen_k) {
        $k = $e['title'] . '|' . $e['event_date'];
        if (isset($seen_k[$k])) return false;
        $seen_k[$k] = true;
        return true;
    }));

    // Overdue detection
    if (!empty($tsk['due_date']) && strtolower($tsk['status'] ?? '') !== 'completed') {
        if (time() > strtotime($tsk['due_date'])) {
            $events[] = [
                'event_type'  => 'overdue',
                'title'       => 'Deadline Crossed — Overdue',
                'description' => 'Task has passed its deadline: ' . date('d M Y', strtotime($tsk['due_date'])),
                'old_status'  => null,
                'new_status'  => 'Overdue',
                'actor_name'  => 'System',
                'actor_desig' => 'Auto-detected',
                'remarks'     => null,
                'attachment'  => null,
                'event_date'  => date('Y-m-d H:i:s'),
            ];
            $tsk['status'] = 'Overdue';
        }
    }

    echo json_encode([
        'task'   => $tsk,
        'events' => $events,
        'docs'   => $docs,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ═══════════════════════════════════════════════════════════════════
// AUTO-CREATE tables (idempotent – won't overwrite existing data)
// ═══════════════════════════════════════════════════════════════════
$conn->query("
    CREATE TABLE IF NOT EXISTS `task_tracking` (
        `tracking_id`  INT(11)      NOT NULL AUTO_INCREMENT,
        `task_id`      INT(11)      NOT NULL,
        `status`       VARCHAR(50)  NOT NULL DEFAULT 'Pending',
        `remarks`      TEXT                  DEFAULT NULL,
        `updated_by`   INT(11)               DEFAULT NULL,
        `updated_date` DATETIME              DEFAULT NULL,
        `created_date` TIMESTAMP   NOT NULL  DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`tracking_id`),
        KEY `idx_task_id` (`task_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$conn->query("
    CREATE TABLE IF NOT EXISTS `task_status_history` (
        `history_id`  INT(11)      NOT NULL AUTO_INCREMENT,
        `task_id`     INT(11)      NOT NULL,
        `old_status`  VARCHAR(50)           DEFAULT NULL,
        `new_status`  VARCHAR(50)           DEFAULT NULL,
        `changed_by`  INT(11)               DEFAULT NULL,
        `change_date` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `remarks`     TEXT                  DEFAULT NULL,
        PRIMARY KEY (`history_id`),
        KEY `idx_task_id` (`task_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$conn->query("
    CREATE TABLE IF NOT EXISTS `task_activity_logs` (
        `log_id`        INT(11)       NOT NULL AUTO_INCREMENT,
        `task_id`       INT(11)       NOT NULL,
        `user_id`       INT(11)                DEFAULT NULL,
        `activity_type` VARCHAR(100)           DEFAULT NULL,
        `description`   TEXT                   DEFAULT NULL,
        `activity_time` DATETIME               DEFAULT NULL,
        PRIMARY KEY (`log_id`),
        KEY `idx_task_id` (`task_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$conn->query("
    CREATE TABLE IF NOT EXISTS `task_remarks` (
        `remark_id`           INT(11) NOT NULL AUTO_INCREMENT,
        `task_id`             INT(11) NOT NULL,
        `user_id`             INT(11)          DEFAULT NULL,
        `remark_text`         TEXT             NOT NULL,
        `status_after_remark` VARCHAR(50)      DEFAULT NULL,
        `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`remark_id`),
        KEY `idx_task_id` (`task_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// ═══════════════════════════════════════════════════════════════════
// HANDLE POST: Admin adds a tracking entry
// ═══════════════════════════════════════════════════════════════════
$post_msg  = '';
$post_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_tracking') {
    $pt_task_id  = (int)($_POST['pt_task_id']  ?? 0);
    $pt_status   = trim($_POST['pt_status']    ?? '');
    $pt_remarks  = trim($_POST['pt_remarks']   ?? '');
    $pt_by       = (int)($_POST['pt_by']       ?? 0);
    $pt_old_stat = trim($_POST['pt_old_status'] ?? '');

    if ($pt_task_id > 0 && $pt_status !== '') {
        $sStatus  = $conn->real_escape_string($pt_status);
        $sRemarks = $conn->real_escape_string($pt_remarks);
        $bySQL    = $pt_by > 0 ? $pt_by : 'NULL';

        // 1. Insert manual tracking entry
        $ok = $conn->query("
            INSERT INTO task_tracking (task_id, status, remarks, updated_by, updated_date)
            VALUES ($pt_task_id, '$sStatus', '$sRemarks', $bySQL, NOW())
        ");

        if ($ok) {
            // 2. Record in status_history (with old → new)
            $sOld = $conn->real_escape_string($pt_old_stat);
            $conn->query("
                INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks)
                VALUES ($pt_task_id, " . ($sOld ? "'$sOld'" : 'NULL') . ", '$sStatus', $bySQL, NOW(), '$sRemarks')
            ");

            // 3. Log activity
            $actDesc = $conn->real_escape_string("Status changed" . ($pt_old_stat ? " from '$pt_old_stat'" : '') . " to '$pt_status'" . ($pt_remarks ? ": $pt_remarks" : ''));
            $conn->query("
                INSERT INTO task_activity_logs (task_id, user_id, activity_type, description, activity_time)
                VALUES ($pt_task_id, $bySQL, 'status_update', '$actDesc', NOW())
            ");

            // 4. Update tasks table
            $conn->query("UPDATE tasks SET status='$sStatus', updated_at=NOW() WHERE task_id=$pt_task_id");

            if (isset($_POST['is_ajax']) && $_POST['is_ajax'] == 1) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'success', 'message' => "Tracking entry saved! Status updated to $pt_status."]);
                exit;
            }

            $post_msg  = "Tracking entry saved! Status updated to <strong>$pt_status</strong>.";
            $post_type = 'success';
        } else {
            if (isset($_POST['is_ajax']) && $_POST['is_ajax'] == 1) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
                exit;
            }
            $post_msg  = 'Database error: ' . $conn->error;
            $post_type = 'error';
        }
    } else {
        if (isset($_POST['is_ajax']) && $_POST['is_ajax'] == 1) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => 'Task ID and new Status are required.']);
            exit;
        }
        $post_msg  = 'Task ID and new Status are required.';
        $post_type = 'error';
    }
}

// ═══════════════════════════════════════════════════════════════════
// SEARCH / FILTER LOGIC
// ═══════════════════════════════════════════════════════════════════
$search_query  = trim($_GET['search']        ?? '');
$search_type   = trim($_GET['search_type']   ?? 'all');  // task_no | task_title | all
$filter_status = trim($_GET['filter_status'] ?? '');

$task           = null;
$tracking       = [];
$task_doc       = null;
$search_results = [];
$search_error   = '';

if (!empty($search_query)) {
    $safe_q = $conn->real_escape_string($search_query);

    if ($search_type === 'task_no') {
        $where = "t.task_no = '$safe_q'";
    } elseif ($search_type === 'task_title') {
        $where = "t.task_title LIKE '%$safe_q%'";
    } else {
        $where = "(t.task_no = '$safe_q' OR t.task_title LIKE '%$safe_q%')";
    }

    $status_clause = $filter_status ? " AND t.status = '" . $conn->real_escape_string($filter_status) . "'" : '';

    $list_sql = "
        SELECT t.task_id, t.task_no, t.task_title, t.status, t.priority,
               t.due_date, t.created_at,
               u.full_name AS assigned_name
          FROM tasks t
          LEFT JOIN users u ON u.user_id = t.assigned_user_id
         WHERE $where $status_clause
         ORDER BY t.task_id DESC
         LIMIT 30";

    $list_res = $conn->query($list_sql);
    if ($list_res && $list_res->num_rows > 0) {
        while ($r = $list_res->fetch_assoc()) $search_results[] = $r;
    } else {
        $search_error = 'No tasks found matching your search.';
    }
}

// ─── Load selected task details + build complete timeline ────────
$raw_task_id      = trim($_GET['task_id'] ?? '');
$selected_task_id = null;
$master_timeline  = [];
$task_remarks_arr = [];
$activity_logs    = [];
$status_history   = [];
$escalations      = [];
$assignments      = [];

if ($raw_task_id !== '') {
    $is_numeric_id = is_numeric($raw_task_id);
    $where_clause  = $is_numeric_id
        ? "t.task_id = " . (int)$raw_task_id
        : "t.task_no = '" . $conn->real_escape_string($raw_task_id) . "'";

    // ── Full task details ────────────────────────────────────────
    $task_res = $conn->query("
        SELECT t.*,
               u_assigned.full_name    AS assigned_employee,
               u_assigned.designation  AS assigned_designation,
               u_assigned.phone        AS assigned_phone,
               r.role_name,
               d.department_name,
               u_creator.full_name     AS created_by_name
          FROM tasks t
          LEFT JOIN users       u_assigned ON u_assigned.user_id  = t.assigned_user_id
          LEFT JOIN roles       r          ON r.role_id            = t.assigned_role_id
          LEFT JOIN departments d          ON d.id                 = t.department_id
          LEFT JOIN users       u_creator  ON u_creator.user_id    = t.created_by
         WHERE $where_clause
         LIMIT 1");

    if ($task_res && $task_res->num_rows > 0) {
        $task = $task_res->fetch_assoc();
        $selected_task_id = (int)$task['task_id'];
    }

    if ($task) {
        // ── Documents ────────────────────────────────────────────
        $doc_res = $conn->query("
            SELECT td.*, u.full_name AS uploaded_by_name
              FROM task_documents td
              LEFT JOIN users u ON u.user_id = td.uploaded_by
             WHERE td.task_id = $selected_task_id
             ORDER BY td.uploaded_at DESC LIMIT 1");
        if ($doc_res && $doc_res->num_rows > 0) $task_doc = $doc_res->fetch_assoc();

        // ── Manual tracking entries ───────────────────────────────
        $tt_res = $conn->query("
            SELECT tt.tracking_id AS id, tt.status, tt.remarks,
                   COALESCE(tt.updated_date, tt.created_date) AS event_date,
                   u.full_name AS actor_name, u.designation AS actor_desig
              FROM task_tracking tt
              LEFT JOIN users u ON u.user_id = tt.updated_by
             WHERE tt.task_id = $selected_task_id
             ORDER BY event_date ASC");
        if ($tt_res) while ($r = $tt_res->fetch_assoc()) $tracking[] = $r;

        // ── Status history (old → new changes) ───────────────────
        $sh_res = $conn->query("
            SELECT tsh.*, u.full_name AS actor_name, u.designation AS actor_desig
              FROM task_status_history tsh
              LEFT JOIN users u ON u.user_id = tsh.changed_by
             WHERE tsh.task_id = $selected_task_id
             ORDER BY tsh.change_date ASC");
        if ($sh_res) while ($r = $sh_res->fetch_assoc()) $status_history[] = $r;

        // ── Escalations ───────────────────────────────────────────
        $esc_res = $conn->query("
            SELECT te.*, u_to.full_name AS escalated_to_name
              FROM task_escalations te
              LEFT JOIN users u_to ON u_to.user_id = te.escalated_to
             WHERE te.task_id = $selected_task_id
             ORDER BY te.escalation_date ASC");
        if ($esc_res) while ($r = $esc_res->fetch_assoc()) $escalations[] = $r;

        // ── Assignment history ────────────────────────────────────
        $asgn_res = $conn->query("
            SELECT ta.*,
                   u_to.full_name   AS assigned_to_name,
                   u_from.full_name AS assigned_from_name,
                   r.role_name      AS assigned_to_role_name
              FROM task_assignments ta
              LEFT JOIN users u_to   ON u_to.user_id   = ta.assigned_to_user
              LEFT JOIN users u_from ON u_from.user_id  = ta.assigned_from_user
              LEFT JOIN roles r      ON r.role_id        = ta.assigned_to_role
             WHERE ta.task_id = $selected_task_id
             ORDER BY ta.assigned_date ASC");
        // Fallback: if schema uses user_id/assigned_by/assigned_at
        if (!$asgn_res) {
            $asgn_res = $conn->query("
                SELECT ta.*, u_to.full_name AS assigned_to_name,
                       u_from.full_name AS assigned_from_name
                  FROM task_assignments ta
                  LEFT JOIN users u_to   ON u_to.user_id   = ta.user_id
                  LEFT JOIN users u_from ON u_from.user_id  = ta.assigned_by
                 WHERE ta.task_id = $selected_task_id
                 ORDER BY ta.assigned_at ASC");
        }
        if ($asgn_res) while ($r = $asgn_res->fetch_assoc()) $assignments[] = $r;

        // ── Remarks ──────────────────────────────────────────────
        $rem_res = $conn->query("
            SELECT tr.*, u.full_name AS user_name, u.designation
              FROM task_remarks tr
              LEFT JOIN users u ON u.user_id = tr.user_id
             WHERE tr.task_id = $selected_task_id
             ORDER BY tr.created_at ASC");
        if ($rem_res) while ($r = $rem_res->fetch_assoc()) $task_remarks_arr[] = $r;

        // ── Activity logs ─────────────────────────────────────────
        $act_res = $conn->query("
            SELECT tal.*, u.full_name AS actor_name, u.designation AS actor_designation
              FROM task_activity_logs tal
              LEFT JOIN users u ON u.user_id = tal.user_id
             WHERE tal.task_id = $selected_task_id
             ORDER BY COALESCE(tal.activity_time, tal.log_id) ASC");
        if ($act_res) while ($r = $act_res->fetch_assoc()) $activity_logs[] = $r;

        // ════════════════════════════════════════════════════════════
        // BUILD UNIFIED MASTER TIMELINE
        // ════════════════════════════════════════════════════════════

        // 1. Task Created
        $master_timeline[] = [
            'event_type'  => 'created',
            'title'       => 'Task Created',
            'description' => '"' . ($task['task_title'] ?? '') . '" was created and added to the system.',
            'change_detail'=> '',
            'actor_name'  => $task['created_by_name'] ?? 'Admin',
            'actor_desig' => 'Administrator',
            'event_date'  => $task['created_at'] ?? date('Y-m-d H:i:s'),
            'status'      => 'Pending',
        ];

        // 2. Assignment history
        foreach ($assignments as $a) {
            $to   = $a['assigned_to_name'] ?? ($a['assigned_to_role_name'] ?? ($a['full_name'] ?? 'Unknown'));
            $from = $a['assigned_from_name'] ?? null;
            $date = $a['assigned_date'] ?? ($a['assigned_at'] ?? ($a['created_at'] ?? ''));
            $master_timeline[] = [
                'event_type'  => 'assignment',
                'title'       => 'Task Assigned',
                'description' => 'Assigned to ' . $to . ($from ? ' (from ' . $from . ')' : '') . ($a['remarks'] ? '. Note: ' . $a['remarks'] : ''),
                'change_detail'=> $from ? "$from → $to" : "→ $to",
                'actor_name'  => $from ?? 'Admin',
                'actor_desig' => '',
                'event_date'  => $date,
                'status'      => $a['status'] ?? 'Assigned',
            ];
        }

        // 3. Status history (with old → new tracking)
        foreach ($status_history as $sh) {
            $old = $sh['old_status'] ?? '';
            $new = $sh['new_status'] ?? '';
            $master_timeline[] = [
                'event_type'   => 'status_change',
                'title'        => 'Status Changed',
                'description'  => $sh['remarks'] ?? ("Status updated to $new"),
                'change_detail'=> $old ? "$old → $new" : "→ $new",
                'actor_name'   => $sh['actor_name']  ?? 'System',
                'actor_desig'  => $sh['actor_desig'] ?? '',
                'event_date'   => $sh['change_date'],
                'status'       => $new,
            ];
        }

        // 4. Manual tracking entries
        foreach ($tracking as $te) {
            $master_timeline[] = [
                'event_type'   => 'tracking',
                'title'        => 'Update: ' . $te['status'],
                'description'  => $te['remarks'] ?? ('Status tracked as ' . $te['status']),
                'change_detail'=> '',
                'actor_name'   => $te['actor_name']  ?? 'Admin',
                'actor_desig'  => $te['actor_desig'] ?? '',
                'event_date'   => $te['event_date'],
                'status'       => $te['status'],
            ];
        }

        // 5. Activity logs
        foreach ($activity_logs as $al) {
            $master_timeline[] = [
                'event_type'   => $al['activity_type'] ?? 'activity',
                'title'        => ucwords(str_replace('_', ' ', $al['activity_type'] ?? 'Activity')),
                'description'  => $al['description'] ?? '',
                'change_detail'=> '',
                'actor_name'   => $al['actor_name']       ?? 'System',
                'actor_desig'  => $al['actor_designation'] ?? '',
                'event_date'   => $al['activity_time'] ?? '',
                'status'       => $al['activity_type'] ?? '',
            ];
        }

        // 6. Escalations
        foreach ($escalations as $esc) {
            $master_timeline[] = [
                'event_type'   => 'escalation',
                'title'        => 'Task Escalated (Level ' . ($esc['escalation_level'] ?? '1') . ')',
                'description'  => $esc['reason'] ?? 'Task was escalated.',
                'change_detail'=> '→ ' . ($esc['escalated_to_name'] ?? 'Higher Authority'),
                'actor_name'   => $esc['escalated_to_name'] ?? 'System',
                'actor_desig'  => '',
                'event_date'   => $esc['escalation_date'] ?? '',
                'status'       => $esc['status'] ?? 'Escalated',
            ];
        }

        // 7. Remarks
        foreach ($task_remarks_arr as $rem) {
            $master_timeline[] = [
                'event_type'   => 'remark',
                'title'        => 'Remark Added',
                'description'  => $rem['remark_text'] ?? '',
                'change_detail'=> '',
                'actor_name'   => $rem['user_name'] ?? 'Unknown',
                'actor_desig'  => $rem['designation'] ?? '',
                'event_date'   => $rem['created_at'] ?? '',
                'status'       => $rem['status_after_remark'] ?? 'remark',
            ];
        }

        // Sort chronologically (oldest first)
        usort($master_timeline, function ($a, $b) {
            return strtotime($a['event_date'] ?: '0') <=> strtotime($b['event_date'] ?: '0');
        });

        // Deduplicate
        $seen = [];
        $master_timeline = array_filter($master_timeline, function ($e) use (&$seen) {
            $key = $e['title'] . '|' . $e['event_date'];
            if (isset($seen[$key])) return false;
            $seen[$key] = true;
            return true;
        });
        $master_timeline = array_values($master_timeline);

        // Overdue injection
        if (strtolower(trim($task['status'] ?? '')) !== 'completed' && !empty($task['due_date'])) {
            if (time() > strtotime($task['due_date'])) {
                $master_timeline[] = [
                    'event_type'   => 'overdue',
                    'title'        => 'Deadline Crossed — Overdue',
                    'description'  => 'Task has passed its deadline: ' . date('d M Y', strtotime($task['due_date'])),
                    'change_detail'=> '',
                    'actor_name'   => 'System',
                    'actor_desig'  => 'Auto-detected',
                    'event_date'   => date('Y-m-d H:i:s'),
                    'status'       => 'Overdue',
                ];
                $task['status'] = 'Overdue';
            }
        }
    }
}

// ── Fetch users for "Updated By" dropdown ─────────────────────────
$all_users = [];
$ur = $conn->query("SELECT user_id, full_name, designation FROM users WHERE status='Active' ORDER BY full_name ASC LIMIT 300");
if ($ur) while ($u = $ur->fetch_assoc()) $all_users[] = $u;

// ── Helper functions ─────────────────────────────────────────────
function statusBadgeClass(string $status): string {
    return match(strtolower(trim($status))) {
        'pending'     => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800',
        'assigned'    => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-200 dark:border-blue-800',
        'in progress' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 border border-purple-200 dark:border-purple-800',
        'on hold'     => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400 border border-orange-200 dark:border-orange-800',
        'completed'   => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800',
        'rejected'    => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800',
        'cancelled'   => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800',
        'overdue'     => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800',
        'escalated'   => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800',
        default       => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600',
    };
}

function priorityBadge(string $p): string {
    return match(strtolower(trim($p))) {
        'critical' => 'bg-purple-100 text-purple-700 border border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800',
        'high'     => 'bg-red-100 text-red-700 border border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800',
        'medium'   => 'bg-yellow-100 text-yellow-700 border border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:border-yellow-800',
        'low'      => 'bg-green-100 text-green-700 border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800',
        default    => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
    };
}

function masterEventIcon(string $event_type, string $status): string {
    $et = strtolower(trim($event_type));
    $st = strtolower(trim($status));
    if ($et === 'created')                          return 'plus-circle';
    if ($et === 'remark')                           return 'message-square';
    if ($et === 'overdue')                          return 'alert-triangle';
    if ($et === 'escalation')                       return 'alert-triangle';
    if ($et === 'status_change' || $et === 'tracking') return timelineIcon($status);
    if (str_contains($et, 'reassign'))              return 'refresh-cw';
    if (str_contains($et, 'assign'))                return 'user-check';
    if (str_contains($et, 'upload')  || str_contains($et, 'document') || str_contains($et, 'file')) return 'paperclip';
    if (str_contains($et, 'start'))                 return 'play-circle';
    if ($st === 'completed'  || str_contains($et, 'complet')) return 'check-circle-2';
    if ($st === 'rejected'   || str_contains($et, 'reject'))  return 'x-circle';
    if ($st === 'cancelled'  || str_contains($et, 'cancel'))  return 'x-circle';
    if ($st === 'on hold'    || str_contains($et, 'hold'))    return 'pause-circle';
    if ($st === 'in progress')                      return 'loader';
    return 'git-commit';
}

function timelineIcon(string $status): string {
    return match(strtolower(trim($status))) {
        'pending'      => 'clock',
        'assigned'     => 'user-check',
        'in progress'  => 'loader',
        'on hold'      => 'pause-circle',
        'completed'    => 'check-circle-2',
        'rejected'     => 'x-circle',
        'cancelled'    => 'x-circle',
        'task created' => 'plus-circle',
        'overdue'      => 'alert-triangle',
        'escalated'    => 'alert-triangle',
        default        => 'circle',
    };
}

function masterEventColor(string $event_type, string $status): string {
    $et = strtolower(trim($event_type));
    $st = strtolower(trim($status));
    if ($et === 'created')                   return '#64748b';
    if ($et === 'remark')                    return '#f59e0b';
    if ($et === 'overdue')                   return '#ef4444';
    if ($et === 'escalation')                return '#ef4444';
    if (str_contains($et, 'reassign'))       return '#8b5cf6';
    if (str_contains($et, 'assign'))         return '#3b82f6';
    if (str_contains($et, 'upload') || str_contains($et, 'document')) return '#0ea5e9';
    if ($st === 'pending')                   return '#eab308';
    if ($st === 'assigned')                  return '#3b82f6';
    if ($st === 'in progress')               return '#a855f7';
    if ($st === 'on hold')                   return '#f97316';
    if ($st === 'completed')                 return '#22c55e';
    if ($st === 'rejected' || $st === 'cancelled' || $st === 'overdue' || $st === 'escalated') return '#ef4444';
    return '#94a3b8';
}

function masterEventBg(string $event_type, string $status): string {
    $et = strtolower(trim($event_type));
    $st = strtolower(trim($status));
    if ($et === 'created')                   return 'from-slate-500 to-slate-600';
    if ($et === 'remark')                    return 'from-amber-400 to-amber-500';
    if ($et === 'overdue')                   return 'from-red-600 to-red-700';
    if ($et === 'escalation')                return 'from-red-600 to-red-700';
    if (str_contains($et, 'reassign'))       return 'from-violet-500 to-violet-600';
    if (str_contains($et, 'assign'))         return 'from-blue-500 to-blue-600';
    if (str_contains($et, 'upload') || str_contains($et, 'document')) return 'from-sky-400 to-sky-500';
    if ($st === 'pending')                   return 'from-yellow-400 to-yellow-500';
    if ($st === 'assigned')                  return 'from-blue-500 to-blue-600';
    if ($st === 'in progress')               return 'from-purple-500 to-purple-600';
    if ($st === 'on hold')                   return 'from-orange-500 to-orange-600';
    if ($st === 'completed')                 return 'from-green-500 to-green-600';
    if ($st === 'rejected' || $st === 'cancelled' || $st === 'overdue' || $st === 'escalated') return 'from-red-500 to-red-600';
    return 'from-slate-400 to-slate-500';
}

close_db_connection();
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Tracking — Amravati Connect</title>
    <meta name="description" content="Admin: Track the complete journey and all activity changes of any task on the Amravati Connect platform.">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        navy:    { 50:'#eef2f6', 100:'#d9e2ec', 500:'#1a365d', 600:'#152b4a', 700:'#0f1f38', 900:'#0a1424' },
                        govgreen:{ 50:'#edf7ed', 500:'#2e7d32', 600:'#256428' },
                        saffron: { 50:'#fff3e0', 500:'#f57c00' },
                    }
                }
            }
        }
    </script>

    <style>
        :root { --background:0 0% 100%; --foreground:222.2 84% 4.9%; --border:214.3 31.8% 91.4%; }
        .dark  { --background:222.2 84% 4.9%; --foreground:210 40% 98%; --border:217.2 32.6% 17.5%; }
        body   { font-family:'Inter',sans-serif; }
        ::-webkit-scrollbar { width:6px; height:6px; }
        ::-webkit-scrollbar-track { background:transparent; }
        ::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:3px; }
        .dark ::-webkit-scrollbar-thumb { background:#475569; }

        .glass-panel {
            background: rgba(255,255,255,.8);
            backdrop-filter: blur(12px);
            border:1px solid rgba(255,255,255,.25);
        }
        .dark .glass-panel { background:rgba(15,23,42,.8); border:1px solid rgba(255,255,255,.06); }

        /* ── Timeline ─────────────────────────────── */
        .timeline-wrapper { position:relative; }
        .timeline-line {
            position:absolute; left:19px; top:44px; bottom:0;
            width:2px;
            background:linear-gradient(to bottom, #cbd5e1 0%, #cbd5e1 88%, transparent 100%);
        }
        .dark .timeline-line { background:linear-gradient(to bottom, #334155 0%, #334155 88%, transparent 100%); }

        .tl-node { position:relative; padding-left:56px; padding-bottom:32px; }
        .tl-node:last-child { padding-bottom:0; }

        .tl-dot {
            position:absolute; left:0; top:0;
            width:40px; height:40px; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            box-shadow:0 0 0 4px #fff, 0 2px 10px rgba(0,0,0,.12);
            z-index:2;
            transition:transform .2s;
        }
        .dark .tl-dot { box-shadow:0 0 0 4px #0f172a, 0 2px 10px rgba(0,0,0,.3); }
        .tl-node:hover .tl-dot { transform:scale(1.08); }

        .tl-card {
            border-radius:14px;
            padding:16px 20px;
            position:relative;
            background:#fff;
            border:1px solid #e2e8f0;
            box-shadow:0 1px 4px rgba(0,0,0,.05);
            transition:box-shadow .2s, transform .2s;
        }
        .dark .tl-card { background:#1e293b; border-color:#334155; }
        .tl-card:hover { box-shadow:0 6px 24px rgba(0,0,0,.09); transform:translateY(-1px); }

        /* Change badge (old → new) */
        .change-badge {
            display:inline-flex; align-items:center; gap:6px;
            padding:3px 10px; border-radius:999px;
            font-size:11px; font-weight:700; font-family:monospace;
            background:rgba(99,102,241,.1); color:#4338ca;
            border:1px solid rgba(99,102,241,.2);
        }
        .dark .change-badge { background:rgba(99,102,241,.15); color:#a5b4fc; border-color:rgba(99,102,241,.3); }

        /* Status progress bar */
        .progress-step { flex:1; text-align:center; position:relative; }
        .progress-step::before {
            content:''; position:absolute; top:14px; left:-50%; right:50%;
            height:2px; background:#e2e8f0; z-index:0;
        }
        .dark .progress-step::before { background:#334155; }
        .progress-step:first-child::before { display:none; }
        .progress-step.active::before,
        .progress-step.done::before { background:#152b4a; }
        .dark .progress-step.active::before,
        .dark .progress-step.done::before { background:#60a5fa; }

        /* Animations */
        @keyframes fadeSlideIn { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
        .animate-in { animation:fadeSlideIn .35s ease both; }

        /* Overdue pulse */
        @keyframes pulse-red {
            0%,100%{box-shadow:0 0 0 4px #fff,0 2px 10px rgba(0,0,0,.12)}
            50%{box-shadow:0 0 0 6px rgba(239,68,68,.25),0 2px 10px rgba(0,0,0,.12)}
        }
        .overdue-pulse { animation:pulse-red 1.8s infinite; }
        .dark .overdue-pulse {
            animation:none;
            box-shadow:0 0 0 5px rgba(239,68,68,.3),0 2px 10px rgba(0,0,0,.3);
        }

        /* Result row */
        .result-row:hover { background:#f8fafc; }
        .dark .result-row:hover { background:#1e293b; }
    </style>
</head>
<body class="h-screen flex overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">

<!-- ═══════════════════════════════════════════════════════════════
     SIDEBAR
════════════════════════════════════════════════════════════════ -->
<aside class="w-64 bg-white dark:bg-slate-950 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-all duration-300 z-20" id="sidebar">
    <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-800 gap-3">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-navy-600 to-navy-500 flex items-center justify-center">
            <i data-lucide="landmark" class="text-white w-5 h-5"></i>
        </div>
        <span class="font-bold text-lg text-navy-700 dark:text-white tracking-tight">Amravati Connect</span>
    </div>

    <div class="flex-1 overflow-y-auto py-3">
        <nav class="space-y-0.5 px-3">
            <p class="px-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-3">Main Modules</p>
            <a href="dashboard.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors gap-3">
                <i data-lucide="layout-dashboard" class="w-4 h-4 text-slate-400 flex-shrink-0"></i> Executive Dashboard
            </a>
            <a href="create_task.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors gap-3">
                <i data-lucide="network" class="w-4 h-4 text-slate-400 flex-shrink-0"></i> Task Allocation
            </a>
            <a href="task_tracking.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg bg-navy-50 dark:bg-navy-900/40 text-navy-700 dark:text-blue-400 gap-3">
                <i data-lucide="route" class="w-4 h-4 text-navy-600 dark:text-blue-400 flex-shrink-0"></i> Task Tracking
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors gap-3">
                <i data-lucide="bell-ring" class="w-4 h-4 text-slate-400 flex-shrink-0"></i> Announcements
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors gap-3">
                <i data-lucide="award" class="w-4 h-4 text-slate-400 flex-shrink-0"></i> Appreciation
            </a>

            <p class="px-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-4">Analytics &amp; Data</p>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors gap-3">
                <i data-lucide="pie-chart" class="w-4 h-4 text-slate-400 flex-shrink-0"></i> Reports &amp; Analytics
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors gap-3">
                <i data-lucide="folder-open" class="w-4 h-4 text-slate-400 flex-shrink-0"></i> Document Management
            </a>

            <p class="px-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-4">Administration</p>
            <a href="user_creation.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors gap-3">
                <i data-lucide="users" class="w-4 h-4 text-slate-400 flex-shrink-0"></i> User Management
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors gap-3">
                <i data-lucide="shield-check" class="w-4 h-4 text-slate-400 flex-shrink-0"></i> Audit Logs
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors gap-3">
                <i data-lucide="settings" class="w-4 h-4 text-slate-400 flex-shrink-0"></i> Settings
            </a>
        </nav>
    </div>

    <div class="p-4 border-t border-slate-200 dark:border-slate-800">
        <button class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-navy-600 to-navy-500 hover:opacity-90 rounded-xl transition-opacity">
            <i data-lucide="bot" class="w-4 h-4"></i> Ask Amravati AI
        </button>
    </div>
</aside>

<!-- ═══════════════════════════════════════════════════════════════
     MAIN CONTENT
════════════════════════════════════════════════════════════════ -->
<div class="flex-1 flex flex-col overflow-hidden">

    <!-- TOPBAR -->
    <header class="h-16 glass-panel border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-10 sticky top-0">
        <div class="flex items-center flex-1 gap-4">
            <button id="sidebarToggle" class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
            <nav class="flex items-center text-sm gap-1.5">
                <a href="dashboard.php?lang=<?= $lang ?>" class="text-slate-500 dark:text-slate-400 hover:text-navy-600 transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                <a href="task_tracking.php" class="text-slate-500 dark:text-slate-400 hover:text-navy-600 transition-colors">Task Tracking</a>
                <?php if ($task): ?>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                <span class="font-semibold text-slate-800 dark:text-white"><?= htmlspecialchars($task['task_no'] ?? ('#' . $task['task_id'])) ?></span>
                <?php endif; ?>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <button class="text-xs font-medium text-slate-600 dark:text-slate-300 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="languages" class="w-3.5 h-3.5 inline-block mr-1"></i> EN / MR
            </button>
            <button id="themeToggle" class="p-2 text-slate-500 dark:text-slate-400 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="moon" class="w-4.5 h-4.5 dark:hidden"></i>
                <i data-lucide="sun"  class="w-4.5 h-4.5 hidden dark:block"></i>
            </button>
            <!-- Notifications -->
            <?php include 'include/notification_widget.php'; ?>
            <div class="flex items-center gap-3 border-l border-slate-200 dark:border-slate-700 pl-3 ml-1">
                <div class="hidden sm:flex flex-col text-right leading-tight">
                    <span class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($sName) ?></span>
                    <span class="text-xs text-slate-500"><?= htmlspecialchars($sRole) ?></span>
                </div>
                <div class="h-9 w-9 rounded-full bg-gradient-to-br from-navy-600 to-navy-500 flex items-center justify-center text-white font-bold text-sm border-2 border-white dark:border-slate-800 shadow-sm"><?= htmlspecialchars($initials) ?></div>
            </div>
        </div>
    </header>

    <!-- SCROLLABLE MAIN -->
    <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 p-6 sm:p-8">

        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-navy-600 to-navy-500 flex items-center justify-center shadow-lg shadow-navy-500/25">
                        <i data-lucide="route" class="w-5 h-5 text-white"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Task Tracking</h1>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 ml-[52px]">
                    Admin: Search and track the complete journey &amp; all changes of any task.
                </p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center gap-3">
                <a href="create_task.php"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-navy-600 hover:bg-navy-700 rounded-xl transition-colors shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i> Create Task
                </a>
                <a href="dashboard.php?lang=<?= $lang ?>"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
                </a>
            </div>
        </div>

        <!-- Flash Message -->
        <?php if ($post_msg): ?>
        <div id="flashMsg" class="flex items-center gap-3 px-5 py-3.5 rounded-xl mb-6 text-sm font-medium animate-in
             <?= $post_type === 'success' ? 'bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-300 border border-green-200 dark:border-green-800'
                                          : 'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-800' ?>">
            <i data-lucide="<?= $post_type === 'success' ? 'check-circle-2' : 'alert-circle' ?>" class="w-4 h-4 flex-shrink-0"></i>
            <?= $post_msg ?>
        </div>
        <?php endif; ?>

        <!-- ══════════════════════════════════════════════════════════
             SEARCH CARD
        ══════════════════════════════════════════════════════════ -->
        <?php if (!$selected_task_id): ?>
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-6 animate-in">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                    <i data-lucide="search" class="w-4 h-4 text-navy-600 dark:text-blue-400"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-slate-800 dark:text-white">Search &amp; Filter Tasks</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Find any task by Task ID (e.g. TASK_001) or Task Name</p>
                </div>
            </div>
            <div class="p-6">
                <form method="GET" action="task_tracking.php" id="searchForm">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="sm:w-44">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Search By</label>
                            <select name="search_type"
                                    class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-navy-500 transition-colors">
                                <option value="all"        <?= $search_type === 'all'        ? 'selected' : '' ?>>Task ID or Name</option>
                                <option value="task_no"    <?= $search_type === 'task_no'    ? 'selected' : '' ?>>Task ID</option>
                                <option value="task_title" <?= $search_type === 'task_title' ? 'selected' : '' ?>>Task Name</option>
                            </select>
                        </div>

                        <div class="flex-1">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Search Query</label>
                            <div class="relative">
                                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                                <input type="text" name="search" id="searchInput"
                                       value="<?= htmlspecialchars($search_query) ?>"
                                       placeholder="Enter Task ID (TASK_001) or task name…"
                                       class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-navy-500 transition-colors">
                            </div>
                        </div>

                        <div class="sm:w-44">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Filter by Status</label>
                            <select name="filter_status"
                                    class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-navy-500 transition-colors">
                                <option value="">All Statuses</option>
                                <?php foreach (['Pending','Assigned','In Progress','On Hold','Completed','Rejected'] as $st): ?>
                                <option value="<?= $st ?>" <?= $filter_status === $st ? 'selected' : '' ?>><?= $st ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-navy-600 hover:bg-navy-700 rounded-xl shadow-sm transition-colors">
                                <i data-lucide="search" class="w-4 h-4"></i> Search
                            </button>
                            <?php if ($search_query): ?>
                            <a href="task_tracking.php"
                               class="inline-flex items-center px-3 py-2.5 border border-slate-300 dark:border-slate-600 rounded-xl text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- No Results -->
        <?php if (!empty($search_error) && !$task): ?>
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-12 text-center animate-in mb-6">
            <div class="w-14 h-14 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="search-x" class="w-7 h-7 text-slate-400"></i>
            </div>
            <h3 class="text-base font-semibold text-slate-700 dark:text-slate-200 mb-1">No Tasks Found</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">No tasks matched "<strong><?= htmlspecialchars($search_query) ?></strong>". Try a different term.</p>
        </div>
        <?php endif; ?>

        <!-- ══════════════════════════════════════════════════════════
             SEARCH RESULTS TABLE
        ══════════════════════════════════════════════════════════ -->
        <?php if (!empty($search_results) && !$selected_task_id): ?>
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-6 animate-in">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-800 dark:text-white">Search Results</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"><?= count($search_results) ?> task(s) found for "<?= htmlspecialchars($search_query) ?>"</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                    <?= count($search_results) ?> result<?= count($search_results) !== 1 ? 's' : '' ?>
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-900/50">
                        <tr>
                            <?php foreach (['Task ID','Task Title','Assigned To','Due Date','Priority','Status','Action'] as $h): ?>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= $h ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                        <?php foreach ($search_results as $sr):
                            $track_url = 'task_tracking.php?task_id=' . $sr['task_id'] . '&search=' . urlencode($search_query) . '&search_type=' . urlencode($search_type);
                        ?>
                        <tr class="result-row transition-colors cursor-pointer" onclick="openTrackModal(<?= $sr['task_id'] ?>)">
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-xs font-bold text-navy-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded-md"><?= htmlspecialchars($sr['task_no']) ?></span>
                            </td>
                            <td class="px-5 py-3.5 max-w-xs">
                                <div class="text-sm font-medium text-slate-900 dark:text-white truncate"><?= htmlspecialchars($sr['task_title']) ?></div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <?php if ($sr['assigned_name']): ?>
                                <div class="flex items-center gap-2">
                                    <div class="h-7 w-7 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-white">
                                        <?= strtoupper(substr($sr['assigned_name'], 0, 1)) ?>
                                    </div>
                                    <span class="text-sm text-slate-700 dark:text-slate-300"><?= htmlspecialchars($sr['assigned_name']) ?></span>
                                </div>
                                <?php else: ?><span class="text-xs text-slate-400">Unassigned</span><?php endif; ?>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                                <?= $sr['due_date'] ? date('d M Y', strtotime($sr['due_date'])) : '—' ?>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <?php if ($sr['priority']): ?>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full <?= priorityBadge($sr['priority']) ?>"><?= htmlspecialchars($sr['priority']) ?></span>
                                <?php else: echo '—'; endif; ?>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full <?= statusBadgeClass($sr['status']) ?>"><?= htmlspecialchars($sr['status']) ?></span>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                <button onclick="event.stopPropagation(); openTrackModal(<?= $sr['task_id'] ?>)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-white bg-gradient-to-r from-navy-600 to-navy-500 hover:opacity-90 shadow-sm transition-all hover:scale-105 active:scale-95">
                                    <i data-lucide="route" class="w-3.5 h-3.5"></i> Track
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>


        <!-- ══════════════════════════════════════════════════════════
             TASK DETAIL VIEW
        ══════════════════════════════════════════════════════════ -->
        <?php if ($task): ?>

        <!-- Status Progress Bar -->
        <?php
        $current_status = $task['status'] ?? 'Pending';
        $base_flow = ['Pending', 'Assigned', 'Accepted', 'In Progress'];
        if (strtolower($current_status) === 'overdue' || strtolower($current_status) === 'escalated') {
            $statuses = array_merge($base_flow, ['Overdue']);
        } elseif (strtolower($current_status) === 'rejected') {
            $statuses = array_merge($base_flow, ['Rejected']);
        } else {
            $statuses = array_merge($base_flow, ['Completed']);
        }
        if (!in_array($current_status, $statuses) && !in_array(strtolower($current_status), ['overdue', 'escalated'])) {
            $statuses[] = $current_status;
        }
        $current_idx = array_search($current_status, $statuses);
        if ($current_idx === false) $current_idx = -1;
        ?>
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-6 animate-in">
            <div class="px-6 py-5">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-sm font-semibold text-slate-800 dark:text-white flex items-center gap-2">
                        <i data-lucide="activity" class="w-4 h-4 text-navy-600 dark:text-blue-400"></i>
                        Current Status Progress
                    </h2>
                    <span class="px-3 py-1 text-xs font-bold rounded-full <?= statusBadgeClass($current_status) ?>">
                        <?= htmlspecialchars($current_status) ?>
                    </span>
                </div>
                <div class="flex items-start">
                    <?php foreach ($statuses as $idx => $s):
                        $cls = '';
                        if ($idx < $current_idx) $cls = 'done';
                        elseif ($idx === $current_idx) $cls = 'active';
                        $dot_bg = 'bg-slate-200 dark:bg-slate-600';
                        $label_cls = 'text-slate-400 dark:text-slate-500';
                        if ($idx <= $current_idx) {
                            $sl = strtolower($s);
                            if ($sl === 'escalated' || $sl === 'overdue' || $sl === 'rejected') {
                                $dot_bg = 'bg-red-500';
                                $label_cls = 'text-red-600 dark:text-red-400 font-bold';
                            } elseif ($sl === 'completed') {
                                $dot_bg = 'bg-green-500';
                                $label_cls = 'text-green-600 dark:text-green-400 font-bold';
                            } else {
                                $dot_bg = 'bg-navy-600 dark:bg-blue-500';
                                $label_cls = 'text-navy-600 dark:text-blue-400 font-semibold';
                            }
                        }
                    ?>
                    <div class="progress-step <?= $cls ?> flex-1">
                        <div class="relative flex justify-center mb-2">
                            <?php if ($idx > 0): ?>
                            <div class="absolute top-3.5 right-1/2 left-0 h-0.5 <?= $idx <= $current_idx ? 'bg-navy-600 dark:bg-blue-500' : 'bg-slate-200 dark:bg-slate-600' ?>"></div>
                            <?php endif; ?>
                            <div class="relative w-7 h-7 rounded-full <?= $dot_bg ?> flex items-center justify-center z-10">
                                <?php if ($idx < $current_idx): ?>
                                    <i data-lucide="check" class="w-3.5 h-3.5 text-white"></i>
                                <?php elseif ($idx === $current_idx): ?>
                                    <div class="w-2.5 h-2.5 rounded-full bg-white"></div>
                                <?php else: ?>
                                    <div class="w-2 h-2 rounded-full bg-slate-400 dark:bg-slate-500"></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-[11px] text-center leading-tight <?= $label_cls ?>"><?= $s ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">

            <!-- ── LEFT (Task Details) ─────────────────────────────── -->
            <div class="xl:col-span-2 space-y-6">

                <!-- Task Info -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden animate-in">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-slate-50 to-white dark:from-slate-800/60 dark:to-slate-800">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                            <i data-lucide="file-text" class="w-4 h-4 text-navy-600 dark:text-blue-400"></i>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-sm font-semibold text-slate-800 dark:text-white">Task Details</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Complete task information</p>
                        </div>
                        <span class="font-mono text-xs font-bold text-navy-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2.5 py-1 rounded-lg">
                            <?= htmlspecialchars($task['task_no'] ?? ('#' . $task['task_id'])) ?>
                        </span>
                    </div>
                    <div class="p-6">
                        <!-- Title -->
                        <div class="flex items-start gap-3 mb-4">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white leading-snug flex-1">
                                <?= htmlspecialchars($task['task_title']) ?>
                            </h3>
                            <?php if ($task['priority']): ?>
                            <span class="mt-1 px-2.5 py-1 text-xs font-bold rounded-full <?= priorityBadge($task['priority']) ?> whitespace-nowrap flex-shrink-0">
                                <?= htmlspecialchars($task['priority']) ?> Priority
                            </span>
                            <?php endif; ?>
                        </div>

                        <?php if ($task['task_description']): ?>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4 border border-slate-100 dark:border-slate-700 mb-5">
                            <?= nl2br(htmlspecialchars($task['task_description'])) ?>
                        </p>
                        <?php endif; ?>

                        <!-- Detail Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <?php
                            $details = [
                                ['icon'=>'user-circle', 'label'=>'Assigned Employee', 'value'=>$task['assigned_employee'] ?? null],
                                ['icon'=>'badge',       'label'=>'Designation',       'value'=>$task['assigned_designation'] ?? null],
                                ['icon'=>'shield',      'label'=>'Assigned Role',     'value'=>$task['role_name'] ?? null],
                                ['icon'=>'building-2',  'label'=>'Department',        'value'=>$task['department_name'] ?? null],
                                ['icon'=>'calendar',    'label'=>'Created Date',      'value'=>!empty($task['created_at']) ? date('d M Y, h:i A', strtotime($task['created_at'])) : null],
                                ['icon'=>'play-circle', 'label'=>'Start Date',        'value'=>!empty($task['start_date']) ? date('d M Y', strtotime($task['start_date'])) : null],
                                ['icon'=>'clock',       'label'=>'Due / Deadline',    'value'=>!empty($task['due_date']) ? date('d M Y', strtotime($task['due_date'])) : null, 'color'=>'text-red-600 dark:text-red-400'],
                                ['icon'=>'check-circle','label'=>'Completed On',      'value'=>!empty($task['completion_date']) ? date('d M Y', strtotime($task['completion_date'])) : null, 'color'=>'text-green-600 dark:text-green-400'],
                                ['icon'=>'tag',         'label'=>'Category',          'value'=>$task['task_category'] ?? null],
                                ['icon'=>'user-cog',    'label'=>'Created By',        'value'=>$task['created_by_name'] ?? null],
                            ];
                            foreach ($details as $d):
                                if (empty($d['value'])) continue;
                                $col = $d['color'] ?? 'text-slate-800 dark:text-white';
                            ?>
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700/50">
                                <div class="w-8 h-8 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i data-lucide="<?= $d['icon'] ?>" class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400"></i>
                                </div>
                                <div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-0.5"><?= $d['label'] ?></p>
                                    <p class="text-sm font-semibold <?= $col ?>"><?= htmlspecialchars($d['value']) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Attachment -->
                        <?php if ($task_doc): ?>
                        <div class="mt-4 p-4 rounded-xl border border-blue-100 dark:border-blue-900/40 bg-blue-50 dark:bg-blue-900/10 flex items-center gap-4">
                            <div class="w-10 h-10 rounded-lg bg-white dark:bg-slate-800 border border-blue-200 dark:border-blue-800 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="paperclip" class="w-5 h-5 text-blue-600 dark:text-blue-400"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-slate-500 mb-0.5">Attachment</p>
                                <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">
                                    <?= htmlspecialchars($task_doc['original_name'] ?? basename($task_doc['file_path'])) ?>
                                </p>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Uploaded by <?= htmlspecialchars($task_doc['uploaded_by_name'] ?? 'Admin') ?>
                                </p>
                            </div>
                            <a href="<?= htmlspecialchars($task_doc['file_path']) ?>" target="_blank"
                               class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg text-blue-700 dark:text-blue-300 bg-white dark:bg-slate-800 border border-blue-200 dark:border-blue-800 hover:bg-blue-50 transition-colors flex-shrink-0">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i> View
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Remarks History ─────────────────────────────── -->
                <?php if (!empty($task_remarks_arr)): ?>
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden animate-in">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                            <i data-lucide="message-square" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-800 dark:text-white">Remarks &amp; Updates</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400"><?= count($task_remarks_arr) ?> remark(s) recorded</p>
                        </div>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-700">
                        <?php foreach ($task_remarks_arr as $remark): ?>
                        <div class="px-6 py-4 flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-white flex-shrink-0 mt-0.5">
                                <?= strtoupper(substr($remark['user_name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    <span class="text-sm font-semibold text-slate-800 dark:text-white"><?= htmlspecialchars($remark['user_name'] ?? 'Unknown') ?></span>
                                    <?php if ($remark['designation']): ?>
                                    <span class="text-xs text-slate-400">· <?= htmlspecialchars($remark['designation']) ?></span>
                                    <?php endif; ?>
                                    <?php if ($remark['status_after_remark']): ?>
                                    <span class="ml-auto px-2 py-0.5 text-xs font-semibold rounded-full <?= statusBadgeClass($remark['status_after_remark']) ?>">
                                        <?= htmlspecialchars($remark['status_after_remark']) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed"><?= nl2br(htmlspecialchars($remark['remark_text'] ?? '')) ?></p>
                                <p class="text-[11px] text-slate-400 mt-1 font-mono">
                                    <?= !empty($remark['created_at']) ? date('d M Y, h:i A', strtotime($remark['created_at'])) : '' ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ── Add Tracking Entry (Admin) ──────────────────── -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden animate-in">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                        <div class="w-8 h-8 rounded-lg bg-navy-50 dark:bg-navy-900/30 flex items-center justify-center">
                            <i data-lucide="plus-circle" class="w-4 h-4 text-navy-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-800 dark:text-white">Add Tracking Entry</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Admin: Manually log a status update or activity event</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="task_tracking.php?task_id=<?= $selected_task_id ?>&search=<?= urlencode($search_query) ?>&search_type=<?= urlencode($search_type) ?>">
                            <input type="hidden" name="action"       value="add_tracking">
                            <input type="hidden" name="pt_task_id"   value="<?= $selected_task_id ?>">
                            <input type="hidden" name="pt_old_status" value="<?= htmlspecialchars($current_status) ?>">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">New Status <span class="text-red-500">*</span></label>
                                    <select name="pt_status" required
                                            class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-navy-500 transition-colors">
                                        <option value="">— Select Status —</option>
                                        <?php foreach (['Pending','Assigned','In Progress','On Hold','Completed','Rejected','Transferred','Under Review','Escalated'] as $st): ?>
                                        <option value="<?= $st ?>" <?= $current_status === $st ? 'selected' : '' ?>><?= $st ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($current_status): ?>
                                    <p class="text-[11px] text-slate-400 mt-1.5">
                                        Current: <span class="font-semibold text-slate-600 dark:text-slate-300"><?= htmlspecialchars($current_status) ?></span>
                                        &nbsp;→&nbsp; will change to new selection
                                    </p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Updated By</label>
                                    <select name="pt_by"
                                            class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-navy-500 transition-colors">
                                        <option value="">— Admin / System —</option>
                                        <?php foreach ($all_users as $u): ?>
                                        <option value="<?= $u['user_id'] ?>">
                                            <?= htmlspecialchars($u['full_name']) ?><?= $u['designation'] ? ' — ' . htmlspecialchars($u['designation']) : '' ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Remarks / Notes</label>
                                <textarea name="pt_remarks" rows="3"
                                          placeholder="Describe the update, observation or reason for the status change…"
                                          class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-navy-500 transition-colors resize-y"></textarea>
                            </div>

                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-navy-600 hover:bg-navy-700 rounded-xl shadow-sm transition-colors">
                                <i data-lucide="save" class="w-4 h-4"></i> Save Tracking Entry
                            </button>
                        </form>
                    </div>
                </div>

            </div><!-- /LEFT -->

            <!-- ── RIGHT (Stats + Legend) ─────────────────────────── -->
            <div class="space-y-5">

                <!-- Quick Stats -->
                <div class="grid grid-cols-2 gap-3">
                    <?php
                    $qs = [
                        ['label'=>'Task ID',      'value'=>$task['task_no'] ?? ('#'.$task['task_id']),
                         'icon'=>'hash',       'bg'=>'bg-blue-50 dark:bg-blue-900/20', 'ic'=>'text-navy-600 dark:text-blue-400'],
                        ['label'=>'Status',       'value'=>$task['status'],
                         'icon'=>'activity',   'bg'=>'bg-green-50 dark:bg-green-900/20', 'ic'=>'text-govgreen-600 dark:text-green-400'],
                        ['label'=>'Priority',     'value'=>$task['priority'] ?? '—',
                         'icon'=>'zap',        'bg'=>'bg-red-50 dark:bg-red-900/20', 'ic'=>'text-red-600 dark:text-red-400'],
                        ['label'=>'Total Events', 'value'=>count($master_timeline).' steps',
                         'icon'=>'git-branch', 'bg'=>'bg-purple-50 dark:bg-purple-900/20', 'ic'=>'text-purple-600 dark:text-purple-400'],
                        ['label'=>'Status Changes','value'=>count($status_history).' change'.((count($status_history)!==1)?'s':''),
                         'icon'=>'refresh-cw', 'bg'=>'bg-indigo-50 dark:bg-indigo-900/20', 'ic'=>'text-indigo-600 dark:text-indigo-400'],
                        ['label'=>'Escalations',  'value'=>count($escalations).' event'.((count($escalations)!==1)?'s':''),
                         'icon'=>'alert-triangle','bg'=>'bg-amber-50 dark:bg-amber-900/20', 'ic'=>'text-amber-600 dark:text-amber-400'],
                    ];
                    foreach ($qs as $q): ?>
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-7 h-7 rounded-lg <?= $q['bg'] ?> flex items-center justify-center">
                                <i data-lucide="<?= $q['icon'] ?>" class="w-3.5 h-3.5 <?= $q['ic'] ?>"></i>
                            </div>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400"><?= $q['label'] ?></span>
                        </div>
                        <p class="text-sm font-bold text-slate-800 dark:text-white truncate"><?= htmlspecialchars((string)($q['value'] ?? '—')) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Assigned Person -->
                <?php if ($task['assigned_employee'] || $task['role_name']): ?>
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">Assigned To</p>
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-navy-600 to-navy-500 flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                            <?= strtoupper(substr($task['assigned_employee'] ?? $task['role_name'] ?? 'A', 0, 1)) ?>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800 dark:text-white"><?= htmlspecialchars($task['assigned_employee'] ?? ($task['role_name'] ?? '—')) ?></p>
                            <?php if ($task['assigned_designation']): ?>
                            <p class="text-xs text-slate-500"><?= htmlspecialchars($task['assigned_designation']) ?></p>
                            <?php endif; ?>
                            <?php if ($task['department_name']): ?>
                            <p class="text-xs text-slate-400 mt-1 flex items-center gap-1">
                                <i data-lucide="building-2" class="w-3 h-3"></i>
                                <?= htmlspecialchars($task['department_name']) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Event Legend -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">Event Legend</p>
                    <div class="grid grid-cols-2 gap-2.5">
                        <?php foreach ([
                            ['#64748b','Task Created'],['#3b82f6','Assignment'],
                            ['#6366f1','Status Change'],['#f97316','On Hold'],
                            ['#22c55e','Completed'],    ['#f59e0b','Remark'],
                            ['#7c3aed','Escalation'],   ['#ef4444','Overdue/Rejected'],
                        ] as [$col, $lbl]): ?>
                        <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:<?= $col ?>"></span>
                            <?= $lbl ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div><!-- /RIGHT -->
        </div>

        <!-- ══════════════════════════════════════════════════════════
             TASK JOURNEY TIMELINE
        ══════════════════════════════════════════════════════════ -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden animate-in">
            <!-- Header -->
            <div class="flex items-center gap-4 px-6 py-5 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-navy-50/60 via-white to-white dark:from-slate-900/60 dark:via-slate-800 dark:to-slate-800">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-navy-600 to-navy-500 flex items-center justify-center shadow-md shadow-navy-500/25 flex-shrink-0">
                    <i data-lucide="git-branch" class="w-5 h-5 text-white"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Task Journey Timeline</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        <?= count($master_timeline) ?> event(s) &nbsp;•&nbsp;
                        <?= count($status_history) ?> status change(s) &nbsp;•&nbsp;
                        <?= count($escalations) ?> escalation(s) &nbsp;•&nbsp;
                        Chronological order
                    </p>
                </div>
                <span class="px-3 py-1.5 text-xs font-bold rounded-full <?= statusBadgeClass($task['status']) ?>">
                    Current: <?= htmlspecialchars($task['status']) ?>
                </span>
            </div>

            <div class="p-6 sm:p-8">
                <?php if (empty($master_timeline)): ?>
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="git-commit" class="w-8 h-8 text-slate-400"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1">No Events Yet</h3>
                    <p class="text-sm text-slate-400">Task journey events will appear here as the task progresses.</p>
                </div>
                <?php else: ?>

                <!-- Vertical Timeline -->
                <div class="timeline-wrapper">
                    <div class="timeline-line"></div>

                    <?php foreach ($master_timeline as $idx => $event):
                        $is_latest  = ($idx === count($master_timeline) - 1);
                        $ev_status  = $event['status'] ?? '';
                        $ev_icon    = masterEventIcon($event['event_type'], $ev_status);
                        $ev_bg_cls  = masterEventBg($event['event_type'], $ev_status);
                        $ev_color   = masterEventColor($event['event_type'], $ev_status);
                        $ev_date    = !empty($event['event_date']) ? date('d M Y  •  h:i A', strtotime($event['event_date'])) : '';
                        $is_overdue = strtolower($event['event_type']) === 'overdue';
                        $change     = trim($event['change_detail'] ?? '');

                        // Action label
                        $lbl = 'By';
                        $et  = strtolower($event['event_type']);
                        if ($et === 'created')                      $lbl = 'Created by';
                        elseif ($et === 'remark')                   $lbl = 'Remark by';
                        elseif (str_contains($et, 'assign'))        $lbl = 'Assigned by';
                        elseif ($et === 'status_change')            $lbl = 'Changed by';
                        elseif ($et === 'tracking')                 $lbl = 'Logged by';
                        elseif ($et === 'escalation')               $lbl = 'Escalated to';
                    ?>
                    <div class="tl-node" style="animation:fadeSlideIn .35s ease <?= ($idx * 0.055) ?>s both">

                        <!-- Icon Dot -->
                        <div class="tl-dot bg-gradient-to-br <?= $ev_bg_cls ?><?= $is_overdue ? ' overdue-pulse' : '' ?>">
                            <i data-lucide="<?= $ev_icon ?>" class="w-[18px] h-[18px] text-white"></i>
                        </div>

                        <!-- Card -->
                        <div class="tl-card" style="border-left:4px solid <?= $ev_color ?>">

                            <!-- Status tag -->
                            <div class="absolute -top-2.5 left-4">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 shadow-sm">
                                    <?= htmlspecialchars($event['event_type'] === 'created' ? 'CREATED' : strtoupper($ev_status ?: $event['event_type'])) ?>
                                </span>
                            </div>

                            <!-- Title + Latest badge -->
                            <div class="flex items-center gap-2 mt-1 mb-2 flex-wrap">
                                <h4 class="text-base font-bold text-slate-900 dark:text-white">
                                    <?= htmlspecialchars($event['title']) ?>
                                </h4>
                                <?php if ($is_latest): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold rounded-full bg-gradient-to-r from-navy-600 to-navy-500 text-white">
                                    <i data-lucide="sparkles" class="w-2.5 h-2.5"></i> LATEST
                                </span>
                                <?php endif; ?>
                                <?php if ($is_overdue): ?>
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800 animate-pulse">
                                    ⚠ OVERDUE
                                </span>
                                <?php endif; ?>
                            </div>

                            <!-- Change Detail (old → new) -->
                            <?php if ($change): ?>
                            <div class="mb-2.5">
                                <span class="change-badge">
                                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                    <?= htmlspecialchars($change) ?>
                                </span>
                            </div>
                            <?php endif; ?>

                            <!-- Description -->
                            <?php if (!empty($event['description'])): ?>
                            <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed mb-3">
                                <?= nl2br(htmlspecialchars($event['description'])) ?>
                            </p>
                            <?php endif; ?>

                            <!-- Meta row -->
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-700 pt-3 mt-1">
                                <?php if (!empty($event['actor_name'])): ?>
                                <div class="flex items-center gap-1.5">
                                    <i data-lucide="user" class="w-3 h-3 flex-shrink-0"></i>
                                    <span><?= $lbl ?></span>
                                    <strong class="text-slate-700 dark:text-slate-200 font-semibold"><?= htmlspecialchars($event['actor_name']) ?></strong>
                                    <?php if (!empty($event['actor_desig'])): ?>
                                    <span class="text-slate-400 dark:text-slate-500">· <?= htmlspecialchars($event['actor_desig']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                <?php if ($ev_date): ?>
                                <div class="flex items-center gap-1.5">
                                    <i data-lucide="clock" class="w-3 h-3 flex-shrink-0"></i>
                                    <span class="font-mono"><?= $ev_date ?></span>
                                </div>
                                <?php endif; ?>
                            </div>

                        </div><!-- /.tl-card -->
                    </div><!-- /.tl-node -->
                    <?php endforeach; ?>
                </div><!-- /.timeline-wrapper -->
                <?php endif; ?>
            </div>
        </div>

        <?php endif; // end if $task ?>

        <!-- ──────────────────────────────────────────────────────────
             LANDING / EMPTY STATE
        ─────────────────────────────────────────────────────────── -->
        <?php if (!$search_query && !$task): ?>
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-16 text-center animate-in">
            <div class="w-20 h-20 bg-gradient-to-br from-navy-600 to-navy-500 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-navy-500/30">
                <i data-lucide="route" class="w-10 h-10 text-white"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Track Any Task Journey</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto mb-8 leading-relaxed">
                Enter a <strong>Task ID</strong>
                (e.g. <span class="font-mono font-semibold text-navy-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2 py-0.5 rounded">TASK_001</span>)
                or any part of a <strong>Task Name</strong> above to see the complete journey,
                all status changes, assignment history, escalations and activity events.
            </p>
            <div class="flex flex-wrap justify-center gap-3">
                <?php foreach ([
                    ['#eab308','Pending'],['#3b82f6','Assigned'],['#8b5cf6','Accepted'],
                    ['#6366f1','In Progress'],['#22c55e','Completed'],['#ef4444','Overdue'],
                ] as [$c, $l]): ?>
                <div class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-600 dark:text-slate-400">
                    <span class="w-2.5 h-2.5 rounded-full" style="background:<?= $c ?>"></span>
                    <?= $l ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </main>
</div>

<!-- Floating AI Chat Button -->
<div class="fixed bottom-6 right-6 z-50">
    <button class="w-13 h-13 w-[52px] h-[52px] bg-gradient-to-r from-navy-600 to-navy-500 rounded-full shadow-xl shadow-navy-500/30 flex items-center justify-center text-white hover:scale-110 transition-transform"
            title="Ask Amravati AI">
        <i data-lucide="message-square-text" class="w-6 h-6"></i>
    </button>
</div>

<!-- Scripts -->
<script>
    lucide.createIcons();

    // Dark mode toggle
    const themeToggle = document.getElementById('themeToggle');
    themeToggle.addEventListener('click', () => {
        document.documentElement.classList.toggle('dark');
    });

    // Sidebar toggle
    const sidebar = document.getElementById('sidebar');
    document.getElementById('sidebarToggle').addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
    });

    // Auto-focus search on landing
    <?php if (!$task && !$search_query): ?>
    document.getElementById('searchInput')?.focus();
    <?php endif; ?>

    // Enter key submits
    document.getElementById('searchInput')?.addEventListener('keydown', e => {
        if (e.key === 'Enter') document.getElementById('searchForm').submit();
    });

    // Auto-hide flash message
    const flash = document.getElementById('flashMsg');
    if (flash) setTimeout(() => {
        flash.style.transition = 'opacity .4s';
        flash.style.opacity = '0';
        setTimeout(() => flash.remove(), 400);
    }, 5000);
</script>

<!-- ═══════════════════════════════════════════════════════════════
     TASK TIMELINE MODAL (slide-in drawer)
════════════════════════════════════════════════════════════════ -->
<div id="trackModal"
     class="fixed inset-0 z-[100] flex items-stretch justify-end pointer-events-none"
     aria-hidden="true">

    <!-- Backdrop -->
    <div id="modalBackdrop"
         onclick="closeTrackModal()"
         class="absolute inset-0 bg-black/50 backdrop-blur-sm opacity-0 transition-opacity duration-300 pointer-events-none"></div>

    <!-- Panel -->
    <div id="modalPanel"
         class="relative w-full max-w-3xl h-full bg-white dark:bg-slate-900 shadow-2xl flex flex-col translate-x-full transition-transform duration-300 ease-[cubic-bezier(.32,.72,0,1)] pointer-events-auto">

        <!-- Panel Header -->
        <div id="modalHeader"
             class="flex-shrink-0 px-6 py-4 bg-gradient-to-r from-navy-700 to-navy-600 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                <i data-lucide="route" class="w-5 h-5 text-white"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div id="modalTaskNo" class="text-xs font-bold text-blue-200 tracking-wider font-mono mb-0.5"></div>
                <div id="modalTaskTitle" class="text-base font-bold text-white truncate"></div>
            </div>
            <button onclick="closeTrackModal()"
                    class="p-2 text-white/70 hover:text-white hover:bg-white/10 rounded-lg transition-colors flex-shrink-0">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Task Details Strip -->
        <div id="modalDetailsStrip"
             class="flex-shrink-0 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 px-6 py-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Task ID</p>
                    <p id="mdTaskNo" class="text-sm font-bold text-slate-800 dark:text-white font-mono"></p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Assigned To</p>
                    <p id="mdAssignedTo" class="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate"></p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Priority</p>
                    <p id="mdPriority" class="text-sm font-semibold"></p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Due Date</p>
                    <p id="mdDueDate" class="text-sm font-semibold text-red-600 dark:text-red-400"></p>
                </div>
            </div>
            <!-- Description -->
            <div id="mdDescWrap" class="mt-3 hidden">
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Description</p>
                <p id="mdDesc" class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-2"></p>
            </div>
        </div>

        <!-- Status Progress Bar -->
        <div id="modalProgressWrap" class="flex-shrink-0 px-6 py-3 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700">
            <div id="modalProgressBar" class="flex items-start gap-0"></div>
        </div>

        <!-- Scrollable Timeline Content -->
        <div class="flex-1 overflow-y-auto">

            <!-- Loading Spinner -->
            <div id="modalLoader" class="flex flex-col items-center justify-center py-20 gap-4">
                <div class="w-10 h-10 border-4 border-navy-200 border-t-navy-600 rounded-full animate-spin"></div>
                <p class="text-sm text-slate-500 dark:text-slate-400">Loading task journey…</p>
            </div>

            <!-- Error State -->
            <div id="modalError" class="hidden flex-col items-center justify-center py-20 text-center px-8">
                <div class="w-14 h-14 bg-red-50 dark:bg-red-900/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="alert-circle" class="w-7 h-7 text-red-500"></i>
                </div>
                <h3 class="text-base font-semibold text-slate-700 dark:text-slate-200 mb-1">Failed to Load</h3>
                <p id="modalErrorMsg" class="text-sm text-slate-400"></p>
            </div>

            <!-- Timeline -->
            <div id="modalTimeline" class="hidden px-6 py-6">

                <!-- Event count badge -->
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i data-lucide="git-branch" class="w-4 h-4 text-navy-600 dark:text-blue-400"></i>
                        Task Journey Timeline
                    </h3>
                    <span id="modalEventCount" class="text-xs font-semibold px-2.5 py-1 rounded-full bg-navy-50 dark:bg-navy-900/40 text-navy-600 dark:text-blue-400 border border-navy-100 dark:border-navy-800"></span>
                </div>

                <!-- Attachments section (from task_documents) -->
                <div id="modalAttachments" class="hidden mb-5 p-4 rounded-xl border border-blue-100 dark:border-blue-900/40 bg-blue-50 dark:bg-blue-900/10">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i data-lucide="paperclip" class="w-3.5 h-3.5"></i> Attached Documents
                    </p>
                    <div id="modalAttachmentList" class="space-y-2"></div>
                </div>

                <!-- Timeline nodes will be injected here -->
                <div id="tlNodes" class="relative">
                    <div class="absolute left-4 top-5 bottom-0 w-0.5 bg-gradient-to-b from-slate-200 to-transparent dark:from-slate-700"></div>
                </div>



            </div>
        </div>

        <!-- Panel Footer -->
        <div class="flex-shrink-0 border-t border-slate-200 dark:border-slate-700 px-6 py-4 bg-white dark:bg-slate-900 flex items-center justify-between gap-3">
            <div id="modalCurrentStatus" class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <span class="text-xs text-slate-400">Current Status:</span>
                <span id="modalStatusBadge" class="px-2.5 py-1 text-xs font-bold rounded-full"></span>
            </div>
            <div class="flex gap-2">
                <button id="modalFullViewBtn" onclick="goToFullView()"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg text-white bg-gradient-to-r from-navy-600 to-navy-500 hover:opacity-90 shadow-sm transition-all hover:scale-105 active:scale-95">
                    <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i> Full View
                </button>
                <button onclick="closeTrackModal()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     FULL-SCREEN TASK VIEW OVERLAY
════════════════════════════════════════════════════════════════ -->
<div id="fullScreenOverlay"
     class="fixed inset-0 z-[200] bg-slate-50 dark:bg-slate-950 flex flex-col"
     style="display:none !important; opacity:0; transition: opacity 0.25s ease;"
     aria-hidden="true">

    <!-- Top Bar -->
    <div class="flex-shrink-0 bg-gradient-to-r from-navy-800 to-navy-600 px-6 py-4 flex items-center gap-4 shadow-lg">
        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
            <i data-lucide="maximize-2" class="w-5 h-5 text-white"></i>
        </div>
        <div class="flex-1 min-w-0">
            <div id="fv-task-no" class="text-xs font-bold text-blue-200 tracking-widest font-mono mb-0.5"></div>
            <div id="fv-task-title" class="text-lg font-bold text-white truncate"></div>
        </div>
        <div class="flex items-center gap-3">
            <span id="fv-status-badge" class="px-3 py-1 text-xs font-bold rounded-full"></span>
            <button onclick="closeFullScreen()"
                    class="p-2 text-white/70 hover:text-white hover:bg-white/10 rounded-xl transition-colors flex items-center gap-1.5 text-sm font-semibold">
                <i data-lucide="minimize-2" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Exit Full View</span>
            </button>
        </div>
    </div>

    <!-- Scrollable Body -->
    <div class="flex-1 overflow-y-auto">
        <div class="max-w-5xl mx-auto px-4 sm:px-8 py-8 space-y-6">

            <!-- Task Meta Strip -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-5 mb-4">
                    <div>
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Task ID</p>
                        <p id="fv-task-no-detail" class="text-sm font-bold text-navy-600 dark:text-blue-400 font-mono"></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Assigned To</p>
                        <p id="fv-assigned" class="text-sm font-semibold text-slate-800 dark:text-white"></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Priority</p>
                        <p id="fv-priority" class="text-sm font-bold"></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Due Date</p>
                        <p id="fv-due" class="text-sm font-semibold text-red-600 dark:text-red-400"></p>
                    </div>
                </div>
                <div id="fv-desc-wrap" class="hidden">
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Description</p>
                    <p id="fv-desc" class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed bg-slate-50 dark:bg-slate-900/50 rounded-xl p-3 border border-slate-100 dark:border-slate-700"></p>
                </div>
            </div>

            <!-- Status Progress Bar -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-5 flex items-center gap-2">
                    <i data-lucide="activity" class="w-3.5 h-3.5"></i> Status Progress
                </h3>
                <div id="fv-progress-bar" class="flex items-start"></div>
            </div>

            <!-- Full Timeline -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="flex items-center gap-4 px-6 py-5 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-navy-50/60 via-white to-white dark:from-slate-900/60 dark:via-slate-800 dark:to-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-navy-600 to-navy-500 flex items-center justify-center shadow-md flex-shrink-0">
                        <i data-lucide="git-branch" class="w-5 h-5 text-white"></i>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Task Journey Timeline</h2>
                        <p id="fv-event-count" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"></p>
                    </div>
                </div>
                <div class="p-6 sm:p-8">
                    <!-- Attachments -->
                    <div id="fv-attachments" class="hidden mb-6 p-4 rounded-xl border border-blue-100 dark:border-blue-900/40 bg-blue-50 dark:bg-blue-900/10">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <i data-lucide="paperclip" class="w-3.5 h-3.5"></i> Attached Documents
                        </p>
                        <div id="fv-attachment-list" class="space-y-2"></div>
                    </div>
                    <!-- Timeline nodes -->
                    <div id="fv-timeline-nodes" class="relative">
                        <div class="absolute left-5 top-5 bottom-0 w-0.5 bg-gradient-to-b from-slate-200 via-slate-200 to-transparent dark:from-slate-700 dark:via-slate-700"></div>
                    </div>
                </div>
            </div>

            <!-- Footer spacing -->
            <div class="h-6"></div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     MODAL JAVASCRIPT
════════════════════════════════════════════════════════════════ -->
<script>
// ── Config ────────────────────────────────────────────────────────
const AJAX_BASE = 'task_tracking.php?ajax=timeline&task_id=';

// ── Icon map by event_type ────────────────────────────────────────
const TL_ICONS = {
    created:      { icon:'plus-circle',   bg:'from-slate-500 to-slate-600',  col:'#64748b', label:'Created'     },
    assigned:     { icon:'user-check',    bg:'from-blue-500 to-blue-600',    col:'#3b82f6', label:'Assigned'    },
    acknowledged: { icon:'thumbs-up',     bg:'from-cyan-500 to-cyan-600',    col:'#06b6d4', label:'Acknowledged'},
    started:      { icon:'play-circle',   bg:'from-indigo-500 to-indigo-600',col:'#6366f1', label:'Started'     },
    status_change:{ icon:'refresh-cw',    bg:'from-purple-500 to-purple-600',col:'#8b5cf6', label:'Changed'     },
    tracking:     { icon:'git-commit',    bg:'from-slate-400 to-slate-500',  col:'#94a3b8', label:'Admin Log'   },
    remark:       { icon:'message-square',bg:'from-amber-400 to-amber-500',  col:'#f59e0b', label:'Remark'      },
    rejected:     { icon:'x-circle',      bg:'from-red-500 to-red-600',      col:'#ef4444', label:'Rejected'    },
    completed:    { icon:'check-circle-2',bg:'from-green-500 to-green-600',  col:'#22c55e', label:'Completed'   },
    escalation:   { icon:'alert-triangle',bg:'from-red-600 to-red-700',      col:'#ef4444', label:'Escalated'   },
    overdue:      { icon:'alert-triangle',bg:'from-red-600 to-red-700',      col:'#ef4444', label:'Overdue'     },
    document:     { icon:'paperclip',     bg:'from-sky-400 to-sky-500',      col:'#0ea5e9', label:'Document'    },
    activity:     { icon:'zap',           bg:'from-slate-400 to-slate-500',  col:'#94a3b8', label:'Activity'    },
};

const STATUS_COLORS = {
    pending:     'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
    assigned:    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    'in progress':'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
    'on hold':   'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
    completed:   'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    rejected:    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    cancelled:   'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    overdue:     'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    escalated:   'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
};

function getStatusClass(s) {
    return STATUS_COLORS[(s||'').toLowerCase()] || 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300';
}

function getProgressStyle(sl) {
    if (sl.includes('pending'))   return { bg: 'bg-yellow-500', text: 'text-yellow-600 dark:text-yellow-400', icon: 'clock' };
    if (sl.includes('assign'))    return { bg: 'bg-blue-500',   text: 'text-blue-600 dark:text-blue-400',   icon: 'user-check' };
    if (sl.includes('progress'))  return { bg: 'bg-purple-500', text: 'text-purple-600 dark:text-purple-400', icon: 'loader' };
    if (sl.includes('complet'))   return { bg: 'bg-green-500',  text: 'text-green-600 dark:text-green-400', icon: 'check-circle-2' };
    if (sl.includes('reject') || sl.includes('cancel')) return { bg: 'bg-red-500', text: 'text-red-600 dark:text-red-400', icon: 'x-circle' };
    if (sl.includes('overdue') || sl.includes('escalate')) return { bg: 'bg-red-500', text: 'text-red-600 dark:text-red-400', icon: 'alert-triangle' };
    return { bg: 'bg-slate-500', text: 'text-slate-600 dark:text-slate-400', icon: 'circle' };
}

function getTlInfo(et) {
    return TL_ICONS[et] || TL_ICONS['activity'];
}

function fmtDate(d) {
    if (!d) return '';
    const dt = new Date(d);
    if (isNaN(dt)) return d;
    return dt.toLocaleDateString('en-IN', {day:'2-digit',month:'short',year:'numeric'})
         + ' · ' + dt.toLocaleTimeString('en-IN', {hour:'2-digit',minute:'2-digit'});
}

function fmtDateShort(d) {
    if (!d) return '—';
    const dt = new Date(d);
    if (isNaN(dt)) return d;
    return dt.toLocaleDateString('en-IN', {day:'2-digit',month:'short',year:'numeric'});
}

// ── Open Modal ────────────────────────────────────────────────────
async function openTrackModal(taskId) {
    const modal    = document.getElementById('trackModal');
    const backdrop = document.getElementById('modalBackdrop');
    const panel    = document.getElementById('modalPanel');
    const loader   = document.getElementById('modalLoader');
    const err      = document.getElementById('modalError');
    const timeline = document.getElementById('modalTimeline');

    // Reset state
    loader.style.display = 'flex';
    err.classList.add('hidden');
    timeline.classList.add('hidden');
    document.getElementById('tlNodes').innerHTML =
        '<div class="absolute left-4 top-5 bottom-0 w-0.5 bg-gradient-to-b from-slate-200 to-transparent dark:from-slate-700"></div>';
    document.getElementById('modalTaskNo').textContent    = '';
    document.getElementById('modalTaskTitle').textContent = '';
    document.getElementById('modalProgressBar').innerHTML = '';

    // Show modal
    modal.style.pointerEvents = 'auto';
    modal.setAttribute('aria-hidden', 'false');
    backdrop.style.pointerEvents = 'auto';
    document.body.style.overflow = 'hidden';

    requestAnimationFrame(() => {
        backdrop.style.opacity = '1';
        panel.style.transform  = 'translateX(0)';
    });

    // Fetch data
    try {
        const res  = await fetch(AJAX_BASE + taskId);
        const data = await res.json();

        if (data.error) throw new Error(data.error);

        renderModal(data.task, data.events, data.docs || []);
        // Cache data for Full View
        _fvCache = { task: data.task, events: data.events, docs: data.docs || [] };
    } catch(e) {
        loader.style.display = 'none';
        err.classList.remove('hidden');
        err.style.display = 'flex';
        document.getElementById('modalErrorMsg').textContent = e.message || 'Could not load task data.';
    }

    lucide.createIcons();
}

// ── Global cache for task data (used by Full View) ───────────────
let _fvCache = { task: null, events: [], docs: [] };

// ── Navigate to Full View (full-screen overlay) ───────────────────
function goToFullView() {
    if (!_fvCache.task) return;
    openFullScreen(_fvCache.task, _fvCache.events, _fvCache.docs);
}

// ── Open Full-Screen Overlay ──────────────────────────────────────
function openFullScreen(task, events, docs) {
    const overlay = document.getElementById('fullScreenOverlay');
    if (!overlay) return;

    // Populate header
    const taskNo = task.task_no || ('#' + task.task_id);
    document.getElementById('fv-task-no').textContent       = taskNo;
    document.getElementById('fv-task-title').textContent    = task.task_title || '—';
    document.getElementById('fv-task-no-detail').textContent= taskNo;
    document.getElementById('fv-assigned').textContent      = task.assigned_employee || task.role_name || 'Unassigned';
    document.getElementById('fv-due').textContent           = fmtDateShort(task.due_date);

    // Priority
    const pEl = document.getElementById('fv-priority');
    pEl.textContent = task.priority || '—';
    const pMap = { high:'text-red-600', medium:'text-yellow-600', low:'text-green-600', critical:'text-purple-600' };
    pEl.className = 'text-sm font-bold ' + (pMap[(task.priority||'').toLowerCase()] || 'text-slate-600 dark:text-slate-300');

    // Description
    const descWrap = document.getElementById('fv-desc-wrap');
    const descEl   = document.getElementById('fv-desc');
    if (task.task_description) {
        descEl.textContent = task.task_description;
        descWrap.classList.remove('hidden');
    } else {
        descWrap.classList.add('hidden');
    }

    // Status badge
    const status = task.status || 'Pending';
    const sb = document.getElementById('fv-status-badge');
    sb.textContent = status;
    sb.className = 'px-3 py-1 text-xs font-bold rounded-full ' + getStatusClass(status);

    // Progress bar
    const st = status.toLowerCase();
    let flow = ['Pending', 'Assigned', 'In Progress', 'Completed'];
    if (st === 'rejected')  flow = ['Pending', 'Assigned', 'In Progress', 'Rejected'];
    if (st === 'on hold')   flow = ['Pending', 'Assigned', 'In Progress', 'On Hold', 'Completed'];
    if (st === 'escalated') flow = ['Pending', 'Assigned', 'In Progress', 'Escalated'];
    if (!flow.map(s => s.toLowerCase()).includes(st)) flow.push(status);
    const curIdx = flow.findIndex(s => s.toLowerCase() === st);
    document.getElementById('fv-progress-bar').innerHTML = flow.map((s, idx) => {
        const done = idx < curIdx, active = idx === curIdx;
        const sl = s.toLowerCase();
        let style = getProgressStyle(sl);
        let dotBg = 'bg-slate-200 dark:bg-slate-700';
        let lbl   = 'text-slate-400 text-[11px]';
        let lineC = 'bg-slate-200 dark:bg-slate-700';
        
        if (idx <= curIdx) {
            dotBg = style.bg;
            lbl   = style.text + ' font-semibold text-[11px]';
            lineC = style.bg;
        }
        const line  = idx > 0 ? `<div class="absolute top-4 right-1/2 left-0 h-0.5 ${lineC}"></div>` : '';
        const inner = (done || active)
                    ? `<i data-lucide="${style.icon}" class="w-4 h-4 text-white"></i>`
                    : `<div class="w-2 h-2 rounded-full bg-slate-400 dark:bg-slate-500"></div>`;
        return `<div class="flex-1 text-center relative">
            ${line}
            <div class="flex justify-center mb-2">
                <div class="relative z-10 w-8 h-8 rounded-full ${dotBg} flex items-center justify-center shadow-sm">${inner}</div>
            </div>
            <div class="${lbl} leading-tight">${s}</div>
        </div>`;
    }).join('');

    // Attachments
    const attEl   = document.getElementById('fv-attachments');
    const attList = document.getElementById('fv-attachment-list');
    if (docs && docs.length > 0) {
        attList.innerHTML = docs.map(doc => {
            const name = doc.original_name || (doc.file_path ? doc.file_path.split('/').pop() : 'Document');
            const ext  = name.split('.').pop().toUpperCase();
            const size = doc.file_size ? (doc.file_size / 1024).toFixed(1) + ' KB' : '';
            return `<div class="flex items-center gap-3 p-2.5 bg-white dark:bg-slate-800 rounded-xl border border-blue-100 dark:border-blue-900/50">
                <div class="w-9 h-9 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i data-lucide="file" class="w-4 h-4 text-blue-600 dark:text-blue-400"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-slate-800 dark:text-white truncate">${escHtml(name)}</p>
                    <p class="text-[10px] text-slate-400">${ext}${size ? ' · ' + size : ''}${doc.uploaded_by_name ? ' · ' + escHtml(doc.uploaded_by_name) : ''}</p>
                </div>
                <a href="${escHtml(doc.file_path)}" target="_blank" rel="noopener"
                   class="px-2.5 py-1.5 text-[10px] font-semibold rounded-lg text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 transition-colors flex-shrink-0">
                    <i data-lucide="download" class="w-3.5 h-3.5 inline-block"></i> Download
                </a>
            </div>`;
        }).join('');
        attEl.classList.remove('hidden');
    } else {
        attEl.classList.add('hidden');
    }

    // Event count
    document.getElementById('fv-event-count').textContent =
        events.length + ' event' + (events.length !== 1 ? 's' : '') + ' · Chronological order';

    // Timeline nodes
    const tlContainer = document.getElementById('fv-timeline-nodes');
    tlContainer.innerHTML = '<div class="absolute left-5 top-5 bottom-0 w-0.5 bg-gradient-to-b from-slate-200 via-slate-200 to-transparent dark:from-slate-700 dark:via-slate-700"></div>';
    if (events.length === 0) {
        tlContainer.innerHTML += `<div class="text-center py-16 text-sm text-slate-400">No timeline events recorded yet.</div>`;
    } else {
        tlContainer.innerHTML += events.map((ev, i) => buildFvTimelineNode(ev, i, events.length)).join('');
    }

    // Show overlay
    overlay.style.removeProperty('display');
    overlay.style.display = 'flex';
    overlay.style.flexDirection = 'column';
    document.body.style.overflow = 'hidden';
    overlay.setAttribute('aria-hidden', 'false');
    requestAnimationFrame(() => { overlay.style.opacity = '1'; });
    lucide.createIcons();
}

// ── Close Full-Screen Overlay ──────────────────────────────────────
function closeFullScreen() {
    const overlay = document.getElementById('fullScreenOverlay');
    if (!overlay) return;
    overlay.style.opacity = '0';
    document.body.style.overflow = 'hidden'; // keep drawer scroll locked
    setTimeout(() => {
        overlay.style.display = 'none';
        overlay.setAttribute('aria-hidden', 'true');
    }, 250);
}

// ── Build Full-View timeline node (larger cards) ──────────────────
function buildFvTimelineNode(ev, idx, total) {
    const info      = getTlInfo(ev.event_type);
    const isLatest  = idx === total - 1;
    const isOverdue = ev.event_type === 'overdue';
    const isRejected   = ev.event_type === 'rejected';
    const isCompleted  = ev.event_type === 'completed';

    let changeBadge = '';
    if (ev.old_status || ev.new_status) {
        const oldS = ev.old_status || '—';
        const newS = ev.new_status || '—';
        changeBadge = `<div class="flex items-center gap-2 mb-3">
            <span class="text-[11px] font-mono font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">${escHtml(oldS)}</span>
            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400 flex-shrink-0"></i>
            <span class="text-[11px] font-mono font-semibold px-2.5 py-1 rounded-full ${getStatusClass(newS)}">${escHtml(newS)}</span>
        </div>`;
    }

    let specialSection = '';
    if (isRejected && ev.remarks) {
        specialSection = `<div class="mt-3 p-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/40">
            <p class="text-[11px] font-semibold text-red-600 dark:text-red-400 uppercase tracking-wider mb-1">Rejection Reason</p>
            <p class="text-sm text-red-700 dark:text-red-300">${escHtml(ev.remarks)}</p>
        </div>`;
    } else if (isCompleted && ev.remarks) {
        specialSection = `<div class="mt-3 p-3 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-900/40">
            <p class="text-[11px] font-semibold text-green-600 dark:text-green-400 uppercase tracking-wider mb-1">Completion Details</p>
            <p class="text-sm text-green-700 dark:text-green-300">${escHtml(ev.remarks)}</p>
        </div>`;
    }

    let remarksSection = '';
    if (!isRejected && !isCompleted && ev.remarks && ev.remarks !== ev.description) {
        remarksSection = `<div class="mt-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-900/30">
            <p class="text-[11px] font-semibold text-amber-600 dark:text-amber-400 mb-1">Remarks</p>
            <p class="text-sm text-amber-800 dark:text-amber-300">${escHtml(ev.remarks)}</p>
        </div>`;
    }

    const overduePulse = isOverdue ? ' ring-4 ring-red-300 dark:ring-red-900' : '';
    const latestBadge  = isLatest  ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 text-[9px] font-bold rounded-full bg-navy-600 text-white ml-2">LATEST</span>` : '';

    return `<div class="relative pl-16 pb-8" style="animation: fadeSlideIn 0.3s ease ${(idx * 0.04).toFixed(2)}s both">
        <div class="absolute left-0 top-0 w-10 h-10 rounded-full bg-gradient-to-br ${info.bg} flex items-center justify-center z-10 shadow-md${overduePulse}">
            <i data-lucide="${info.icon}" class="w-[18px] h-[18px] text-white"></i>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl border-l-4 border border-slate-200 dark:border-slate-700 p-5 shadow-sm hover:shadow-md transition-shadow"
             style="border-left-color: ${info.col}">
            <div class="absolute -top-2.5 left-18" style="left:4.5rem">
                <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 shadow-sm">${escHtml(info.label)}</span>
            </div>
            <div class="flex items-start justify-between gap-2 mb-2 mt-1">
                <h4 class="text-base font-bold text-slate-900 dark:text-white leading-tight">
                    ${escHtml(ev.title)}${latestBadge}
                </h4>
                ${isOverdue ? '<span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800 animate-pulse flex-shrink-0">⚠ OVERDUE</span>' : ''}
            </div>
            ${changeBadge}
            ${ev.description ? `<p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-3">${escHtml(ev.description)}</p>` : ''}
            ${specialSection}
            ${remarksSection}
            <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60">
                ${ev.actor_name ? `<div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center text-[10px] font-bold text-slate-600 dark:text-white flex-shrink-0">${escHtml((ev.actor_name||'S').charAt(0).toUpperCase())}</div>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">${escHtml(ev.actor_name)}</span>
                    ${ev.actor_desig ? `<span class="text-xs text-slate-400">· ${escHtml(ev.actor_desig)}</span>` : ''}
                </div>` : ''}
                ${ev.event_date ? `<div class="flex items-center gap-1.5 text-xs text-slate-400 dark:text-slate-500 font-mono ml-auto">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    ${fmtDate(ev.event_date)}
                </div>` : ''}
            </div>
        </div>
    </div>`;
}

// ── Close Modal ───────────────────────────────────────────────────
function closeTrackModal() {
    const modal    = document.getElementById('trackModal');
    const backdrop = document.getElementById('modalBackdrop');
    const panel    = document.getElementById('modalPanel');

    backdrop.style.opacity = '0';
    panel.style.transform  = 'translateX(100%)';
    document.body.style.overflow = '';

    setTimeout(() => {
        modal.style.pointerEvents = 'none';
        backdrop.style.pointerEvents = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }, 300);
}

// Close on Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        const fv = document.getElementById('fullScreenOverlay');
        if (fv && fv.style.display !== 'none' && fv.getAttribute('aria-hidden') !== 'true') {
            closeFullScreen();
        } else {
            closeTrackModal();
        }
    }
});

// ── Render Modal Content ──────────────────────────────────────────
function renderModal(task, events, docs) {
    const loader   = document.getElementById('modalLoader');
    const timeline = document.getElementById('modalTimeline');
    loader.style.display = 'none';



    // Header
    const taskNo = task.task_no || ('#' + task.task_id);
    document.getElementById('modalTaskNo').textContent    = taskNo;
    document.getElementById('modalTaskTitle').textContent = task.task_title || '—';

    // Details strip
    document.getElementById('mdTaskNo').textContent     = taskNo;
    document.getElementById('mdAssignedTo').textContent = task.assigned_employee || task.role_name || 'Unassigned';
    const pEl = document.getElementById('mdPriority');
    pEl.textContent = task.priority || '—';
    const pMap = { high:'text-red-600 dark:text-red-400', medium:'text-yellow-600 dark:text-yellow-400', low:'text-green-600 dark:text-green-400', critical:'text-purple-600 dark:text-purple-400' };
    pEl.className = 'text-sm font-bold ' + (pMap[(task.priority||'').toLowerCase()] || 'text-slate-600 dark:text-slate-300');
    document.getElementById('mdDueDate').textContent    = fmtDateShort(task.due_date);

    const descEl = document.getElementById('mdDesc');
    const descWrap = document.getElementById('mdDescWrap');
    if (task.task_description) {
        descEl.textContent = task.task_description;
        descWrap.classList.remove('hidden');
    }

    // Footer badge
    const status   = task.status || 'Pending';
    const statusBadge = document.getElementById('modalStatusBadge');
    statusBadge.textContent = status;
    statusBadge.className   = 'px-2.5 py-1 text-xs font-bold rounded-full ' + getStatusClass(status);

    // Store current task_id on panel for Full View navigation
    document.getElementById('modalPanel').dataset.taskId = task.task_id;

    // ── Status Progress Bar ───────────────────────────────────────
    const st = (status).toLowerCase();
    let flow = ['Pending', 'Assigned', 'In Progress', 'Completed'];
    if (st === 'rejected')  flow = ['Pending', 'Assigned', 'In Progress', 'Rejected'];
    if (st === 'on hold')   flow = ['Pending', 'Assigned', 'In Progress', 'On Hold', 'Completed'];
    if (st === 'escalated') flow = ['Pending', 'Assigned', 'In Progress', 'Escalated'];
    if (!flow.map(s=>s.toLowerCase()).includes(st)) flow.push(status);

    const curIdx = flow.findIndex(s => s.toLowerCase() === st);
    const pbEl   = document.getElementById('modalProgressBar');
    pbEl.innerHTML = flow.map((s, idx) => {
        const done   = idx < curIdx;
        const active = idx === curIdx;
        const sl     = s.toLowerCase();
        let style = getProgressStyle(sl);
        let dotBg = 'bg-slate-200 dark:bg-slate-700';
        let lbl   = 'text-slate-400 text-[10px]';
        let lineC = 'bg-slate-200 dark:bg-slate-700';
        
        if (idx <= curIdx) {
            dotBg = style.bg;
            lbl   = style.text + ' font-semibold text-[10px]';
            lineC = style.bg;
        }
        const line = idx > 0 ? `<div class="absolute top-3.5 right-1/2 left-0 h-0.5 ${lineC}"></div>` : '';
        const inner = (done || active)
                    ? `<i data-lucide="${style.icon}" class="w-3.5 h-3.5 text-white"></i>`
                    : `<div class="w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500"></div>`;
        return `<div class="flex-1 text-center relative">
                    ${line}
                    <div class="flex justify-center mb-1.5">
                        <div class="relative z-10 w-7 h-7 rounded-full ${dotBg} flex items-center justify-center shadow-sm">${inner}</div>
                    </div>
                    <div class="${lbl} leading-tight">${s}</div>
                </div>`;
    }).join('');

    // ── Attachments ───────────────────────────────────────────────
    const attEl   = document.getElementById('modalAttachments');
    const attList = document.getElementById('modalAttachmentList');
    if (docs && docs.length > 0) {
        attList.innerHTML = docs.map(doc => {
            const name = doc.original_name || (doc.file_path ? doc.file_path.split('/').pop() : 'Document');
            const ext  = name.split('.').pop().toUpperCase();
            const size = doc.file_size ? (doc.file_size / 1024).toFixed(1) + ' KB' : '';
            return `<div class="flex items-center gap-3 p-2 bg-white dark:bg-slate-800 rounded-lg border border-blue-100 dark:border-blue-900/50">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-md flex items-center justify-center flex-shrink-0">
                            <i data-lucide="file" class="w-4 h-4 text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-slate-800 dark:text-white truncate">${escHtml(name)}</p>
                            <p class="text-[10px] text-slate-400">${ext}${size ? ' · ' + size : ''}${doc.uploaded_by_name ? ' · ' + escHtml(doc.uploaded_by_name) : ''}</p>
                        </div>
                        <a href="${escHtml(doc.file_path)}" target="_blank" rel="noopener"
                           class="px-2 py-1 text-[10px] font-semibold rounded text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 transition-colors flex-shrink-0">
                            <i data-lucide="download" class="w-3 h-3 inline-block"></i>
                        </a>
                    </div>`;
        }).join('');
        attEl.classList.remove('hidden');
    }

    // ── Timeline Nodes ────────────────────────────────────────────
    document.getElementById('modalEventCount').textContent = events.length + ' event' + (events.length !== 1 ? 's' : '');

    const tlContainer = document.getElementById('tlNodes');
    if (events.length === 0) {
        tlContainer.innerHTML = `<div class="text-center py-10 text-sm text-slate-400">No timeline events recorded yet.</div>`;
    } else {
        const nodes = events.map((ev, i) => buildTimelineNode(ev, i, events.length)).join('');
        tlContainer.innerHTML =
            '<div class="absolute left-4 top-5 bottom-0 w-0.5 bg-gradient-to-b from-slate-200 via-slate-200 to-transparent dark:from-slate-700 dark:via-slate-700"></div>'
            + nodes;
    }

    timeline.classList.remove('hidden');
    lucide.createIcons();
}

// ── Build one timeline node ───────────────────────────────────────
function buildTimelineNode(ev, idx, total) {
    const info     = getTlInfo(ev.event_type);
    const isLatest = idx === total - 1;
    const isOverdue = ev.event_type === 'overdue';
    const isRejected = ev.event_type === 'rejected';
    const isCompleted = ev.event_type === 'completed';

    // Change badge (old → new)
    let changeBadge = '';
    if (ev.old_status || ev.new_status) {
        const oldS = ev.old_status || '—';
        const newS = ev.new_status || '—';
        changeBadge = `
            <div class="flex items-center gap-1.5 mb-2">
                <span class="text-[10px] font-mono font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">${escHtml(oldS)}</span>
                <i data-lucide="arrow-right" class="w-3 h-3 text-slate-400 flex-shrink-0"></i>
                <span class="text-[10px] font-mono font-semibold px-2 py-0.5 rounded-full ${getStatusClass(newS)}">${escHtml(newS)}</span>
            </div>`;
    }

    // Special section for rejected: show reason prominently
    let specialSection = '';
    if (isRejected && ev.remarks) {
        specialSection = `
            <div class="mt-2 p-2.5 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/40">
                <p class="text-[10px] font-semibold text-red-600 dark:text-red-400 uppercase tracking-wider mb-1">Rejection Reason</p>
                <p class="text-xs text-red-700 dark:text-red-300">${escHtml(ev.remarks)}</p>
            </div>`;
    }

    // Achievement/completion section
    if (isCompleted && ev.remarks) {
        specialSection = `
            <div class="mt-2 p-2.5 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-900/40">
                <p class="text-[10px] font-semibold text-green-600 dark:text-green-400 uppercase tracking-wider mb-1">Completion Details</p>
                <p class="text-xs text-green-700 dark:text-green-300">${escHtml(ev.remarks)}</p>
            </div>`;
    }

    // Remarks section (for other types)
    let remarksSection = '';
    if (!isRejected && !isCompleted && ev.remarks && ev.remarks !== ev.description) {
        remarksSection = `
            <div class="mt-2 p-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-900/30">
                <p class="text-[10px] font-semibold text-amber-600 dark:text-amber-400 mb-0.5">Remarks</p>
                <p class="text-xs text-amber-800 dark:text-amber-300">${escHtml(ev.remarks)}</p>
            </div>`;
    }

    const overduePulse = isOverdue ? ' ring-4 ring-red-300 dark:ring-red-900' : '';
    const latestBadge = isLatest ? `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[9px] font-bold rounded-full bg-navy-600 text-white ml-2">LATEST</span>` : '';

    return `
        <div class="relative pl-12 pb-7 tl-modal-node" style="animation: fadeSlideIn 0.3s ease ${(idx * 0.04).toFixed(2)}s both">
            <!-- Icon dot -->
            <div class="absolute left-0 top-0 w-8 h-8 rounded-full bg-gradient-to-br ${info.bg} flex items-center justify-center z-10 shadow-sm${overduePulse}">
                <i data-lucide="${info.icon}" class="w-[15px] h-[15px] text-white"></i>
            </div>

            <!-- Card -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border-l-4 border border-slate-200 dark:border-slate-700 p-4 shadow-sm hover:shadow-md transition-shadow"
                 style="border-left-color: ${info.col}">

                <!-- Event type label -->
                <div class="absolute -top-2 left-14">
                    <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 shadow-xs">
                        ${escHtml(info.label)}
                    </span>
                </div>

                <!-- Title + Latest badge -->
                <div class="flex items-start justify-between gap-2 mb-2 mt-1">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-tight">
                        ${escHtml(ev.title)}${latestBadge}
                    </h4>
                    ${isOverdue ? '<span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800 animate-pulse flex-shrink-0">⚠ OVERDUE</span>' : ''}
                </div>

                <!-- Old → New status change -->
                ${changeBadge}

                <!-- Description -->
                ${ev.description ? `<p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-2">${escHtml(ev.description)}</p>` : ''}

                <!-- Special sections (rejection/completion) -->
                ${specialSection}
                ${remarksSection}

                <!-- Meta: Actor + Date -->
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-3 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                    ${ev.actor_name ? `
                    <div class="flex items-center gap-1.5">
                        <div class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center text-[9px] font-bold text-slate-600 dark:text-white flex-shrink-0">
                            ${escHtml((ev.actor_name || 'S').charAt(0).toUpperCase())}
                        </div>
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">${escHtml(ev.actor_name)}</span>
                        ${ev.actor_desig ? `<span class="text-[10px] text-slate-400">· ${escHtml(ev.actor_desig)}</span>` : ''}
                    </div>` : ''}
                    ${ev.event_date ? `
                    <div class="flex items-center gap-1 text-[10px] text-slate-400 dark:text-slate-500 font-mono ml-auto">
                        <i data-lucide="clock" class="w-3 h-3"></i>
                        ${fmtDate(ev.event_date)}
                    </div>` : ''}
                </div>
            </div>
        </div>`;
}

// ── HTML escape helper ────────────────────────────────────────────
function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>
</body>
</html>
