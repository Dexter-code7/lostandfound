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

$usersFile = 'users.json';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$userIdToDelete = $data['userId'] ?? null;

if (!$userIdToDelete) {
    echo json_encode(['success' => false, 'message' => 'User ID not provided.']);
    exit();
}

// Prevent admin from deleting themselves
if ($userIdToDelete === $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot delete your own admin account.']);
    exit();
}


$users = [];
if (file_exists($usersFile)) {
    $fileContent = file_get_contents($usersFile);
    if ($fileContent !== false) {
        $decodedContent = json_decode($fileContent, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedContent)) {
            $users = $decodedContent;
        }
    }
}

$updatedUsers = [];
$userDeleted = false;

foreach ($users as $user) {
    if (isset($user['id']) && $user['id'] === $userIdToDelete) {
        $userDeleted = true;
    } else {
        $updatedUsers[] = $user;
    }
}

if ($userDeleted) {
    if (file_put_contents($usersFile, json_encode($updatedUsers, JSON_PRETTY_PRINT), LOCK_EX) !== false) {
        echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save updated users file.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
}
?>