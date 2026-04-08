<?php
chdir(__DIR__ . '/app');
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';
mysqli_report(MYSQLI_REPORT_OFF);
require_once('init.php');

$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("SET sql_mode=''");

// Ensure upload directory exists
$uploadDir = '../public/uploads/properties/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Clear old images
$conn->query("DELETE FROM `property_images`");
$conn->query("ALTER TABLE `property_images` AUTO_INCREMENT = 1");
array_map('unlink', glob("$uploadDir*.*"));

// Helper function to create a placeholder image
function createPlaceholderImage($text, $path, $width = 800, $height = 600)
{
    if (!function_exists('imagecreatetruecolor')) {
        // If GD not present, just create an empty file (silent fallback)
        file_put_contents($path, '');
        return;
    }

    $img = imagecreatetruecolor($width, $height);

    // Choose a premium dark color (e.g. 1d3354, 2a3b54...)
    $colors = [
        [29, 51, 84],
        [44, 62, 80],
        [52, 73, 94],
        [34, 49, 63],
        [30, 130, 76]
    ];
    $rc = $colors[array_rand($colors)];

    $bg = imagecolorallocate($img, $rc[0], $rc[1], $rc[2]);
    $fg = imagecolorallocate($img, 240, 240, 240);
    imagefilledrectangle($img, 0, 0, $width, $height, $bg);

    // Simple text centering (requires TTF for nice fonts, using built-in for fallback)
    $font = 5;
    $tWidth = imagefontwidth($font) * strlen($text);
    $tHeight = imagefontheight($font);
    imagestring($img, $font, ($width - $tWidth) / 2, ($height - $tHeight) / 2, $text, $fg);

    imagejpeg($img, $path, 90);
    imagedestroy($img);
}

$res = $conn->query("SELECT p.id, p.org_id, p.name, t.type_name as property_type FROM properties p LEFT JOIN property_types t ON t.id = p.type_id");
$props = [];
if ($res) {
    while ($row = $res->fetch_assoc())
        $props[] = $row;
} else {
    die("Failed to fetch properties: " . $conn->error);
}

$roomNames = ['Exterior View', 'Living Room', 'Kitchen Area', 'Master Bedroom', 'Bathroom'];

echo "Seeding images for " . count($props) . " properties...\n";
foreach ($props as $p) {
    $num_images = rand(4, 5);

    for ($i = 0; $i < $num_images; $i++) {
        $caption = ($i === 0) ? "Front View" : $roomNames[$i];
        $filename = "prop_{$p['id']}_img_{$i}.jpg";
        $localPath = $uploadDir . $filename;
        $dbPath = "public/uploads/properties/" . $filename;

        createPlaceholderImage($p['name'] . ' - ' . $caption, $localPath);

        $is_cover = ($i === 0) ? 1 : 0;
        $sql = "INSERT INTO property_images (org_id, property_id, image_path, is_cover, caption) 
                VALUES ({$p['org_id']}, {$p['id']}, '$dbPath', $is_cover, '$caption')";

        if (!$conn->query($sql)) {
            echo "Error inserting image: " . $conn->error . "\n";
        }

        if ($is_cover) {
            $conn->query("UPDATE properties SET logo = '$dbPath' WHERE id = {$p['id']}");
        }
    }
}

$conn->query("SET FOREIGN_KEY_CHECKS = 1");
echo "Image Seeding completed successfully.\n";
