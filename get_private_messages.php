<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for production

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to view private messages.']);
    exit();
}

$privateChatsFile = 'private_chats.json';
$chatPartnerId = $_GET['partnerId'] ?? null;

if (!$chatPartnerId) {
    echo json_encode(['success' => false, 'message' => 'Chat partner ID not provided.']);
    exit();
}

$currentUserId = $_SESSION['user_id'];

// Construct the chat ID by sorting the two user IDs to ensure consistency
$participantIds = [$currentUserId, $chatPartnerId];
sort($participantIds); // Sort alphabetically
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

$messages = [];
foreach ($allChats as $chat) {
    if (isset($chat['chat_id']) && $chat['chat_id'] === $chatId) {
        $messages = $chat['messages'] ?? [];
        break;
    }
}

echo json_encode($messages);
?>