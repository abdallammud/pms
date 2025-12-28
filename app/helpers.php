<?php
function escapeStr($str)
{
    return $GLOBALS['conn']->real_escape_string($str);
}
function escapePostData($postData)
{
    // Create an array to hold the escaped data
    $escapedData = [];

    // Loop through each key-value pair in $_POST
    foreach ($postData as $key => $value) {
        // Check if the value is an array
        if (is_array($value)) {
            // Recursively escape array values
            $escapedData[$key] = escapePostData($value);
        } else {
            // Escape the string and store it
            $escapedData[$key] = escapeStr($value);
        }
    }

    // Return the array of escaped data
    return $escapedData;
}

function get_data($table, array $fields)
{
    // Ensure the table name is safe
    // $allowedTables = ['company', 'branches', 'states']; // Define allowed tables
    // if (!in_array($table, $allowedTables)) {
    //     return false; // Prevent SQL injection by checking allowed tables
    // }

    // Start building the query
    $query = "SELECT * FROM `$table` WHERE ";
    $conditions = [];
    $params = [];

    // Build conditions based on the provided fields
    foreach ($fields as $key => $value) {
        $conditions[] = "`$key` = ?";
        $params[] = $value; // Store the value for binding
    }

    // Combine conditions into the query
    $query .= implode(' AND ', $conditions);

    // Prepare the statement
    if ($stmt = $GLOBALS['conn']->prepare($query)) {
        // Bind parameters dynamically
        $types = str_repeat('s', count($params)); // Assuming all values are strings; adjust if needed
        $stmt->bind_param($types, ...$params);

        // Execute the query
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // Fetch data
        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    return false; // Return false if no records are found or if an error occurs
}

// Function to check and create entities
function checkAndCreateEntity($table, $name, $userId, $class)
{
    $entity = get_data($table, ['name' => $name]);
    if (!$entity) {
        $data = ['name' => $name, 'added_by' => $userId];
        $id = $class->create($data);
        if (!$id)
            throw new Exception("Failed to create $table: $name");
        return $id;
    }
    return $entity[0]['id'];
}

function check_exists($table, $columns, $not = array())
{
    // Ensure the connection variable is set
    if (!isset($GLOBALS['conn'])) {
        echo json_encode(['error' => true, 'msg' => 'Database connection not established']);
        exit();
    }

    $conn = $GLOBALS['conn'];

    // Build the query
    $query = "SELECT * FROM $table WHERE ";
    $conditions = [];
    foreach ($columns as $column => $value) {
        $conditions[] = "$column = '$value'";
    }
    $query .= implode(' AND ', $conditions);

    if (count($not) > 0) {
        $query .= " AND ";
        $conditions = [];
        foreach ($not as $column => $value) {
            $conditions[] = "$column <> '$value'";
        }
        $query .= implode(' AND ', $conditions);
    }

    // Execute the query
    $result = mysqli_query($conn, $query);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            echo json_encode(['error' => true, 'msg' => 'Record already exists']);
            exit();
        } else {
            return true;
        }
    } else {
        echo json_encode(['error' => true, 'msg' => 'Database query error: ' . mysqli_error($conn)]);
        exit();
    }
}

function checkForeignKey($primaryId, $primaryName, $tables)
{
    global $conn; // Ensure the global connection is accessible

    // Loop through each table and check if the foreign key exists
    foreach ($tables as $table) {
        // Query to check if the primary ID exists as a foreign key in the table
        $query = "SELECT COUNT(*) AS count FROM `$table` WHERE `$primaryName` = ?";

        // Prepare and execute the query
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $primaryId); // Bind the ID parameter to the query
        $stmt->execute();

        // Get the query result
        $result = $stmt->get_result()->fetch_assoc();

        // Check if any rows exist with the given foreign key
        if ($result['count'] > 0) {
            // If the ID exists in any table, return error message in JSON format and exit
            echo json_encode(['error' => true, 'msg' => 'Cannot delete, record is in use']);
            exit();
        }
    }

    // If no foreign key was found, return true
    return true;
}


