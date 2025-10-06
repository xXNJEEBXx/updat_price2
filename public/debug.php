<!DOCTYPE html>
<html>
<head>
    <title>Debug Info</title>
</head>
<body>
    <h1>Debug Information</h1>
    <pre>
PHP Version: <?php echo phpversion(); ?>

Document Root: <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'not set'; ?>

Laravel Index: <?php echo file_exists(__DIR__ . '/index.php') ? 'EXISTS' : 'NOT FOUND'; ?>

Storage Writable: <?php echo is_writable(__DIR__ . '/../storage') ? 'YES' : 'NO'; ?>

Bootstrap Cache Writable: <?php echo is_writable(__DIR__ . '/../bootstrap/cache') ? 'YES' : 'NO'; ?>

ENV Variables:
<?php
echo "APP_ENV: " . (getenv('APP_ENV') ?: 'not set') . "\n";
echo "APP_KEY: " . (getenv('APP_KEY') ? 'SET (hidden)' : 'NOT SET') . "\n";
echo "DB_CONNECTION: " . (getenv('DB_CONNECTION') ?: 'not set') . "\n";
?>

Files in public:
<?php
foreach (scandir(__DIR__) as $file) {
    echo "- $file\n";
}
?>
    </pre>
</body>
</html>
