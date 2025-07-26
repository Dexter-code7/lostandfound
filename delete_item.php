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
$uploadDir = 'uploads/'; // Directory where images are stored

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$itemIdToDelete = $data['itemId'] ?? null;

if (!$itemIdToDelete) {
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

$updatedItems = [];
$itemDeleted = false;
$imagePathToDelete = null;

foreach ($items as $item) {
    if (isset($item['id']) && $item['id'] === $itemIdToDelete) {
        $itemDeleted = true;
        if (isset($item['imagePath']) && file_exists($item['imagePath'])) {
            $imagePathToDelete = $item['imagePath']; // Store path for deletion
        }
    } else {
        $updatedItems[] = $item;
    }
}

if ($itemDeleted) {
    if (file_put_contents($itemsFile, json_encode($updatedItems, JSON_PRETTY_PRINT), LOCK_EX) !== false) {
        // Attempt to delete the associated image file
        if ($imagePathToDelete && unlink($imagePathToDelete)) {
            // Image deleted successfully
        } elseif ($imagePathToDelete) {
            error_log("Failed to delete image file: " . $imagePathToDelete);
        }
        echo json_encode(['success' => true, 'message' => 'Item deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save updated items file.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Item not found.']);
}
?>