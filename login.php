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

$usersFile = 'users.json'; // Path to your users JSON file

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE || !isset($data['username']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit();
}

$username = trim($data['username']);
$password = trim($data['password']);

// Read existing users
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

$loggedIn = false;
$userId = null;
$userName = null;

foreach ($users as $user) {
    if ($user['username'] === $username) {
        // Verify the password
        if (password_verify($password, $user['password'])) {
            $loggedIn = true;
            $userId = $user['id'];
            $userName = $user['username'];
            break;
        }
    }
}

if ($loggedIn) {
    // Set session variables
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $userName;
    echo json_encode(['success' => true, 'message' => 'Login successful.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
}
?>