// Settings
function get_setting($type)
{
    $defaultValue = settingsArray();
    $setting = get_data('sys_settings', array('type' => $type));
    if (!$setting) {
        $setting = $defaultValue[$type];
    } else {
        $setting = $setting[0];
    }

    return $setting;
}


$GLOBALS['MAIL'] = [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'random.my.gm@gmail.com',
    'password' => 'esez afrv dnpe nukx', // ⚠️ Use App Password, not Gmail password
    'secure' => 'tls',        // 'tls' or 'ssl'
    'from' => 'random.my.gm@gmail.com',
    'fromName' => 'SUPPORT CENTER',
    'replyTo' => 'no-reply@yourdomain.com'
];
function settingsArray()
{
    $defaultValue = array(
        'staff_prefix' => [
            'type' => 'staff_prefix',
            'value' => 'SB',
            'section' => 'employees',
            'details' => 'Staff number prefix',
            'remarks' => ''
        ],
        'working_hours' => [
            'type' => 'working_hours',
            'value' => '8',
            'section' => 'payroll',
            'details' => 'Working hours per day',
            'remarks' => 'required'
        ],
        'working_days' => [
            'type' => 'working_days',
            'value' => '5',
            'section' => 'payroll',
            'details' => 'Working days per week',
            'remarks' => 'required'
        ],
        'time_in' => [
            'type' => 'time_in',
            'value' => '8:00 AM',
            'section' => 'payroll',
            'details' => 'Time in',
            'remarks' => 'required'
        ],
        'time_out' => [
            'type' => 'time_out',
            'value' => '5:00 PM',
            'section' => 'payroll',
            'details' => 'Time out',
            'remarks' => 'required'
        ],
        'overtime' => [
            'type' => 'overtime',
            'value' => 'Yes',
            'section' => 'payroll',
            'details' => 'Include Overtime in Payroll Calculations',
            'remarks' => 'required'
        ],
        'primary_color' => [
            'type' => 'primary_color',
            'value' => '#0d6efd',
            'section' => 'system',
            'details' => 'System primary color',
            'remarks' => 'required'
        ],
        'secondary_color' => [
            'type' => 'secondary_color',
            'value' => '#50b848',
            'section' => 'system',
            'details' => 'System secondary color',
            'remarks' => 'required'
        ],
        'system_logo' => [
            'type' => 'system_logo',
            'value' => 'logo.png',
            'section' => 'system',
            'details' => 'System Logo',
            'remarks' => 'required'
        ],
        'disabled_features' => [
            'type' => 'disabled_features',
            'value' => '[]',
            'section' => 'admin',
            'details' => 'Disabled features',
            'remarks' => 'required'
        ],
        'email_config' => [
            'type' => 'email_config',
            'value' => json_encode($GLOBALS['MAIL']),
            'section' => 'email',
            'details' => 'Email configuration',
            'remarks' => 'required'
        ],
        'dashboard_charts' => [
            'type' => 'dashboard_charts',
            'value' => '[]',
            'section' => 'admin',
            'details' => 'Dashboard charts',
            'remarks' => 'required'
        ]

    );

    // Retrieve settings from the database
    $dbSettings = getSettingsFromDb();

    // Merge the database settings with the default values
    // Override default values with those from the database if they exist
    foreach ($defaultValue as $key => &$setting) {
        if (isset($dbSettings[$key])) {
            $setting['value'] = $dbSettings[$key]['value']; // Override with DB value
        }
    }

    return $defaultValue;
}

function getSettingsFromDb()
{
    $settings = [];
    $fromDb = $GLOBALS['settingsClass']->read_all();

    foreach ($fromDb as $setting) {
        // Use the type as the key for merging with default settings
        $settings[$setting['type']] = array(
            'type' => $setting['type'],
            'value' => $setting['value'],
            'section' => $setting['section'],
            'details' => $setting['details'],
            'remarks' => $setting['remarks'],
        );
    }

    return $settings;
}

