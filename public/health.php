<?php
// Set proper headers for health check
http_response_code(200);
header('Content-Type: application/json');
header('Cache-Control: no-cache');

// Return a simple JSON response
echo json_encode([
    'status' => 'healthy',
    'php_version' => phpversion(),
    'timestamp' => date('Y-m-d H:i:s'),
    'memory_usage' => memory_get_usage(true)
]);
?>
