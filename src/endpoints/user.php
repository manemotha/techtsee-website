<?php
declare(strict_types=1);

use Slim\App;

// REQUIRED FILES
require __DIR__ . '/../core/validators/vcredentials.php';
require __DIR__ . '/../core/validators/vrequest.php';


return function(App $app) {

    $app->post('/user/login', function() {

        // GET & DECODE: User request data
        $inputUserData = json_decode(file_get_contents("php://input"), true);

        // CATCH: Login exceptions
        try {
            $userData = login($inputUserData);
        }
        catch (Exception $error_message) {

            // Exception message
            $error_message = $error_message->getMessage();

            // SECURITY: Hide which credential is incorrect
            if ($error_message == 'incorrect password' || $error_message == 'incorrect username') {
                http_response_code(400);
                echo json_encode(['error'=>'incorrect credentials']);
                exit;
            }

            http_response_code(400);
            echo json_encode(['error'=>$error_message]);
            exit;
        }
        catch (TypeError) { // Invalid JSON format
            http_response_code(400);
            echo json_encode(["error" => "invalid JSON format"]);
            exit;
        }

        // User login succeeded
        http_response_code(200);
        echo json_encode(['message'=>'login succeeded', 'user_data'=>$userData]);
        exit;
    });

    $app->post('/user/signup', function() {

        // GET & DECODE: User request data
        $inputUserData = json_decode(file_get_contents("php://input"), true);

        // CATCH: Signup exceptions
        try {
            signup($inputUserData);
        }
        catch (Exception $error_message) {

            // CONFLICT: User with username exists
            if ($error_message->getMessage() == 'user with same username exists') {
                http_response_code(409);
                echo json_encode(['error'=>$error_message->getMessage()]);
                exit;
            }

            http_response_code(400);
            echo json_encode(['error'=>$error_message->getMessage()]);
            exit;

        }
        catch (TypeError) { // Invalid JSON format
            http_response_code(400);
            echo json_encode(["error" => "invalid JSON format"]);
            exit;
        }

        // User signup succeeded
        http_response_code(200);
        echo json_encode(['message'=>'signup succeeded']);
        exit;
    });

};
