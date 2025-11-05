<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;


function generateToken(int $length = 32): string {
    return bin2hex(random_bytes($length));
}


function setAuthCookie(string $token): void {

    $URL_BASENAME = $_ENV['URL_BASENAME'];

    setcookie('token', $token, [
        'expires'  => time() + 60 * 60 * 24 * 7, // 7 days
        'path'     => "$URL_BASENAME/",
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}


function getTokenFromCookie(Request $request): ?string {
    return $request->getCookieParams()['token'] ?? null;
}


function generate_uuid_v4() {
    $data = random_bytes(16);
    // Set version to 0100
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}


function getDateTime(string $datetime) {
    return date('Y-m-d H:i:s', strtotime($datetime));
}
