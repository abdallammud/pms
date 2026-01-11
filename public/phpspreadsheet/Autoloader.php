<?php
/**
 * PHPSpreadsheet Autoloader
 * PSR-4 style autoloader for PhpSpreadsheet and its stub dependencies
 */

namespace PhpOffice\PhpSpreadsheet;

class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register([new self(), 'autoload']);
    }

    public static function autoload(string $class): void
    {
        // 1. Handle PhpOffice\PhpSpreadsheet classes
        // These are in the same folder as this Autoloader.php
        $prefix = 'PhpOffice\\PhpSpreadsheet\\';
        if (strpos($class, $prefix) === 0) {
            $relativeClass = substr($class, strlen($prefix));
            $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }

        // 2. Handle Stubs in the /src subfolder
        // (Psr\, Composer\, ZipStream\)
        $stubPrefixes = ['Psr\\', 'Composer\\', 'ZipStream\\'];
        foreach ($stubPrefixes as $prefix) {
            if (strpos($class, $prefix) === 0) {
                $file = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        }
    }
}
