<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for production
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

// --- SERVER-SIDE ADMIN CHECK ---
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Admin privileges required.']);
    exit();
}
// --- END ADMIN CHECK ---

$itemsFile = 'items.json';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$itemIdToToggle = $data['itemId'] ?? null;
$newReportedStatus = $data['reported'] ?? null; // Expects true/false boolean

if (!$itemIdToToggle || !is_bool($newReportedStatus)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input for toggling report status.']);
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

$itemFound = false;
foreach ($items as &$item) { // Use & for reference to modify the original array element
    if (isset($item['id']) && $item['id'] === $itemIdToToggle) {
        $item['reported'] = $newReportedStatus; // Set the new status
        $itemFound = true;
        break;
    }
}
unset($item); // Break the reference

if ($itemFound) {
    if (file_put_contents($itemsFile, json_encode($items, JSON_PRETTY_PRINT), LOCK_EX) !== false) {
        echo json_encode(['success' => true, 'message' => 'Item report status updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update item report status.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Item not found.']);
}
?>