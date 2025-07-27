<?php
header('Content-Type: application/json');
// Allow both production and development origins
$allowedOrigins = ['https://heavenofweb.fr', 'http://localhost:3000', 'http://localhost:3001'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: https://heavenofweb.fr');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON data from request body
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate admin password (simple security check)
    if (!isset($data['adminPassword']) || $data['adminPassword'] !== 'heaven2025') {
        throw new Exception('Unauthorized access');
    }
    
    // Remove password from data before saving
    unset($data['adminPassword']);
    
    // Create data directory if it doesn't exist
    $dataDir = '../data/';
    if (!file_exists($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    // Save data to JSON file
    $dataFile = $dataDir . 'siteData.json';
    $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    if (file_put_contents($dataFile, $jsonData) === false) {
        throw new Exception('Failed to save data to file');
    }
    
    // Create backup with timestamp
    $backupFile = $dataDir . 'backup_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($backupFile, $jsonData);
    
    // Clean up old backups (keep only last 10)
    $backupFiles = glob($dataDir . 'backup_*.json');
    if (count($backupFiles) > 10) {
        usort($backupFiles, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        $filesToDelete = array_slice($backupFiles, 0, count($backupFiles) - 10);
        foreach ($filesToDelete as $file) {
            unlink($file);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Data saved successfully',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
