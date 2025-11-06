<?php
declare(strict_types=1);


/**
 * @throws Exception
 */
function validateUsername(string $username): string {

    // ENSURE: username is required min & max length
    if (strlen($username) < 4 || strlen($username)> 30) {
        throw new Exception("username should be greater than 4 and less than 30 chars.");
    }

    // ENSURE: username does not contain unwanted chars/symbols
    if (!preg_match('/^(?![._])(?!.*[._]{2})[A-Za-z0-9._]+(?<![._])$/', $username)) {
        throw new Exception("username can only start & end with letters, then contain dots, and underscores between letters");
    }

    // RETURN: validated username string in lowercases
    return strtolower($username);

}
