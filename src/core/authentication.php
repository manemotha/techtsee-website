<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;


function getDB(): PDO {
    static $db = null;

    // ENV: Get database variables
    $DB_HOST = $_ENV['DB_HOST'];
    $DB_DATABASE = $_ENV['DB_DATABASE'];
    $DB_USERNAME = $_ENV['DB_USERNAME'];
    $DB_PASSWORD = $_ENV['DB_PASSWORD'];

    if ($db === null) {
        $db = new PDO("mysql:host=$DB_HOST; dbname=$DB_DATABASE; charset=utf8mb4", $DB_USERNAME, $DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
    return $db;
}


function signup(array $data): array {

    // MYSQL: Open database connection
    $db = getDB();

    // ENSURE: All required fields/columns exist
    $required = ['username','displayName','email','password'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return ['status' => false, 'message' => "missing field: $field"];
        }
    }

    // MYSQL: Query for user with same username
    $stmt = $db->prepare("SELECT username FROM User WHERE username = ?");
    $stmt->execute([$data['username']]);

    // ENSURE: No user with same username exists
    if ($stmt->fetch()) {
        return ['status' => false, 'message' => 'user with same username exists'];
    }

    // GENERATE: User ID
    $generatedUserID = generate_uuid_v4();

    // GENERATE: Hashed password
    $hash = password_hash($data['password'], PASSWORD_DEFAULT);

    // MYSQL: Insert user data into database
    $stmt = $db->prepare("INSERT INTO User (id, username, displayName, email, password) VALUES (?,?,?,?,?)");
    $stmt->execute([
        $generatedUserID,
        $data['username'],
        $data['displayName'],
        $data['email'],
        $hash
    ]);

    return ['status' => true, 'message' => 'Account created successfully'];
}


function login(Request $request, string $username, string $password): array {

    // MYSQL: Open database connection
    $db = getDB();

    $stmt = $db->prepare("SELECT * FROM User WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // VALIDATE: user password with database hashed password
    if (!$user || !password_verify($password, $user['password'])) {
        return ['status' => false, 'message' => 'incorrect password'];
    }

    // SESSION: Get token from user cookies
    $oldToken = $request->getCookieParams()['token'] ?? null;

    // MYSQL: Inactivate/revoke old authentication token
    if ($oldToken) {
        $db->prepare("UPDATE Tokens SET isActive = false WHERE userId = ? AND token = ?")->execute([$user['id'] ?? null, $oldToken]);
    }

    // Token expiration DateTime
    $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));

    // Create new token
    $token = generateToken(32);

    // MYSQL: Insert new token data into database
    $stmt = $db->prepare("INSERT INTO Tokens (userId, token, created, isActive, expires) VALUES (?, ?, NOW(), true, ?)");
    $stmt->execute([$user['id'], $token, $expiry]);

    // SESSION: Set authentication token to user's browser
    setAuthCookie($token);

    return ['status' => true, 'message' => $user];
}


function authenticate($token): ?array {
    $db = getDB();
    if (!$token) return null;

    $stmt = $db->prepare("
        SELECT U.* 
        FROM User AS U 
        JOIN Tokens AS T ON T.userId = U.id 
        WHERE T.token = ? AND T.isActive = true AND T.expires > NOW()
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) return $user;

    return null;
}
