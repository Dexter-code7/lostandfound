<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for production
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to send private messages.']);
    exit();
}

$privateChatsFile = 'private_chats.json';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$chatPartnerId = $data['partnerId'] ?? null;
$messageText = $data['message'] ?? null;
$timestamp = $data['timestamp'] ?? date('c'); // Use provided timestamp or current

if (!$chatPartnerId || !is_string($messageText) || trim($messageText) === '') {
    echo json_encode(['success' => false, 'message' => 'Invalid message or partner ID.']);
    exit();
}

$currentUserId = $_SESSION['user_id'];
$currentUserName = $_SESSION['username'];

// Construct the chat ID by sorting the two user IDs to ensure consistency
$participantIds = [$currentUserId, $chatPartnerId];
sort($participantIds);
$chatId = implode('-', $participantIds);

$allChats = [];
if (file_exists($privateChatsFile)) {
    $fileContent = file_get_contents($privateChatsFile);
    if ($fileContent !== false) {
        $decodedContent = json_decode($fileContent, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedContent)) {
            $allChats = $decodedContent;
        }
    }
}

$chatFound = false;
$newMessage = [
    'senderId' => $currentUserId,
    'senderName' => $currentUserName,
    'message' => $messageText,
    'timestamp' => $timestamp
];

foreach ($allChats as &$chat) { // Use & for reference
    if (isset($chat['chat_id']) && $chat['chat_id'] === $chatId) {
        $chat['messages'][] = $newMessage;
        $chatFound = true;
        break;
    }
}
unset($chat); // Break the reference

if (!$chatFound) {
    // Create a new chat if it doesn't exist
    $allChats[] = [
        'chat_id' => $chatId,
        'participants' => [$currentUserId, $chatPartnerId],
        'messages' => [$newMessage]
    ];
}

// Write updated chats back to file
if (file_put_contents($privateChatsFile, json_encode($allChats, JSON_PRETTY_PRINT), LOCK_EX) !== false) {
    echo json_encode(['success' => true, 'message' => 'Private message sent.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save private message. Check file permissions.']);
}
?>