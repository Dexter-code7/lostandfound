<?php
// Start a PHP session
session_start();

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // IMPORTANT: Adjust for production
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With'); // Added X-Requested-With for FormData

// Handle preflight OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if user is logged in to submit items
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to report an item.']);
    exit();
}

$itemsFile = 'items.json'; // Path to your items JSON file
$uploadDir = 'uploads/'; // Directory to save uploaded images

// Create uploads directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Create recursively with full permissions (adjust for production)
}

// Initialize newItem data from POST (form-data)
$newItem = [
    'itemType' => $_POST['itemType'] ?? '',
    'itemName' => $_POST['itemName'] ?? '',
    'description' => $_POST['description'] ?? '',
    'location' => $_POST['location'] ?? '',
    'date' => $_POST['date'] ?? '',
    'contactName' => $_POST['contactName'] ?? '',
    'contactEmail' => $_POST['contactEmail'] ?? '',
    'contactPhone' => $_POST['contactPhone'] ?? '',
    'imagePath' => null, // Initialize image path as null
    'reported' => false, // Initialize reported status to false
    'status' => 'active', // NEW: Initialize status to 'active'
    'reporterId' => $_SESSION['user_id'],
    'reporterName' => $_SESSION['username'],
    'id' => uniqid('item_'), // Simple unique ID
    'timestamp' => date('c') // ISO 8601 format for current date/time
];

// Basic validation for required fields
if (empty($newItem['itemType']) ||
    empty($newItem['itemName']) ||
    empty($newItem['description']) ||
    empty($newItem['location']) ||
    empty($newItem['date']) ||
    empty($newItem['contactName']) ||
    empty($newItem['contactEmail'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid or incomplete item data received.']);
    exit();
}

// Handle file upload
if (isset($_FILES['itemPhoto']) && $_FILES['itemPhoto']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['itemPhoto']['tmp_name'];
    $fileName = $_FILES['itemPhoto']['name'];
    $fileSize = $_FILES['itemPhoto']['size'];
    $fileType = $_FILES['itemPhoto']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    $newFileName = md5(time() . $fileName) . '.' . $fileExtension; // Generate a unique file name
    $destPath = $uploadDir . $newFileName;

    // Allowed file extensions
    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');

    if (in_array($fileExtension, $allowedfileExtensions)) {
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $newItem['imagePath'] = $destPath; // Store the path to the image
        } else {
            error_log("Failed to move uploaded file: " . $fileTmpPath . " to " . $destPath);
            // Continue without image, but log error
        }
    } else {
        error_log("Invalid file extension for upload: " . $fileExtension);
        // Continue without image, but log error
    }
}

// Read existing items from the JSON file
$currentItems = [];
if (file_exists($itemsFile)) {
    $fileContent = file_get_contents($itemsFile);
    if ($fileContent === false) {
        echo json_encode(['success' => false, 'message' => 'Failed to read items file. Check permissions.']);
        exit();
    }
    $decodedContent = json_decode($fileContent, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedContent)) {
        $currentItems = $decodedContent;
    }
}

// Append the new item to the array
$currentItems[] = $newItem;

// Write the updated items array back to the file
if (file_put_contents($itemsFile, json_encode($currentItems, JSON_PRETTY_PRINT), LOCK_EX) !== false) {
    echo json_encode(['success' => true, 'message' => 'Item reported successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save item data to file. Check file permissions.']);
}
?>