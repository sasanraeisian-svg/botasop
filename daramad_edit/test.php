<?php
echo "PHP is working.\n";
echo "curl loaded: " . (extension_loaded('curl') ? 'YES' : 'NO') . "\n";
echo "pdo loaded: " . (extension_loaded('pdo') ? 'YES' : 'NO') . "\n";
echo "pdo_mysql loaded: " . (extension_loaded('pdo_mysql') ? 'YES' : 'NO') . "\n";

try {
    include "config/configs.php";
    echo "configs.php loaded OK, DB connected.\n";
} catch (Throwable $e) {
    echo "ERROR in configs.php: " . $e->getMessage() . "\n";
}