function getSettingsBySection($section)
{
    // Get the default settings and database settings merged together
    $settings = settingsArray();

    // Filter settings based on the section
    $filteredSettings = [];
    foreach ($settings as $key => $setting) {
        if ($setting['section'] === $section) {
            $filteredSettings[$key] = $setting;
        }
    }

    return $filteredSettings;
}

function sys_setting($type)
{
    // Get the default settings and database settings merged together
    $settings = settingsArray();
    if (isset($settings[$type])) {
        echo $settings[$type]['value'];
    } else {
        return false;
    }
}

function return_setting($type)
{
    // Get the default settings and database settings merged together
    $settings = settingsArray();
    if (isset($settings[$type])) {
        return $settings[$type]['value'];
    } else {
        return false;
    }
}

function select_active($table, $array = array('value' => 'id', 'text' => 'name'), $current = '')
{
    $options = '';
    $sql = "SELECT * FROM `$table` WHERE `status` = ?";
    $params = ['Active'];
    $types = 's';

    // Execute the query
    $activeRows = $GLOBALS['branchClass']->query($sql, $params, $types);

    // Check if any rows are returned
    if (count($activeRows) > 0) {
        // Loop through active rows and build the options
        if (!isset($array['text']))
            $array['text'] = 'name';
        if (!isset($array['value']))
            $array['value'] = 'id';
        foreach ($activeRows as $row) {
            $options .= '<option value="' . $row[$array['value']] . '" ';
            if ($current) {
                if ($row[$array['value']] == $current)
                    $options .= ' selected="selected"';
            }
            $options .= '>' . $row[$array['text']] . '</option>';
        }
    } else {
        // If no active rows are found, display a "No records found" option
        $options .= '<option value="" disabled>No records found</option>';
    }

    // Echo the generated options
    echo $options;
}

function select_all($table, $array = array('value' => 'id', 'text' => 'name'), $current = '')
{
    $options = '';
    $sql = "SELECT * FROM `$table`";


    // Execute the query
    $rows = $GLOBALS['branchClass']->query($sql);

    // Check if any rows are returned
    if (count($rows) > 0) {
        // Loop through active rows and build the options
        if (!isset($array['text']))
            $array['text'] = 'name';
        if (!isset($array['value']))
            $array['value'] = 'id';
        foreach ($rows as $row) {
            $options .= '<option value="' . $row[$array['value']] . '" ';
            if ($current) {
                if ($row[$array['value']] == $current)
                    $options .= ' selected="selected"';
            }
            $options .= '>' . $row[$array['text']] . '</option>';
        }
    } else {
        // If no active rows are found, display a "No records found" option
        $options .= '<option value="" disabled>No records found</option>';
    }

    // Echo the generated options
    echo $options;
}

function calculateEmployeeEarnings($employeeId, $payrollMonth)
{
    $conn = $GLOBALS['conn'];

    // Define earning types
    $earningTypes = ['Commission', 'Bonus', 'Allowance'];
    $earnings = array_fill_keys(array_map('strtolower', $earningTypes), 0);

    // Query to fetch earnings for the given employee and payroll month
    $query = "
        SELECT `transaction_type`, SUM(`amount`) AS total
        FROM `employee_transactions`
        WHERE `emp_id` = '$employeeId'
        AND `status` = 'Approved'
        AND DATE_FORMAT(`date`, '%Y-%m') = '$payrollMonth'
        AND `transaction_type` IN ('" . implode("', '", $earningTypes) . "')
        GROUP BY `transaction_type`
    ";

    // echo $query;
    $result = $conn->query($query);

    // Process the results
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $type = strtolower($row['transaction_type']);
            $earnings[$type] = (float) $row['total'];
        }
    }

    return $earnings;
}

