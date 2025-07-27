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
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $dataFile = '../data/siteData.json';
    
    // Check if data file exists
    if (!file_exists($dataFile)) {
        // Return default data if no saved data exists
        $defaultData = [
            'contact' => [
                'email' => 'contact@heavenofweb.fr',
                'phone' => '+33 1 23 45 67 89',
                'address' => 'Paris, France'
            ],
            'portfolio' => [
                [
                    'id' => '1',
                    'title' => 'Project 1',
                    'alt' => 'Portfolio Project 1',
                    'src' => './img/Portfolio-1.webp',
                    'link' => 'https://example.com',
                    'linkText' => 'View Project'
                ]
            ]
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $defaultData,
            'message' => 'Default data loaded'
        ]);
        exit();
    }
    
    // Read data from file
    $jsonData = file_get_contents($dataFile);
    if ($jsonData === false) {
        throw new Exception('Failed to read data file');
    }
    
    $data = json_decode($jsonData, true);
    if ($data === null) {
        throw new Exception('Invalid JSON in data file');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'message' => 'Data loaded successfully',
        'lastModified' => date('Y-m-d H:i:s', filemtime($dataFile))
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
