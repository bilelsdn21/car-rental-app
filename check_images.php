<?php
require_once 'db.php';

$result = $conn->query('SELECT id, make, model, image_url FROM cars');
while ($row = $result->fetch_assoc()) {
    echo 'Car ' . $row['id'] . ': ' . $row['make'] . ' ' . $row['model'] . ' - Image: ' . $row['image_url'] . '<br>';
}
?>
