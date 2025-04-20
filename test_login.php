<?php
session_start();
echo "<pre>";
echo "Session Contents:\n";
print_r($_SESSION);
echo "\nCurrent User Role: " . ($_SESSION['role'] ?? 'Not logged in');
echo "</pre>";
?> 