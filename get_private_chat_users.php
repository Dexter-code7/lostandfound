<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for production

// --- SERVER-SIDE ADMIN CHECK ---
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Admin privileges required.']);
    exit();
}
// --- END ADMIN CHECK ---

$usersFile = 'users.json';
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

$chatableUsers = [];
foreach ($users as $user) {
    // Exclude the admin's own ID from the list if they are viewing it
    if ($user['id'] !== $_SESSION['user_id']) {
        $chatableUsers[] = [
            'id' => $user['id'],
            'username' => $user['username']
        ];
    }
}

echo json_encode($chatableUsers);
?>