function calculateEmployeeDeductions($employeeId, $payrollMonth)
{
    $conn = $GLOBALS['conn'];

    // Define deduction types
    $deductionTypes = ['Loan', 'Advance', 'Deduction'];
    $deductions = array_fill_keys(array_map('strtolower', $deductionTypes), 0);

    // Query to fetch deductions for the given employee and payroll month
    $query = "
        SELECT `transaction_type`, SUM(`amount`) AS total
        FROM `employee_transactions`
        WHERE `emp_id` = '$employeeId'
        AND `status` = 'Approved'
        AND DATE_FORMAT(`date`, '%Y-%m') = '$payrollMonth'
        AND `transaction_type` IN ('" . implode("', '", $deductionTypes) . "')
        GROUP BY `transaction_type`
    ";
    $result = $conn->query($query);

    // Process the results
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $type = strtolower($row['transaction_type']);
            $deductions[$type] = (float) $row['total'];
        }
    }

    return $deductions;
}

function calculateAttendanceStats($employeeId, $payrollMonth)
{
    $conn = $GLOBALS['conn'];

    // Initialize counters
    $stats = [
        'present_days' => 0,
        'paid_leave_days' => 0,
        'sick_days' => 0,
        'unpaid_leave_days' => 0,
        'holidays' => 0,
        'not_hired_days' => 0,
        'no_show_days' => 0,
        'total_days' => 0,
    ];

    // Query to fetch attendance details for the given employee and payroll month
    $query = "
        SELECT `status`, COUNT(*) AS count
        FROM `atten_details`
        WHERE `emp_id` = '$employeeId'
        AND DATE_FORMAT(`atten_date`, '%Y-%m') = '$payrollMonth'
        GROUP BY `status`
    ";
    $result = $conn->query($query);

    // Process the results
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            switch ($row['status']) {
                case 'P':
                    $stats['present_days'] += (int) $row['count'];
                    break;
                case 'PL':
                    $stats['paid_leave_days'] += (int) $row['count'];
                    break;
                case 'S':
                    $stats['sick_days'] += (int) $row['count'];
                    break;
                case 'UL':
                    $stats['unpaid_leave_days'] += (int) $row['count'];
                    break;
                case 'H':
                    $stats['holidays'] += (int) $row['count'];
                    break;
                case 'NH':
                    $stats['not_hired_days'] += (int) $row['count'];
                    break;
                case 'N':
                    $stats['no_show_days'] += (int) $row['count'];
                    break;
            }
        }
    }

    // Calculate total days in the month
    $startDate = "$payrollMonth-01";
    $totalDaysInMonth = date('t', strtotime($startDate));
    $stats['total_days'] = $totalDaysInMonth;

    return $stats;
}

/*function calculateTimeSheetHours($employeeId, $payrollMonth) {
    $workHoursPerDay = return_setting('working_hours');
    $conn = $GLOBALS['conn'];

    // Initialize counters for overtime and undertime
    $result = [
        'overtime_hours' => 0,
        'undertime_hours' => 0,
        'total_worked_hours' => 0
    ];

    // Query to fetch timesheet details for the given employee and payroll month
    $query = "
        SELECT `ts_date`, `time_in`, `time_out`
        FROM `timesheet_details`
        WHERE `emp_id` = '$employeeId'
        AND DATE_FORMAT(`ts_date`, '%Y-%m') = '$payrollMonth'
        AND `status` = 'P' -- Consider only 'Present' days for working hours
    ";
    $resultSet = $conn->query($query);

    if ($resultSet) {
        while ($row = $resultSet->fetch_assoc()) {
            $timeIn = $row['time_in'];
            $timeOut = $row['time_out'];

            // Skip if no valid time_in or time_out
            if ($timeIn === '00:00:00' || $timeOut === '00:00:00') {
                continue;
            }

            // Calculate worked hours for the day
            $timeInObj = new DateTime($timeIn);
            $timeOutObj = new DateTime($timeOut);
            $workedHours = $timeOutObj->diff($timeInObj)->h + $timeOutObj->diff($timeInObj)->i / 60;

            // Update total worked hours
            $result['total_worked_hours'] += $workedHours;

            // Determine overtime or undertime
            if ($workedHours > $workHoursPerDay) {
                $result['overtime_hours'] += $workedHours - $workHoursPerDay;
            } elseif ($workedHours < $workHoursPerDay) {
                $result['undertime_hours'] += $workHoursPerDay - $workedHours;
            }
        }
    }

    return $result;
}*/

