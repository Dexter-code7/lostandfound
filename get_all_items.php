<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for production

// --- SERVER-SIDE ADMIN CHECK ---
// IMPORTANT: This check is crucial for security.
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Admin privileges required.']);
    exit();
}
// --- END ADMIN CHECK ---

$itemsFile = 'items.json';
$items = [];

if (file_exists($itemsFile)) {
    $fileContent = file_get_contents($itemsFile);
    if ($fileContent !== false) {
        $decodedContent = json_decode($fileContent, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedContent)) {
            $items = $decodedContent;
        }
    }
}

// Ensure 'reported' and 'status' fields exist for all items before sending to frontend
// Also, ensure imagePath is included
$processedItems = [];
foreach ($items as $item) {
    if (!isset($item['reported'])) {
        $item['reported'] = false; // Default to false if not present
    }
    if (!isset($item['status'])) {
        $item['status'] = 'active'; // Default to 'active' if not present
    }
    if (!isset($item['imagePath'])) {
        $item['imagePath'] = null; // Default to null if not present
    }
    $processedItems[] = $item;
}


// Sort items by timestamp (most recent first)
usort($processedItems, function($a, $b) {
    $timeA = isset($a['timestamp']) ? strtotime($a['timestamp']) : 0;
    $timeB = isset($b['timestamp']) ? strtotime($b['timestamp']) : 0;
    return $timeB - $timeA;
});

echo json_encode($processedItems);
?>