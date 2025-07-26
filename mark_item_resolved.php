<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for production
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to perform this action.']);
    exit();
}

$itemsFile = 'items.json';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$itemIdToMark = $data['itemId'] ?? null;

if (!$itemIdToMark) {
    echo json_encode(['success' => false, 'message' => 'Item ID not provided.']);
    exit();
}

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

$itemFoundAndAuthorized = false;
foreach ($items as &$item) { // Use & for reference to modify the original array element
    if (isset($item['id']) && $item['id'] === $itemIdToMark) {
        // --- AUTHORIZATION CHECK ---
        // Only the original reporter can mark it as resolved
        if (isset($item['reporterId']) && $item['reporterId'] === $_SESSION['user_id']) {
            $item['status'] = 'resolved'; // Mark item as resolved
            $itemFoundAndAuthorized = true;
            break;
        } else {
            echo json_encode(['success' => false, 'message' => 'You are not authorized to mark this item as resolved.']);
            exit();
        }
    }
}
unset($item); // Break the reference

if ($itemFoundAndAuthorized) {
    if (file_put_contents($itemsFile, json_encode($items, JSON_PRETTY_PRINT), LOCK_EX) !== false) {
        echo json_encode(['success' => true, 'message' => 'Item marked as Found/Returned.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update item status.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Item not found.']);
}
?>