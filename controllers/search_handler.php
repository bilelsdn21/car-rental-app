<?php
require_once 'db.php';

header('Content-Type: application/json');

function sanitize($str) {
    return htmlspecialchars(strip_tags(trim($str)));
}

try {
    $type = isset($_GET['type']) ? sanitize($_GET['type']) : '';
    $query = isset($_GET['query']) ? sanitize($_GET['query']) : '';
    
    switch($type) {
        case 'locations':
            $stmt = $conn->prepare("SELECT DISTINCT location FROM cars WHERE location LIKE ? AND status = 'available' LIMIT 10");
            $searchTerm = "%$query%";
            $stmt->bind_param('s', $searchTerm);
            break;
            
        case 'makes':
            $stmt = $conn->prepare("SELECT DISTINCT make FROM cars WHERE make LIKE ? AND status = 'available' LIMIT 10");
            $searchTerm = "%$query%";
            $stmt->bind_param('s', $searchTerm);
            break;
            
        case 'categories':
            $stmt = $conn->prepare("SELECT DISTINCT category FROM cars WHERE category LIKE ? AND status = 'available' LIMIT 10");
            $searchTerm = "%$query%";
            $stmt->bind_param('s', $searchTerm);
            break;
            
        default:
            throw new Exception('Invalid search type');
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while($row = $result->fetch_assoc()) {
        $items[] = array_values($row)[0]; // Get first column value
    }
    
    echo json_encode([
        'success' => true,
        'results' => $items
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
