<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App;


return function(App $app) {

    $app->post('/user/login', function(Request $request, Response $response) {

        $URL_BASENAME = $_ENV['URL_BASENAME'];

        // REQUEST: Get user login data
        $inputUserData = $request->getParsedBody();

        // CORE: User login result
        $loginResult = login($request, $inputUserData['username'], $inputUserData['password']);

        // CONDITION: User logged in successfully, redirect to home/index page
        if ($loginResult['status']) {
            // User logged in successfully, redirect to home page
            return $response
                    ->withHeader('Location', "$URL_BASENAME/")
                    ->withStatus(302);
        }

        // User login failed, let user retry login
        return $response
                ->withHeader('Location', "$URL_BASENAME/login")
                ->withStatus(302);
    });

    $app->post('/user/signup', function(Request $request, Response $response) {

        // REQUEST: Get user login data
        $inputUserData = $request->getParsedBody();

        // User login result
        $signupResult = signup([
            'username'=>$inputUserData['username'],
            'displayName'=>$inputUserData['names'],
            'email'=>$inputUserData['email'],
            'password'=>$inputUserData['password'],
        ]);

        $URL_BASENAME = $_ENV['URL_BASENAME'];

        // CONDITION: User could not signup, let user retry signup
        if(!$signupResult) {
            return $response
                ->withHeader('Location', "$URL_BASENAME/signup")
                ->withStatus(302);
        }

        // User signed up successfully, redirect to login page
        return $response
                ->withHeader('Location', "$URL_BASENAME/login")
                ->withStatus(302);
    });

};
