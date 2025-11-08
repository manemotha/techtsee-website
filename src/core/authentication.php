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


/**
 * @param array $data User signup data.
 * @throws Exception
 */
function signup(array $data) :void {

    // MYSQL: Open database connection
    $db = getDB();

    // ENSURE: User request data has required keys
    try {
        validateRequiredKeys($data, [
            'username',
            'display_name',
            'email',
            'password'
        ]);
    } catch (Exception $error_message) {
        throw new Exception($error_message->getMessage());
    }

    // VALIDATE: Username & password
    try {
        validateUsername($data['username']);
        validatePassword($data['password']);
    } catch (Exception $error_message) {
        throw new Exception($error_message->getMessage());
    }

    // MYSQL: Query for user with same username
    $stmt = $db->prepare("SELECT username FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);

    // ENSURE: No user with same username exists
    if ($stmt->fetch()) {
        throw new Exception('user with same username exists');
    }

    // GENERATE: User ID
    $generatedUserID = generate_uuid_v4();

    // GENERATE: Hashed password
    $hash = password_hash($data['password'], PASSWORD_DEFAULT);

    // MYSQL: Insert user data into database
    $stmt = $db->prepare("INSERT INTO users (id, username, display_name, email, password) VALUES (?,?,?,?,?)");
    $stmt->execute([
        $generatedUserID,
        $data['username'],
        $data['display_name'],
        $data['email'],
        $hash
    ]);
}


/**
 * @param array $data User login data.
 * @return array Database user account data after authorization.
 * @throws Exception
 */
function login(array $data): array {

    // MYSQL: Open database connection
    $db = getDB();

    // ENSURE: User request data has required keys
    try {
        validateRequiredKeys($data, [
            'username',
            'password'
        ]);
    } catch (Exception $error_message) {
        throw new Exception($error_message->getMessage());
    }

    // VALIDATE: Username & password
    try {
        $username = validateUsername($data['username']);
        $password = validatePassword($data['password']);
    } catch (Exception $error_message) {
        throw new Exception($error_message->getMessage());
    }

    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // ENSURE: Account with matching username exists
    if (!$user) {
        throw new Exception('incorrect username');
    }

    // COMPARISON: User login-password with database hashed-password
    if (!password_verify($password, $user['password'])) {
        throw new Exception('incorrect password');
    }

    // Token expiration DateTime
    $expiry = getDateTime('+30 days');

    // Create new token
    $token = generateToken(32);

    // MYSQL: Insert new token data into database
    $stmt = $db->prepare("INSERT INTO tokens (user_id, token, is_active, expires_at) VALUES (?, ?, true, ?)");
    $stmt->execute([$user['id'], $token, $expiry]);

    // SESSION: Set authentication token to user browser cookies
    setAuthCookie($token);

    // Remove secret keys from user data for security purposes
    unset($user['password']);

    // User login succeeded
    return $user;
}


function authenticate($token): ?array {
    $db = getDB();
    if (!$token) return null;

    $stmt = $db->prepare("
        SELECT U.* 
        FROM users AS U 
        JOIN tokens AS T ON T.user_id = U.id 
        WHERE T.token = ? AND T.is_active = true AND T.expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) return $user;

    return null;
}
