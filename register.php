<?php
// Start a PHP session (needed for future login)
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

if (strlen($username) < 3) {
    echo json_encode(['success' => false, 'message' => 'Username must be at least 3 characters.']);
    exit();
}
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
    exit();
}

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

// Check if username already exists
foreach ($users as $user) {
    if ($user['username'] === $username) {
        echo json_encode(['success' => false, 'message' => 'Username already taken.']);
        exit();
    }
}

// Hash the password for security
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Generate a simple unique ID for the user
$userId = uniqid('user_');

// Add new user
$users[] = [
    'id' => $userId,
    'username' => $username,
    'password' => $hashedPassword // Store hashed password
];

// Write updated users back to file
if (file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX) !== false) {
    echo json_encode(['success' => true, 'message' => 'Registration successful.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to register user. Check file permissions.']);
}
?>