<?php
header('Content-Type: application/json');

function checkRequirements() {
    $requirements = [
        'PHP Version' => [
            'required' => '7.0.0',
            'current' => PHP_VERSION,
            'status' => version_compare(PHP_VERSION, '7.0.0', '>=')
        ],
        'MySQL Extension' => [
            'required' => true,
            'current' => extension_loaded('mysqli'),
            'status' => extension_loaded('mysqli')
        ],
        'PDO Extension' => [
            'required' => true,
            'current' => extension_loaded('pdo_mysql'),
            'status' => extension_loaded('pdo_mysql')
        ],
        'allow_url_fopen' => [
            'required' => true,
            'current' => ini_get('allow_url_fopen'),
            'status' => ini_get('allow_url_fopen')
        ]
    ];

    return $requirements;
}

function createLogFile() {
    $logFile = 'weather_app.log';
    if (!file_exists($logFile)) {
        file_put_contents($logFile, '');
        chmod($logFile, 0666);
    }
    return file_exists($logFile) && is_writable($logFile);
}

try {
    // Check requirements
    $requirements = checkRequirements();
    $failed = array_filter($requirements, function($req) {
        return !$req['status'];
    });

    if (!empty($failed)) {
        throw new Exception("System requirements not met:\n" . json_encode($failed, JSON_PRETTY_PRINT));
    }

    // Create log file
    if (!createLogFile()) {
        throw new Exception("Failed to create or make writable the log file");
    }

    // Initialize database
    require_once('connect.php');

    echo json_encode([
        'success' => true,
        'message' => 'Setup completed successfully',
        'requirements' => $requirements
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} 