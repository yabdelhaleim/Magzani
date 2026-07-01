<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    
    echo "--- MySQL DATABASES starting with 'tenant' ---\n";
    $stmt = $pdo->query('SHOW DATABASES');
    while ($db = $stmt->fetchColumn()) {
        if (strpos($db, 'tenant') === 0 || $db === 'magzany' || $db === 'magzani_testing') {
            echo "- {$db}\n";
        }
    }

    echo "\n--- MySQL PROCESSLIST ---\n";
    $stmt = $pdo->query('SHOW PROCESSLIST');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['Id']} | User: {$row['User']} | Host: {$row['Host']} | DB: {$row['db']} | Command: {$row['Command']} | Time: {$row['Time']} | State: {$row['State']} | Info: {$row['Info']}\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
