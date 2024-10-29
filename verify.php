<?php
// verify.php
define('ALLOWED_ACCESS', true);
require_once 'config/config.php';

echo "<h1>MySQL Connection Test</h1>";

try {
    // Test basic connection
    $stmt = $pdo->query('SELECT VERSION() as version');
    $result = $stmt->fetch();
    echo "<p style='color:green'>✓ MySQL Version: " . $result['version'] . "</p>";
    
    // Test database selection
    $stmt = $pdo->query('SELECT DATABASE() as db');
    $result = $stmt->fetch();
    echo "<p style='color:green'>✓ Current Database: " . $result['db'] . "</p>";
    
    // Test table creation
    $pdo->exec("CREATE TABLE IF NOT EXISTS test (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50)
    )");
    echo "<p style='color:green'>✓ Table Creation OK</p>";
    
    // Test insertion
    $stmt = $pdo->prepare("INSERT INTO test (name) VALUES (?)");
    $stmt->execute(['Test at ' . date('Y-m-d H:i:s')]);
    echo "<p style='color:green'>✓ Data Insertion OK</p>";
    
    // Test selection
    $stmt = $pdo->query("SELECT * FROM test ORDER BY id DESC LIMIT 1");
    $result = $stmt->fetch();
    echo "<p style='color:green'>✓ Data Selection OK: " . $result['name'] . "</p>";
    
    // Connection info
    echo "<h2>Connection Information</h2>";
    echo "<pre>";
    echo "Host: " . DB_HOST . "\n";
    echo "Port: " . DB_PORT . "\n";
    echo "Database: " . DB_NAME . "\n";
    echo "Character Set: " . DB_CHARSET . "\n";
    echo "</pre>";
    
    // Clean up
    $pdo->exec("DROP TABLE IF EXISTS test");
    
} catch (PDOException $e) {
    echo "<p style='color:red'>✗ Error: " . $e->getMessage() . "</p>";
    
    // Debug information
    echo "<h2>Debug Information</h2>";
    echo "<pre>";
    echo "PHP Version: " . phpversion() . "\n";
    echo "PDO Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";
    echo "</pre>";
}
?>