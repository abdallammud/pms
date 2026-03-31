<?php
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    switch ($action) {
        case 'get_unit_types':     get_unit_types();     break;
        case 'get_unit_type':      get_unit_type();      break;
        case 'save':               save_unit_type();     break;
        case 'delete':             delete_unit_type();   break;
        case 'get_active_types':   get_active_unit_types(); break;
    }
}

function get_unit_types()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $draw   = $_POST['draw']   ?? 1;
    $start  = $_POST['start']  ?? 0;
    $length = $_POST['length'] ?? 10;
    $search = $_POST['search']['value'] ?? '';

    $sql = "SELECT * FROM unit_types WHERE " . tenant_where_clause();

    if (!empty($search)) {
        $s = $conn->real_escape_string($search);
        $sql .= " AND (type_name LIKE '%$s%' OR description LIKE '%$s%')";
    }

    $total = $conn->query("SELECT COUNT(*) as c FROM unit_types WHERE " . tenant_where_clause())->fetch_assoc()['c'] ?? 0;

    $fres = $conn->query(preg_replace('/SELECT\b.*?\bFROM/is', 'SELECT COUNT(*) as c FROM', $sql, 1));
    $filtered = $fres ? ($fres->fetch_assoc()['c'] ?? 0) : 0;

    $order_col = $_POST['order'][0]['column'] ?? 0;
    $order_dir = strtolower($_POST['order'][0]['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
    $cols      = ['type_name', 'description', 'status', 'created_at'];
    $sql .= " ORDER BY " . ($cols[$order_col] ?? 'type_name') . " $order_dir LIMIT $start, $length";

    $result = $conn->query($sql);
    $data   = [];

    while ($row = $result->fetch_assoc()) {
        $badge = $row['status'] === 'active'
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';

        $actions  = '<button class="btn btn-sm btn-primary me-1" onclick="editUnitType(' . $row['id'] . ')"><i class="bi bi-pencil"></i></button>';
        $actions .= '<button class="btn btn-sm btn-danger" onclick="deleteUnitType(' . $row['id'] . ')"><i class="bi bi-trash"></i></button>';

        $data[] = [
            'type_name'   => htmlspecialchars($row['type_name']),
            'description' => htmlspecialchars($row['description'] ?? ''),
            'status'      => $badge,
            'created_at'  => date('M d, Y', strtotime($row['created_at'])),
            'actions'     => $actions,
        ];
    }

    echo json_encode(['draw' => intval($draw), 'recordsTotal' => intval($total), 'recordsFiltered' => intval($filtered), 'data' => $data]);
}

function get_unit_type()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];
    $id   = (int)($_GET['id'] ?? 0);

    $stmt = $conn->prepare("SELECT * FROM unit_types WHERE id = ? AND " . tenant_where_clause());
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode($stmt->get_result()->fetch_assoc());
}

function save_unit_type()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn      = $GLOBALS['conn'];
    $id        = (int)($_POST['unit_type_id'] ?? 0);
    $type_name = trim($_POST['type_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status    = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';
    $org_id    = resolve_request_org_id();

    if (empty($type_name)) {
        echo json_encode(['error' => true, 'msg' => 'Type name is required.']);
        exit;
    }

    if ($id === 0) {
        $dup = $conn->prepare("SELECT id FROM unit_types WHERE type_name = ? AND org_id = ?");
        $dup->bind_param("si", $type_name, $org_id);
        $dup->execute();
        if ($dup->get_result()->num_rows > 0) {
            echo json_encode(['error' => true, 'msg' => 'Unit type name already exists.']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO unit_types (org_id, type_name, description, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $org_id, $type_name, $description, $status);
        echo $stmt->execute()
            ? json_encode(['error' => false, 'msg' => 'Unit type added.'])
            : json_encode(['error' => true, 'msg' => 'DB error: ' . $conn->error]);
    } else {
        $dup = $conn->prepare("SELECT id FROM unit_types WHERE type_name = ? AND id != ? AND org_id = ?");
        $dup->bind_param("sii", $type_name, $id, $org_id);
        $dup->execute();
        if ($dup->get_result()->num_rows > 0) {
            echo json_encode(['error' => true, 'msg' => 'Unit type name already exists.']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE unit_types SET type_name=?, description=?, status=? WHERE id=? AND " . tenant_where_clause());
        $stmt->bind_param("sssi", $type_name, $description, $status, $id);
        echo $stmt->execute()
            ? json_encode(['error' => false, 'msg' => 'Unit type updated.'])
            : json_encode(['error' => true, 'msg' => 'DB error: ' . $conn->error]);
    }
}

function delete_unit_type()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];
    $id   = (int)($_POST['id'] ?? 0);

    $chk = $conn->prepare("SELECT id FROM units WHERE unit_type_id = ? AND " . tenant_where_clause() . " LIMIT 1");
    $chk->bind_param("i", $id);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        echo json_encode(['error' => true, 'msg' => 'Cannot delete. This unit type is in use.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM unit_types WHERE id = ? AND " . tenant_where_clause());
    $stmt->bind_param("i", $id);
    echo $stmt->execute()
        ? json_encode(['error' => false, 'msg' => 'Unit type deleted.'])
        : json_encode(['error' => true, 'msg' => 'DB error: ' . $conn->error]);
}

function get_active_unit_types()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];
    $res  = $conn->query("SELECT id, type_name FROM unit_types WHERE status = 'active' AND " . tenant_where_clause() . " ORDER BY type_name");
    $out  = [];
    while ($r = $res->fetch_assoc()) $out[] = $r;
    echo json_encode($out);
}
