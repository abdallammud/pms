<?php
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    switch ($action) {
        case 'get_amenities':       get_amenities();         break;
        case 'get_amenity':         get_amenity();           break;
        case 'save':                save_amenity();          break;
        case 'delete':              delete_amenity();        break;
        case 'get_active_amenities':get_active_amenities();  break;
    }
}

function get_amenities()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $draw   = $_POST['draw']   ?? 1;
    $start  = $_POST['start']  ?? 0;
    $length = $_POST['length'] ?? 10;
    $search = $_POST['search']['value'] ?? '';

    $sql = "SELECT * FROM amenities WHERE " . tenant_where_clause();

    if (!empty($search)) {
        $s = $conn->real_escape_string($search);
        $sql .= " AND (name LIKE '%$s%')";
    }

    $total    = $conn->query("SELECT COUNT(*) as c FROM amenities WHERE " . tenant_where_clause())->fetch_assoc()['c'] ?? 0;
    $fres     = $conn->query(preg_replace('/SELECT\b.*?\bFROM/is', 'SELECT COUNT(*) as c FROM', $sql, 1));
    $filtered = $fres ? ($fres->fetch_assoc()['c'] ?? 0) : 0;

    $order_dir = strtolower($_POST['order'][0]['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
    $sql .= " ORDER BY name $order_dir LIMIT $start, $length";

    $result = $conn->query($sql);
    $data   = [];

    while ($row = $result->fetch_assoc()) {
        $badge = $row['status'] === 'active'
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';

        $iconHtml = $row['icon'] ? '<i class="bi ' . htmlspecialchars($row['icon']) . ' me-1"></i>' : '';

        $actions  = '<button class="btn btn-sm btn-primary me-1" onclick="editAmenity(' . $row['id'] . ')"><i class="bi bi-pencil"></i></button>';
        $actions .= '<button class="btn btn-sm btn-danger" onclick="deleteAmenity(' . $row['id'] . ')"><i class="bi bi-trash"></i></button>';

        $data[] = [
            'name'       => $iconHtml . htmlspecialchars($row['name']),
            'icon'       => htmlspecialchars($row['icon'] ?? ''),
            'status'     => $badge,
            'created_at' => date('M d, Y', strtotime($row['created_at'])),
            'actions'    => $actions,
        ];
    }

    echo json_encode(['draw' => intval($draw), 'recordsTotal' => intval($total), 'recordsFiltered' => intval($filtered), 'data' => $data]);
}

function get_amenity()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];
    $id   = (int)($_GET['id'] ?? 0);

    $stmt = $conn->prepare("SELECT * FROM amenities WHERE id = ? AND " . tenant_where_clause());
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode($stmt->get_result()->fetch_assoc());
}

function save_amenity()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn   = $GLOBALS['conn'];
    $id     = (int)($_POST['amenity_id'] ?? 0);
    $name   = trim($_POST['name'] ?? '');
    $icon   = trim($_POST['icon'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';
    $org_id = resolve_request_org_id();

    if (empty($name)) {
        echo json_encode(['error' => true, 'msg' => 'Amenity name is required.']);
        exit;
    }

    if ($id === 0) {
        $dup = $conn->prepare("SELECT id FROM amenities WHERE name = ? AND org_id = ?");
        $dup->bind_param("si", $name, $org_id);
        $dup->execute();
        if ($dup->get_result()->num_rows > 0) {
            echo json_encode(['error' => true, 'msg' => 'Amenity name already exists.']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO amenities (org_id, name, icon, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $org_id, $name, $icon, $status);
        echo $stmt->execute()
            ? json_encode(['error' => false, 'msg' => 'Amenity added.'])
            : json_encode(['error' => true, 'msg' => 'DB error: ' . $conn->error]);
    } else {
        $dup = $conn->prepare("SELECT id FROM amenities WHERE name = ? AND id != ? AND org_id = ?");
        $dup->bind_param("sii", $name, $id, $org_id);
        $dup->execute();
        if ($dup->get_result()->num_rows > 0) {
            echo json_encode(['error' => true, 'msg' => 'Amenity name already exists.']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE amenities SET name=?, icon=?, status=? WHERE id=? AND " . tenant_where_clause());
        $stmt->bind_param("sssi", $name, $icon, $status, $id);
        echo $stmt->execute()
            ? json_encode(['error' => false, 'msg' => 'Amenity updated.'])
            : json_encode(['error' => true, 'msg' => 'DB error: ' . $conn->error]);
    }
}

function delete_amenity()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];
    $id   = (int)($_POST['id'] ?? 0);

    $stmt = $conn->prepare("DELETE FROM amenities WHERE id = ? AND " . tenant_where_clause());
    $stmt->bind_param("i", $id);
    echo $stmt->execute()
        ? json_encode(['error' => false, 'msg' => 'Amenity deleted.'])
        : json_encode(['error' => true, 'msg' => 'DB error: ' . $conn->error]);
}

function get_active_amenities()
{
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];
    $res  = $conn->query("SELECT id, name, icon FROM amenities WHERE status = 'active' AND " . tenant_where_clause() . " ORDER BY name");
    $out  = [];
    while ($r = $res->fetch_assoc()) $out[] = $r;
    echo json_encode($out);
}