function calculateTimeSheetHours($employeeId, $payrollMonth, $workHoursPerDay = 0)
{
    if ($workHoursPerDay == 0)
        $workHoursPerDay = return_setting('working_hours');
    $conn = $GLOBALS['conn'];

    // Initialize counters
    $result = [
        'net_hours' => 0, // Positive for overtime, negative for undertime
        'total_worked_hours' => 0
    ];

    // Query to fetch timesheet details for the given employee and payroll month
    $query = "
        SELECT `ts_date`, `time_in`, `time_out`
        FROM `timesheet_details`
        WHERE `emp_id` = '$employeeId'
        AND DATE_FORMAT(`ts_date`, '%Y-%m') = '$payrollMonth'
        AND `status` = 'P' -- Consider only 'Present' days for working hours
    ";
    $resultSet = $conn->query($query);

    if ($resultSet) {
        while ($row = $resultSet->fetch_assoc()) {
            $timeIn = $row['time_in'];
            $timeOut = $row['time_out'];

            // Skip if no valid time_in or time_out
            if ($timeIn === '00:00:00' || $timeOut === '00:00:00') {
                continue;
            }

            // Calculate worked hours for the day
            $timeInObj = new DateTime($timeIn);
            $timeOutObj = new DateTime($timeOut);
            $workedHours = $timeOutObj->diff($timeInObj)->h + $timeOutObj->diff($timeInObj)->i / 60;

            // Update total worked hours
            $result['total_worked_hours'] += $workedHours;

            // Calculate net hours (positive for overtime, negative for undertime)
            $result['net_hours'] += $workedHours - $workHoursPerDay;
        }
    }

    return $result;
}


function getTaxRate(float $amount, int $stateId): float
{
    // Fetch state information
    $stateInfo = get_data('states', ['id' => $stateId]);
    if (!$stateInfo || empty($stateInfo[0]['tax_grid'])) {
        return 0; // Return 0 if no state or tax grid found
    }

    $stateInfo = $stateInfo[0];
    $taxGridJson = $stateInfo['tax_grid'];
    $stampDutyRate = isset($stateInfo['stamp_duty']) ? (float) $stateInfo['stamp_duty'] : 0;

    // Decode the tax grid JSON
    $taxGrid = json_decode($taxGridJson, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($taxGrid)) {
        return 0; // Return 0 if JSON decoding fails
    }

    // Initialize non-taxable amount
    $nonTaxableAmount = 0;

    // Iterate through the tax grid
    foreach ($taxGrid as $taxBracket) {
        $min = isset($taxBracket['min']) ? (float) $taxBracket['min'] : 0;
        $max = isset($taxBracket['max']) ? (float) $taxBracket['max'] : PHP_FLOAT_MAX;
        $rate = isset($taxBracket['rate']) ? (float) $taxBracket['rate'] : 0;

        // Adjust non-taxable amount
        if ($rate == 0) {
            $nonTaxableAmount = $max;
        }

        // Check if the amount falls within the current tax bracket
        if ($amount >= $min && $amount <= $max) {
            // Calculate the taxable amount and tax
            $taxableAmount = max(0, $amount - $nonTaxableAmount);
            $calculatedTax = $taxableAmount * ($rate / 100);

            // Calculate stamp duty
            $stampDuty = $calculatedTax * ($stampDutyRate / 100);

            // Return the total tax including stamp duty
            return $calculatedTax + $stampDuty;
        }
    }

    return 0; // Default to 0 if no applicable tax bracket is found
}


