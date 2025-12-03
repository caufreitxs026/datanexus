<?php
// Configurações do Ambiente
define('PYTHON_PATH', 'python'); // Ou caminho completo ex: 'C:/Python313/python.exe'
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('OUTPUT_DIR', __DIR__ . '/output/');
define('LOG_FILE', __DIR__ . '/system.log');

// Limites de Upload
ini_set('upload_max_filesize', '20M');
ini_set('post_max_size', '25M');
ini_set('display_errors', 0);
error_reporting(E_ALL);
?>