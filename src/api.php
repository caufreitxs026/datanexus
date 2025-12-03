<?php
require_once 'config.php';
header('Content-Type: application/json');

// Aumenta tempo de execução
set_time_limit(300);

// Cria pastas automaticamente se não existirem
if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);
if (!is_dir(OUTPUT_DIR)) mkdir(OUTPUT_DIR, 0777, true);

// Função de Log
function writeLog($msg) {
    $date = date('Y-m-d H:i:s');
    file_put_contents(LOG_FILE, "[$date] $msg" . PHP_EOL, FILE_APPEND);
}

// Garbage Collection (Limpeza de arquivos antigos > 24h)
function cleanOldFiles($dir) {
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
            $path = $dir . $file;
            if (is_file($path) && (time() - filemtime($path)) > 86400) {
                unlink($path);
            }
        }
        closedir($handle);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    
    cleanOldFiles(OUTPUT_DIR);

    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if ($ext !== 'xlsx') {
        echo json_encode(['error' => 'Invalid format. Only .xlsx allowed.']);
        exit;
    }

    // Sanitização do nome do arquivo
    $safeName = md5(uniqid(rand(), true)) . '.xlsx';
    $filePath = UPLOAD_DIR . $safeName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        
        $scriptPath = __DIR__ . '/core.py';

        // Escape de argumentos para segurança
        $cmdPython = escapeshellarg(PYTHON_PATH);
        $cmdScript = escapeshellarg($scriptPath);
        $cmdFile   = escapeshellarg($filePath);
        $cmdOut    = escapeshellarg(OUTPUT_DIR);

        // Executa Python
        $command = "$cmdPython $cmdScript $cmdFile $cmdOut 2>&1";
        
        $startTime = microtime(true);
        $output = shell_exec($command);
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        // Remove upload original
        if (file_exists($filePath)) unlink($filePath);
        
        $result = json_decode($output, true);

        if ($result && !isset($result['error'])) {
            writeLog("SUCCESS: Processed in {$duration}s.");
            echo json_encode(['success' => true, 'data' => $result, 'download_path' => 'output/']);
        } else {
            $errMsg = isset($result['error']) ? $result['error'] : $output;
            writeLog("ERROR: Processing failed. Msg: $errMsg");
            echo json_encode(['error' => 'Processing failed. Check logs.', 'details' => $errMsg]);
        }

    } else {
        writeLog("ERROR: Upload failed.");
        echo json_encode(['error' => 'Upload failed. Check directory permissions.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}
?>