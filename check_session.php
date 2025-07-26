<?php
// Start the PHP session
session_start();

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // IMPORTANT: Adjust for production

$loggedIn = isset($_SESSION['user_id']) && isset($_SESSION['username']);
$userId = $loggedIn ? $_SESSION['user_id'] : null;
$username = $loggedIn ? $_SESSION['username'] : null;

// Initialize admin specific variables
$adminId = null;
$adminUsername = null;
$usersFile = 'users.json'; // Path to your users JSON file

// Fetch admin user's ID and username from users.json
if (file_exists($usersFile)) {
    $fileContent = file_get_contents($usersFile);
    if ($fileContent !== false) {
        $decodedContent = json_decode($fileContent, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedContent)) {
            foreach ($decodedContent as $user) {
                if (isset($user['username']) && $user['username'] === 'admin') {
                    $adminId = $user['id'] ?? null;
                    $adminUsername = $user['username'];
                    break;
                }
            }
        }
    }
}

echo json_encode([
    'loggedIn' => $loggedIn,
    'userId' => $userId,
    'username' => $username,
    'adminId' => $adminId, // Now includes the admin's actual user ID
    'adminUsername' => $adminUsername // Now includes the admin's username
]);
?>