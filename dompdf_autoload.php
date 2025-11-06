<?php
// Manual autoloader for Dompdf
spl_autoload_register(function ($class) {
    if (strpos($class, 'Dompdf\\') === 0) {
        $file = __DIR__ . '/dompdf/src/' . str_replace('\\', '/', substr($class, 7)) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

// Load required libraries
require_once 'dompdf/lib/html5lib/Parser.php';
require_once 'dompdf/lib/php-font-lib/src/FontLib/Autoloader.php';
require_once 'dompdf/lib/php-svg-lib/src/autoload.php';