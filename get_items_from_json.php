<?php
// Start a PHP session
session_start();

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // IMPORTANT: Adjust for production

// Optional: Check if user is logged in to view items, or allow public viewing
// if (!isset($_SESSION['user_id'])) {
//     echo json_encode(['success' => false, 'message' => 'You must be logged in to view items.']);
//     exit();
// }

$itemsFile = 'items.json'; // Path to your items JSON file

$items = [];
if (file_exists($itemsFile)) {
    $fileContent = file_get_contents($itemsFile);
    if ($fileContent === false) {
        error_log("Failed to read items file: " . $itemsFile);
        echo json_encode([]);
        exit();
    }
    $decodedContent = json_decode($fileContent, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedContent)) {
        $items = $decodedContent;
    } else {
        error_log("Invalid JSON in items file: " . $itemsFile);
        echo json_encode([]);
        exit();
    }
}

$currentTimestamp = time(); // Get current Unix timestamp
$thirtyDaysInSeconds = 30 * 24 * 60 * 60; // 30 days in seconds

$nonExpiredItems = [];
$itemsChanged = false; // Flag to track if any items were removed or modified

foreach ($items as $item) {
    // Ensure 'timestamp', 'reported', and 'status' fields exist, defaulting if not
    if (!isset($item['timestamp'])) {
        $item['timestamp'] = date('c'); // Add current timestamp if missing
        $itemsChanged = true;
    }
    if (!isset($item['reported'])) {
        $item['reported'] = false;
        $itemsChanged = true;
    }
    if (!isset($item['status'])) {
        $item['status'] = 'active'; // Default to 'active' if missing
        $itemsChanged = true;
    }

    $itemTimestamp = strtotime($item['timestamp']);

    // Only apply 30-day expiration to 'active' items
    if ($item['status'] === 'active' && ($currentTimestamp - $itemTimestamp) > $thirtyDaysInSeconds) {
        // This active item is expired, do not add it to nonExpiredItems
        $itemsChanged = true;
    } else {
        // This item is either not active, or not expired, so keep it
        $nonExpiredItems[] = $item;
    }
}

// If any items were removed or modified, save the updated list back to the JSON file
if ($itemsChanged) {
    if (file_put_contents($itemsFile, json_encode($nonExpiredItems, JSON_PRETTY_PRINT), LOCK_EX) === false) {
        error_log("Failed to write updated items to file after cleanup/migration: " . $itemsFile);
    }
}

$itemTypeFilter = $_GET['type'] ?? ''; // Get 'type' from URL parameter (e.g., ?type=lost)
$filteredItems = [];

// Filter items based on the 'type' parameter from the non-expired list
foreach ($nonExpiredItems as $item) {
    if (isset($item['itemType']) && $item['itemType'] === $itemTypeFilter) {
        $filteredItems[] = $item;
    }
}

// Optionally, sort items by timestamp (most recent first)
usort($filteredItems, function($a, $b) {
    // Ensure 'timestamp' key exists and is valid for comparison
    $timeA = isset($a['timestamp']) ? strtotime($a['timestamp']) : 0;
    $timeB = isset($b['timestamp']) ? strtotime($b['timestamp']) : 0;
    return $timeB - $timeA; // Sort descending (most recent first)
});

echo json_encode($filteredItems);
?>