function getTaxPercentage(float $amount, int $stateId): float
{
    // Simulating a database fetch for the state record
    $stateInfo = get_data('states', ['id' => $stateId]);
    if ($stateInfo) {
        $stateInfo = $stateInfo[0];
        // Return 0 if state not found or tax grid is empty
        if (!$stateInfo || empty($stateInfo['tax_grid'])) {
            return 0;
        }

        // Decode the tax grid JSON
        $taxGrid = json_decode($stateInfo['tax_grid'], true);

        // Return 0 if JSON decoding fails
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($taxGrid)) {
            return 0;
        }

        // Determine the applicable tax rate
        foreach ($taxGrid as $taxBracket) {
            $min = isset($taxBracket['min']) ? (float) $taxBracket['min'] : 0;
            $max = isset($taxBracket['max']) ? (float) $taxBracket['max'] : PHP_FLOAT_MAX;
            $rate = isset($taxBracket['rate']) ? (float) $taxBracket['rate'] : 0;

            if ($amount >= $min && $amount <= $max) {
                // Return the applicable tax rate (percentage)
                return $rate;
            }
        }
    }
    return 0;
}


// Table customize
function get_columns($table, $reqColumns = 'show_columns')
{
    $defaultColumns = '';
    if ($table == 'showpayrollDT')
        $defaultColumns = "staff_no, full_name, base_salary, earnings, deductions, tax, net_salary, status, action";
    $query = $GLOBALS['conn']->query("SELECT * FROM `table_customize` WHERE `dt_table` = '$table'");
    if ($query->num_rows > 0) {
        $columns = $query->fetch_assoc()[$reqColumns];
        if (!$columns)
            $columns = $defaultColumns;
        $columns = str_replace(" ", "", $columns);
        $columns = explode(",", $columns);
    }

    return $columns;
}


// Helper function for cached entity creation
function getCachedOrCreateEntity($table, $name, $userId, $classInstance, &$cache)
{
    if (empty($name))
        return null;

    $cacheKey = strtolower(trim($name));

    // Check cache first
    if (isset($cache[$table][$cacheKey])) {
        return $cache[$table][$cacheKey];
    }

    // Check database
    $stmt = $GLOBALS['conn']->prepare("SELECT id FROM `$table` WHERE name = ?");
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id = $row['id'];
        $stmt->close();
    } else {
        $stmt->close();
        // Create new entity
        $data = ['name' => $name, 'added_by' => $userId];
        $id = $classInstance->create($data);
    }

    // Cache the result
    $cache[$table][$cacheKey] = $id;
    return $id;
}

// Helper function for batch processing employees
function processBatchEmployees($batchData, $employeeClass, $userId)
{
    $result = ['success' => 0, 'errors' => 0, 'error_messages' => ''];

    try {
        $GLOBALS['conn']->begin_transaction();

        foreach ($batchData as $employeeData) {
            try {
                $employeeData['added_by'] = $userId;

                // Extract and remove arrays before inserting employee
                $budget_codesArray = isset($employeeData['budget_codes_array']) ? $employeeData['budget_codes_array'] : [];
                $projectsArray = isset($employeeData['projects_array']) ? $employeeData['projects_array'] : [];

                unset($employeeData['budget_codes_array'], $employeeData['projects_array'], $employeeData['row_number']);

                // Create the employee
                $empId = $employeeClass->create($employeeData);

                if ($empId) {
                    // Handle staff number
                    if (!isset($employeeData['staff_no']) || $employeeData['staff_no'] == return_setting('staff_prefix')) {
                        $staff_no = return_setting('staff_prefix') . $empId;
                        $employeeClass->update($empId, ['staff_no' => $staff_no]);
                    }

                    // Insert budget codes
                    foreach ($budget_codesArray as $code) {
                        $code = trim($code);
                        if ($code === '')
                            continue;
                        $code_id = getCachedOrCreateEntity('budget_codes', $code, $userId, $GLOBALS['budgetCodesClass'], $GLOBALS['entityCache']);
                        $stmt = $GLOBALS['conn']->prepare("INSERT INTO employee_budget_codes (emp_id, code_id) VALUES (?, ?)");
                        $stmt->bind_param("ii", $empId, $code_id);
                        $stmt->execute();
                        $stmt->close();
                    }

                    // Insert projects
                    foreach ($projectsArray as $proj) {
                        $proj = trim($proj);
                        if ($proj === '')
                            continue;
                        $proj_id = getCachedOrCreateEntity('projects', $proj, $userId, $GLOBALS['projectsClass'], $GLOBALS['entityCache']);
                        $stmt = $GLOBALS['conn']->prepare("INSERT INTO employee_projects (emp_id, project_id) VALUES (?, ?)");
                        $stmt->bind_param("ii", $empId, $proj_id);
                        $stmt->execute();
                        $stmt->close();
                    }

                    $result['success']++;
                } else {
                    $result['errors']++;
                    $result['error_messages'] .= " Failed to create employee at line {$employeeData['row_number']}.";
                }
            } catch (Exception $e) {
                $result['errors']++;
                $result['error_messages'] .= " Error at line {$employeeData['row_number']}: " . $e->getMessage();
            }
        }

        $GLOBALS['conn']->commit();
    } catch (Exception $e) {
        $GLOBALS['conn']->rollback();
        $result['errors'] += count($batchData);
        $result['error_messages'] .= " Batch processing failed: " . $e->getMessage();
    }

    return $result;
}

