<?php
// report_display.php
$report_type = $_GET['report_type'] ?? '';
$startDate = $_GET['startDate'] ?? '';
$endDate = $_GET['endDate'] ?? '';

// Format dates for display
$displayStart = !empty($startDate) ? date('M d, Y', strtotime($startDate)) : 'N/A';
$displayEnd = !empty($endDate) ? date('M d, Y', strtotime($endDate)) : 'N/A';

$reportTitle = "Report Results";
switch ($report_type) {
    case 'rent_collection':
        $reportTitle = "Rent Collection Report";
        break;
    case 'unit_occupancy':
        $reportTitle = "Unit Occupancy Report";
        break;
    case 'tenant_report':
        $reportTitle = "Tenant Report";
        break;
    case 'outstanding_balance':
        $reportTitle = "Outstanding Balance Report";
        break;
    case 'income_expense':
        $reportTitle = "Income vs Expense Report";
        break;
    case 'maintenance_report':
        $reportTitle = "Maintenance Report";
        break;
    case 'maintenance_expense':
        $reportTitle = "Maintenance Expense Report";
        break;
}
?>

<div class="container-fluid">
    <!-- Breadcrumbs/Back button -->
    <div class="mb-4">
        <a href="./reports" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to Reports
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <?php echo $reportTitle; ?>
            </h6>
            <div class="d-flex align-items-center">
                <span class=" p-2 mr-2">
                    <?php echo $displayStart; ?> -
                    <?php echo $displayEnd; ?>
                </span>
                <?php
                $excelParams = http_build_query([
                    'print' => $report_type,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'property_id' => $_GET['property_id'] ?? '',
                    'tenant_status' => $_GET['tenant_status'] ?? ''
                ]);
                ?>
                <a href="./excel.php?<?php echo $excelParams; ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> Download Excel
                </a>
                <a href="./pdf.php?<?php echo $excelParams; ?>" class="btn btn-danger btn-sm ms-1" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> Download PDF
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php
            require_once 'app/report_controller.php';
            $report = new ReportController();
            $data = [];

            if ($report_type == 'rent_collection') {
                $data = $report->getRentCollectionData($_GET);
            } elseif ($report_type == 'unit_occupancy') {
                $data = $report->getUnitOccupancyData($_GET);
            } elseif ($report_type == 'tenant_report') {
                $data = $report->getTenantReportData($_GET);
            } elseif ($report_type == 'outstanding_balance') {
                $data = $report->getOutstandingBalanceData($_GET);
            } elseif ($report_type == 'income_expense') {
                $data = $report->getIncomeExpenseData($_GET);
            } elseif ($report_type == 'maintenance_report') {
                $data = $report->getMaintenanceReportData($_GET);
            } elseif ($report_type == 'maintenance_expense') {
                $data = $report->getMaintenanceExpenseData($_GET);
            }
            ?>
            <div class="table-responsive">
                <table class="table table-bordered datatable" id="report-table" width="100%" cellspacing="0">
                    <thead>
                        <?php if ($report_type == 'rent_collection'): ?>
                            <tr>
                                <th>Date</th>
                                <th>Receipt #</th>
                                <th>Tenant</th>
                                <th>Property</th>
                                <th>Unit</th>
                                <th>Amount</th>
                            </tr>
                        <?php elseif ($report_type == 'unit_occupancy'): ?>
                            <tr>
                                <th>Property</th>
                                <th>Unit #</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Tenant</th>
                            </tr>
                        <?php elseif ($report_type == 'tenant_report'): ?>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Joined Date</th>
                            </tr>
                        <?php elseif ($report_type == 'outstanding_balance'): ?>
                            <tr>
                                <th>Invoice #</th>
                                <th>Due Date</th>
                                <th>Tenant</th>
                                <th>Property</th>
                                <th>Unit</th>
                                <th>Balance</th>
                                <th>Status</th>
                            </tr>
                        <?php elseif ($report_type == 'income_expense'): ?>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Property</th>
                                <th>Description</th>
                                <th>Amount</th>
                            </tr>
                        <?php elseif ($report_type == 'maintenance_report'): ?>
                            <tr>
                                <th>Ref #</th>
                                <th>Date</th>
                                <th>Property</th>
                                <th>Unit</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Description</th>
                            </tr>
                        <?php elseif ($report_type == 'maintenance_expense'): ?>
                            <tr>
                                <th>Date</th>
                                <th>Ref #</th>
                                <th>Property</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Description</th>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <th>Details</th>
                                <th>Ref</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Amount</th>
                            </tr>
                        <?php endif; ?>
                    </thead>
                    <tbody>
                        <?php
                        $totalAmount = 0;
                        foreach ($data as $row):
                            if ($report_type == 'rent_collection') {
                                $totalAmount += $row['amount_paid'];
                                ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($row['received_date'])); ?></td>
                                    <td><?php echo $row['receipt_number']; ?></td>
                                    <td><?php echo $row['tenant_name']; ?></td>
                                    <td><?php echo $row['property_name']; ?></td>
                                    <td><?php echo $row['unit_number']; ?></td>
                                    <td class="text-right font-weight-bold">
                                        $<?php echo number_format($row['amount_paid'], 2); ?></td>
                                </tr>
                                <?php
                            } elseif ($report_type == 'unit_occupancy') {
                                $statusClass = 'secondary';
                                if ($row['status'] == 'occupied')
                                    $statusClass = 'success';
                                elseif ($row['status'] == 'vacant')
                                    $statusClass = 'info';
                                elseif ($row['status'] == 'maintenance')
                                    $statusClass = 'warning';
                                ?>
                                <tr>
                                    <td><?php echo $row['property_name']; ?></td>
                                    <td><?php echo $row['unit_number']; ?></td>
                                    <td><?php echo $row['unit_type']; ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $statusClass; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $row['tenant_name'] ?: '<i class="text-muted">No tenant</i>'; ?></td>
                                </tr>
                                <?php
                            } elseif ($report_type == 'tenant_report') {
                                ?>
                                <tr>
                                    <td><?php echo $row['full_name']; ?></td>
                                    <td><?php echo $row['phone']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td>
                                        <span
                                            class="badge badge-<?php echo $row['status'] == 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                </tr>
                                <?php
                            } elseif ($report_type == 'outstanding_balance') {
                                $totalAmount += $row['amount'];
                                $isOverdue = (strtotime($row['due_date']) < time());
                                ?>
                                <tr>
                                    <td><?php echo $row['invoice_number']; ?></td>
                                    <td class="<?php echo $isOverdue ? 'text-danger font-weight-bold' : ''; ?>">
                                        <?php echo date('M d, Y', strtotime($row['due_date'])); ?>
                                        <?php if ($isOverdue): ?> <i class="fas fa-exclamation-triangle"></i> <?php endif; ?>
                                    </td>
                                    <td><?php echo $row['tenant_name']; ?></td>
                                    <td><?php echo $row['property_name']; ?></td>
                                    <td><?php echo $row['unit_number']; ?></td>
                                    <td class="text-right">$<?php echo number_format($row['amount'], 2); ?></td>
                                    <td>
                                        <span
                                            class="badge badge-<?php echo $row['status'] == 'partial' ? 'warning' : 'danger'; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php
                            } elseif ($report_type == 'income_expense') {
                                $totalAmount += $row['amount'];
                                $isExpense = $row['type'] == 'Expense';
                                ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($row['trans_date'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $isExpense ? 'danger' : 'success'; ?>">
                                            <?php echo $row['type']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $row['property_name']; ?></td>
                                    <td><?php echo $row['details']; ?></td>
                                    <td
                                        class="text-right <?php echo $isExpense ? 'text-danger' : 'text-success'; ?> font-weight-bold">
                                        <?php echo ($isExpense ? '-' : '') . '$' . number_format(abs($row['amount']), 2); ?>
                                    </td>
                                </tr>
                                <?php
                            } elseif ($report_type == 'maintenance_report') {
                                $priorityClass = 'secondary';
                                if ($row['priority'] == 'high')
                                    $priorityClass = 'danger';
                                elseif ($row['priority'] == 'medium')
                                    $priorityClass = 'warning';
                                elseif ($row['priority'] == 'low')
                                    $priorityClass = 'info';

                                $statusClass = 'secondary';
                                if ($row['status'] == 'completed')
                                    $statusClass = 'success';
                                elseif ($row['status'] == 'in_progress')
                                    $statusClass = 'primary';
                                elseif ($row['status'] == 'new')
                                    $statusClass = 'info';
                                ?>
                                <tr>
                                    <td><?php echo $row['reference_number']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td><?php echo $row['property_name']; ?></td>
                                    <td><?php echo $row['unit_number'] ?: 'N/A'; ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $priorityClass; ?>">
                                            <?php echo ucfirst($row['priority']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $statusClass; ?>">
                                            <?php echo str_replace('_', ' ', ucfirst($row['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $row['description']; ?></td>
                                </tr>
                                <?php
                            } elseif ($report_type == 'maintenance_expense') {
                                $totalAmount += $row['amount'];
                                ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($row['expense_date'])); ?></td>
                                    <td><?php echo $row['reference_number'] ?: 'N/A'; ?></td>
                                    <td><?php echo $row['property_name']; ?></td>
                                    <td><?php echo $row['category']; ?></td>
                                    <td class="text-right text-danger font-weight-bold">
                                        $<?php echo number_format($row['amount'], 2); ?>
                                    </td>
                                    <td><?php echo $row['description']; ?></td>
                                </tr>
                                <?php
                            }
                        endforeach;
                        ?>
                    </tbody>
                    <?php if ($report_type == 'rent_collection'): ?>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-right">Total Collected:</th>
                                <th class="text-right">$<?php echo number_format($totalAmount, 2); ?></th>
                            </tr>
                        </tfoot>
                    <?php elseif ($report_type == 'outstanding_balance'): ?>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-right">Total Outstanding:</th>
                                <th class="text-right text-danger">$<?php echo number_format($totalAmount, 2); ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    <?php elseif ($report_type == 'income_expense'): ?>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-right">Net Balance:</th>
                                <th class="text-right <?php echo $totalAmount < 0 ? 'text-danger' : 'text-success'; ?>">
                                    $<?php echo number_format($totalAmount, 2); ?>
                                </th>
                            </tr>
                        </tfoot>
                    <?php elseif ($report_type == 'maintenance_expense'): ?>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-right">Total Maintenance Expense:</th>
                                <th class="text-right text-danger">$<?php echo number_format($totalAmount, 2); ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#report-table').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            pageLength: 25
        });
    });
</script>