<?php
$file = 'd:/xampp/htdocs/FileZillaFTP/source/diff/bu/pms/app/tenant_controller.php';
$content = file_get_contents($file);
$content = rtrim($content);
if (str_ends_with($content, '?>')) {
    $content = substr($content, 0, -2);
}

$newFunc = "\n\nfunction get_tenant_stats()\n{\n\tob_clean();\n\theader('Content-Type: application/json');\n\t\$conn = \$GLOBALS['conn'];\n\t\$org_where = tenant_where_clause();\n\n\t\$total = \$conn->query(\"SELECT COUNT(*) as count FROM tenants WHERE \$org_where\")->fetch_assoc()['count'] ?? 0;\n\t\$active = \$conn->query(\"SELECT COUNT(*) as count FROM tenants WHERE status = 'active' AND \$org_where\")->fetch_assoc()['count'] ?? 0;\n\t\$inactive = \$conn->query(\"SELECT COUNT(*) as count FROM tenants WHERE status = 'inactive' AND \$org_where\")->fetch_assoc()['count'] ?? 0;\n\n\techo json_encode([\n\t\t'error' => false,\n\t\t'stats' => [\n\t\t\tnumber_format(\$total),\n\t\t\tnumber_format(\$active),\n\t\t\tnumber_format(\$inactive)\n\t\t]\n\t]);\n}\n?>";

file_put_contents($file, $content . $newFunc);
echo "Fixed tenant_controller.php\n";
?>