/**
 * Generate reference number for a module
 * Supports concurrency protection, year inclusion, and auto-reset
 */
function generate_reference_number($module)
{
    if (!isset($GLOBALS['conn'])) {
        return strtoupper(substr($module, 0, 3)) . '-' . date('Ymd') . rand(100, 999);
    }

    $conn = $GLOBALS['conn'];
    $conn->begin_transaction();

    try {
        // Lock the settings row for update to prevent race conditions
        $result = $conn->query("
            SELECT setting_value FROM system_settings
            WHERE setting_key = 'transaction_series'
            FOR UPDATE
        ");
        $row = $result->fetch_assoc();

        $current_year = date('Y');

        if ($row && !empty($row['setting_value'])) {
            $series = json_decode($row['setting_value'], true);

            // Check if the module exists in series, handle legacy 'invoice' key
            if (!isset($series[$module])) {
                if ($module == 'rent_invoice' && isset($series['invoice'])) {
                    // Migrate from legacy 'invoice' key
                    $series[$module] = $series['invoice'];
                    $series[$module]['prefix'] = 'RNT-';
                    $series[$module]['suffix'] = '';
                } elseif ($module == 'other_invoice') {
                    // Create new series for other invoices
                    $series[$module] = [
                        'prefix' => 'CHR-',
                        'suffix' => '',
                        'starting_number' => '00001',
                        'current_number' => 0
                    ];
                } elseif ($module == 'payment') {
                    $series[$module] = [
                        'prefix' => 'RCT-',
                        'suffix' => '',
                        'starting_number' => '00001',
                        'current_number' => 0
                    ];
                } elseif ($module == 'maintenance') {
                    $series[$module] = [
                        'prefix' => 'MNT-',
                        'suffix' => '',
                        'starting_number' => '00001',
                        'current_number' => 0
                    ];
                } else {
                    // Module not found, use fallback
                    $conn->commit();
                    return strtoupper(substr($module, 0, 3)) . '-' . date('Ymd') . rand(100, 999);
                }
            }

            $config = $series[$module];
            $prefix = $config['prefix'] ?? '';
            $suffix = $config['suffix'] ?? '';
            $starting = intval($config['starting_number'] ?? 1);
            $current = intval($config['current_number'] ?? 0);

            // Calculate next number
            $next_number = ($current > 0) ? $current + 1 : $starting;

            // Update current number
            $series[$module]['current_number'] = $next_number;
            $updated_series = $conn->real_escape_string(json_encode($series));
            $conn->query("UPDATE system_settings SET setting_value = '$updated_series' WHERE setting_key = 'transaction_series'");

            // Format number with leading zeros
            $formatted_number = str_pad($next_number, 5, '0', STR_PAD_LEFT);

            // Build simple reference
            $reference = $prefix . $formatted_number . $suffix;

            $conn->commit();
            return $reference;
        }

        $conn->commit();

    } catch (Exception $e) {
        $conn->rollback();
    }

    // Fallback
    return strtoupper(substr($module, 0, 3)) . '-' . date('Ymd') . rand(100, 999);
}
?>