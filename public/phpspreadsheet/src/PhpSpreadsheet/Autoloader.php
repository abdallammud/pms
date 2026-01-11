<?php
/**
 * PHPSpreadsheet Autoloader
 * PSR-4 style autoloader for PhpSpreadsheet and dependencies
 */

namespace PhpOffice\PhpSpreadsheet;

class Autoloader
{
    /** @const string */
    const NAMESPACE_PREFIX = 'PhpOffice\\PhpSpreadsheet\\';

    /** @const string */
    const PSR_PREFIX = 'Psr\\';

    /** @const string */
    const COMPOSER_PREFIX = 'Composer\\';

    public static function register(): void
    {
        spl_autoload_register([new self(), 'autoload']);
    }

    public static function autoload(string $class): void
    {
        // Handle PhpSpreadsheet classes
        $prefixLength = strlen(self::NAMESPACE_PREFIX);
        if (0 === strncmp(self::NAMESPACE_PREFIX, $class, $prefixLength)) {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, $prefixLength));
            $file = realpath(__DIR__ . (empty($file) ? '' : DIRECTORY_SEPARATOR) . $file . '.php');
            if ($file && file_exists($file)) {
                require_once $file;
                return;
            }
        }

        // Handle Psr classes (for SimpleCache etc.)
        $psrPrefixLength = strlen(self::PSR_PREFIX);
        if (0 === strncmp(self::PSR_PREFIX, $class, $psrPrefixLength)) {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
            $baseDir = dirname(__DIR__); // Go up to src folder
            $file = $baseDir . DIRECTORY_SEPARATOR . $file . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }

        // Handle Composer classes (for Pcre etc.)
        $composerPrefixLength = strlen(self::COMPOSER_PREFIX);
        if (0 === strncmp(self::COMPOSER_PREFIX, $class, $composerPrefixLength)) {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
            $baseDir = dirname(__DIR__); // Go up to src folder
            $file = $baseDir . DIRECTORY_SEPARATOR . $file . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
}
