<?php
require_once 'db.php';

// Read SQL file
$sql = file_get_contents('update_settings.sql');

// Split SQL into individual statements
$statements = explode(';', $sql);

// Execute each statement
foreach ($statements as $statement) {
    if (trim($statement) !== '') {
        $conn->query(trim($statement) . ';');
    }
}

// Redirect back to settings page
header('Location: settings.php');
exit();
