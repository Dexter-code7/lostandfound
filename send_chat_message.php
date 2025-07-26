<?php
// Start a PHP session
session_start();

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // IMPORTANT: Adjust for production
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to chat.']);
    exit();
}

$chatFile = 'chat.json'; // Path to your chat JSON file

// Get the raw POST data (JSON payload from JavaScript fetch)
$input = file_get_contents('php://input');
$newMessage = json_decode($input, true); // Decode JSON into a PHP associative array

// Validate the incoming message data
if (json_last_error() !== JSON_ERROR_NONE || !isset($newMessage['message']) || !isset($newMessage['timestamp'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid message format received.']);
    exit();
}

// Use session data for senderId and senderName
$newMessage['senderId'] = $_SESSION['user_id'];
$newMessage['senderName'] = $_SESSION['username'];

// Read existing messages from the JSON file
$currentMessages = [];
if (file_exists($chatFile)) {
    $fileContent = file_get_contents($chatFile);
    if ($fileContent === false) {
        echo json_encode(['success' => false, 'message' => 'Failed to read chat file. Check permissions.']);
        exit();
    }
    $decodedContent = json_decode($fileContent, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedContent)) {
        $currentMessages = $decodedContent;
    }
    // If file exists but contains invalid JSON, we'll treat it as empty and start fresh
    // An error message could be logged here for debugging
}

// Append the new message to the array
$currentMessages[] = $newMessage;

// Optional: Limit the number of messages to prevent the file from growing indefinitely
// For example, keep only the last 100 messages
$maxMessages = 100;
if (count($currentMessages) > $maxMessages) {
    $currentMessages = array_slice($currentMessages, -$maxMessages);
}

// Encode the updated messages array back to JSON format
$jsonToSave = json_encode($currentMessages, JSON_PRETTY_PRINT); // JSON_PRETTY_PRINT for readability

// Write the JSON string back to the file
// LOCK_EX is important here: it acquires an exclusive lock on the file while writing,
// preventing other processes from writing to it simultaneously and causing corruption.
if (file_put_contents($chatFile, $jsonToSave, LOCK_EX) !== false) {
    echo json_encode(['success' => true, 'message' => 'Message sent successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save message to file. Check file permissions.']);
}
?>