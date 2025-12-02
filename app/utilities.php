<?php 
function baseUri() {
    // Check if we are on HTTPS or HTTP
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    
    // Get the server name and the current path
    $host = $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    
    // Determine if it's a live environment or a local project directory
    if ($host === 'localhost' || $host === '127.0.0.1') {
        // For local development (e.g., subfolder)
        $baseUri = $protocol . $host . $path;
    } else {
        // For production (e.g., domain)
        $baseUri = $protocol . $host;
    }

    $GLOBALS['baseUri'] = $baseUri;

    return $baseUri;
}

function load_js_module() {
    if(isset($_GET['menu'])) {
        $menu = $_GET['menu'];
        if($menu == 'dashboard') {
            echo '<script src="public/plugins/chartjs/js/chart.js"></script>
                <script src="public/js/dashboard2.js"></script>';
        } else {
            echo '<script type="text/javascript" src="'.baseUri().'/public/js/modules/'.$menu.'.js"></script>';
        }
        
    }
}
function json($array) {
    echo json_encode($array);
}


function formatMoney($amount, $currencySymbol = '$', $decimals = 2) {
    $formattedAmount = number_format($amount, $decimals, '.', ',');
    return $currencySymbol . $formattedAmount;
}

function usernameFromEmail($email) {
    $username = strtok($email, '@');
    return $username;
}

function formatDate($dateString, $format = 'name', $time = false) {
    $date = new DateTime($dateString);

    if (!$date) {
        return "Invalid Date";
    }

    switch ($format) {
        case 'name':
            $return = $date->format('F d, Y');
            break;
        case '-':
            $return = $date->format('d-m-Y');
            break;
        case '/':
            $return = $date->format('d/m/Y');
            break;
        default:
            $return = $date->format('F d, Y');
    }

    if ($time) {
        $return .= ' ' . $date->format('H:i');
    }

    return $return;
}

function getDateDifference($startDate, $endDate) {
    // Convert the dates into DateTime objects
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    
    // Calculate the difference
    $difference = $start->diff($end);
    
    // Extract months and days
    $months = $difference->m + ($difference->y * 12); // Total months, including years converted to months
    $days = $difference->d; // Remaining days
    
    // Get total days
    $totalDays = $start->diff($end)->days;

    // Format the result
    $result = [];
    if ($months > 0) {
        $result[] = "$months month" . ($months > 1 ? 's' : '');
    }
    if ($days > 0 || empty($result)) {
        $result[] = "$days day" . ($days > 1 ? 's' : '');
    }

    // Return the result as an array
    return [
        'description' => implode(' and ', $result), // Human-readable string
        'totalDays' => $totalDays // Total number of days
    ];
}

function getWorkdaysInMonth($yearMonth, $workdaysInWeek = 0) {
    if($workdaysInWeek == 0) $workdaysInWeek = return_setting('working_days');
    // Validate the input format
    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $yearMonth)) {
        throw new InvalidArgumentException("Invalid date format. Use 'Y-m' (e.g., '2024-12').");
    }

    // Extract year and month
    list($year, $month) = explode('-', $yearMonth);

    // Validate the workdaysInWeek
    if ($workdaysInWeek < 1 || $workdaysInWeek > 7) {
        // throw new InvalidArgumentException("Workdays in a week must be between 1 and 7.");
        $workdaysInWeek = return_setting('working_days');
    }

    // Get the first and last days of the month
    $firstDayOfMonth = strtotime("$year-$month-01");
    $lastDayOfMonth = strtotime(date('Y-m-t', $firstDayOfMonth));

    // Initialize variables
    $workdays = 0;
    $weekdays = range(1, $workdaysInWeek); // 1 = Monday, 7 = Sunday

    // Loop through each day of the month
    for ($currentDay = $firstDayOfMonth; $currentDay <= $lastDayOfMonth; $currentDay = strtotime('+1 day', $currentDay)) {
        // Get the numeric representation of the day of the week (1 = Monday, 7 = Sunday)
        $dayOfWeek = date('N', $currentDay);

        // Check if it's a workday
        if (in_array($dayOfWeek, $weekdays)) {
            $workdays++;
        }
    }

    return $workdays;
}

function formatYearMonths($input) {
    // Split the input string into an array of year-month values
    $yearMonths = array_map('trim', explode(',', $input));
    $formatted = [];
    $currentGroup = [];
    $lastYear = null;

    foreach ($yearMonths as $yearMonth) {
        // Parse the year and month
        [$year, $month] = explode('-', $yearMonth);
        $monthName = date("F", mktime(0, 0, 0, $month, 1));
        
        if ($lastYear !== null && $year !== $lastYear) {
            // If the year changes, finalize the current group with its year
            $formatted[] = implode(', ', $currentGroup) . " $lastYear";
            $currentGroup = [];
        }

        $currentGroup[] = $monthName;
        $lastYear = $year;
    }

    // Append the final group
    if (!empty($currentGroup)) {
        $formatted[] = implode(', ', $currentGroup) . " $lastYear";
    }

    return implode(', ', $formatted);
}

function hexToRgb($color) {
    // Check if the input is a valid hex code
    if (preg_match('/^#?([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $color, $matches)) {
        // Remove '#' if present
        $hex = ltrim($color, '#');

        // Convert 3-digit hex to 6-digit
        if (strlen($hex) == 3) {
            $hex = preg_replace('/(.)/', '$1$1', $hex);
        }

        // Convert hex to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "rgb($r, $g, $b)";
    }

    // Return false if the input is not a valid hex code
    return $color;
}



?>