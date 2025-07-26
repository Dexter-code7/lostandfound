<?php
// Start a PHP session
session_start();

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // IMPORTANT: Adjust for production

// Optional: Check if user is logged in to view chat, or allow public chat
// if (!isset($_SESSION['user_id'])) {
//     echo json_encode(['success' => false, 'message' => 'You must be logged in to view chat.']);
//     exit();
// }

$chatFile = 'chat.json'; // Path to your chat JSON file

// Check if the chat file exists
if (file_exists($chatFile)) {
    $fileContent = file_get_contents($chatFile);
    if ($fileContent === false) {
        error_log("Failed to read chat file: " . $chatFile);
        echo json_encode([]);
        exit();
    }
    $messages = json_decode($fileContent, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($messages)) {
        // Successfully decoded JSON, return messages
        echo json_encode($messages);
    } else {
        // File exists but contains invalid JSON, return empty array
        error_log("Invalid JSON in chat file: " . $chatFile);
        echo json_encode([]);
    }
} else {
    // File does not exist yet, return empty array
    echo json_encode([]);
}
?>