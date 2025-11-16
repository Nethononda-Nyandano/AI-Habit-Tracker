<?php
// --- Ensure JSON output ---
header('Content-Type: application/json');

// --- PHP error handling ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Catch uncaught exceptions ---
set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
});



$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    error_log($e->getMessage());
    exit;
}

// --- Get JSON input ---
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

// --- Validate fields ---
$errors = [];
$required = ['name','surname','email','phone','street','city','postal','province','password'];

foreach ($required as $field) {
    if (empty($input[$field])) {
        $errors[$field] = ucfirst($field) . " is required";
    }
}

if (!empty($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Invalid email format";
}

if (!empty($input['password']) && strlen($input['password']) < 6) {
    $errors['password'] = "Password must be at least 6 characters";
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['errors' => $errors]);
    exit;
}

// --- Check if email already exists ---
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->execute([$input['email']]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['errors' => ['email' => 'Email already registered']]);
    exit;
}

// --- Hash password ---
$hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);

// --- Insert user + address ---
try {
    $pdo->beginTransaction();

    // Insert into users
    $stmtUser = $pdo->prepare("INSERT INTO users (name, surname, email, phone, password_hash) VALUES (?, ?, ?, ?, ?)");
    $stmtUser->execute([
        $input['name'],
        $input['surname'],
        $input['email'],
        $input['phone'],
        $hashedPassword
    ]);
    $userId = $pdo->lastInsertId();

    // Insert into addresses
    $stmtAddr = $pdo->prepare("INSERT INTO addresses (user_id, street, city, zip_code, country) VALUES (?, ?, ?, ?, ?)");
    $stmtAddr->execute([
        $userId,
        $input['street'],
        $input['city'],
        $input['postal'],
        $input['province']
    ]);

    $pdo->commit();

    echo json_encode(['message' => 'Registration successful']);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
}
?>
