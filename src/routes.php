<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use Slim\App;

require __DIR__ . '/core/authentication.php';
require __DIR__ . '/core/helpers.php';


// SERVICES QUERY SIMULATION
$services = [
    [
        'title'=>'Microsoft Office Pro - Windows',
        'services'=>[
            ['title'=>'Microsoft Office 2024', 'price'=>360],
            ['title'=>'Microsoft Office 2021', 'price'=>260],
            ['title'=>'Microsoft Office 2019', 'price'=>240],
            ['title'=>'Microsoft Office 2016', 'price'=>220]
        ]
    ], [
        'title'=>'Microsoft Office HB - Mac',
        'services'=>[
            ['title'=>'Microsoft Office 2024', 'price'=>4500],
            ['title'=>'Microsoft Office 2021', 'price'=>2500],
            ['title'=>'Microsoft Office 2019', 'price'=>1500],
            ['title'=>'Microsoft Office 2016', 'price'=>1000]
        ]
    ], [
        'title'=>'Antivirus Software',
        'services'=>[
            ['title'=>'Macfee - 1 PC, 1 year protection', 'price'=>4500],
            ['title'=>'Macfee - 10 devices, 1 year', 'price'=>2500],
            ['title'=>'Avast Ultimate - 1 PC, 1 year protection', 'price'=>1500],
            ['title'=>'Avast Ultimate - 10 devices, 1 year', 'price'=>1000]
        ]
    ], [
        'title'=>'Graphic Design Software',
        'services'=>[
            ['title'=>'Adobe Creative Cloud - 1 year activation', 'price'=>4500],
            ['title'=>'Adobe Express - Premium 1 year activation', 'price'=>1000],
            ['title'=>'Affinity - 2 bundle lifetime subscription', 'price'=>1500],
            ['title'=>'Canva Pro* - Lifetime subscription', 'price'=>1200],
            ['title'=>'Corel Draw - Lifetime subscription', 'price'=>1500]
        ]
    ],
];

// PRODUCTS QUERY SIMULATION
$products = [
    [
        'name'=>'CCTV Camera',
        'description'=>'Home security cameras video surveillance systems isolated vector illustration. Pro Vector',
        'tags'=>['Security', 'Camera', 'CCTV'],
        'image_url'=>'https://dubaimachines.com/pub/media/wysiwyg/Ip_Cameras.jpg',
        'image_alt'=>'CCTV Camera',
        'price'=>699.99
    ], [
        'name'=>'HP LaserJet',
        'description'=>'HP NeverStop LASERJET 1200W All in one Printer. Print/Scan/Cop',
        'tags'=>['Laserjet', 'Scan', 'Print'],
        'image_url'=>'https://i5.walmartimages.com/seo/HP-Color-LaserJet-Pro-MFP-3301sdw-Wireless-Laser-Color-Printer_6e94955a-835a-4c2d-91a9-6f4c678e265e.57e4d6cbb17d266058e1bd984cd2c6fe.jpeg',
        'image_alt'=>'HP Laserjet Printer',
        'price'=>4600.00
    ], [
        'name'=>'IGUGNIK Toner Cartridge',
        'description'=>'4 Pack 218A BK/C/M/Y Toner Cartridge (with Chip) | Replacement for HP 218A Work with 3201dw MFP 3301cdw MFP 3301fdw MFP 3301sdw | W2180A W2181A W2182A W2183A',
        'tags'=>['Toner', 'Printer', 'Cartridge'],
        'image_url'=>'https://m.media-amazon.com/images/I/712GnELgnIL._AC_.jpg',
        'image_alt'=>'Toner Cartridge',
        'price'=>749.99
    ], [
        'name'=>'Apple 15-inch MacBook Air M4',
        'description'=>'2025 Apple MacBook Air 15 3 M4 Processor 16GB RAM 256GB SSD',
        'tags'=>['Apple', 'Laptop', 'Macbook'],
        'image_url'=>'https://media.product.which.co.uk/prod/images/original/21246656a3ee-apple-macbook-pro-16-inch-2001.jpg',
        'image_alt'=>'Apple Macbook',
        'price'=>8999.99
    ]
];


return function(App $app) {

    // TWIG VIEW ROUTES
    $app->get('/', function(Request $request, Response $response){
        global $products, $services;

        // TWIG VIEW
        $view = Twig::fromRequest($request);

        // SESSION: Get token from user cookies
        $token = getTokenFromCookie($request);

        // CORE: User authentication results
        $userData = authenticate($token);

        $URL_BASENAME = $_ENV['URL_BASENAME'];

        // CONDITION: User is not authenticated
        if (!$userData) {
            return $view->render($response, 'index.twig', [
                'URL_BASENAME'=> $URL_BASENAME,
                'services'=>$services,
                'products'=>$products,
                'request'=>$request
            ]);
        }

        return $view->render($response, 'index.twig', [
            'URL_BASENAME'=> $URL_BASENAME,
            'USER_DATA'=>$userData,
            'services'=>$services,
            'products'=>$products,
            'request'=>$request
        ]);
    });

    $app->get('/store', function(Request $request, Response $response) {
        global $products;

        // TWIG VIEW
        $view = Twig::fromRequest($request);

        // SESSION: Get token from user cookies
        $token = getTokenFromCookie($request);

        $URL_BASENAME = $_ENV['URL_BASENAME'];
        $ROUTE_TITLE = 'Store';

        // CORE: User authentication results
        $userData = authenticate($token);

        // CONDITION: User is not authenticated
        if (!$userData) {
            return $view->render($response, 'store.twig', [
                'URL_BASENAME'=> $URL_BASENAME,
                'products'=>$products,
                'request'=>$request
            ]);
        }

        // RENDER: View the store page
        return $view->render($response, 'store.twig', [
            'URL_BASENAME'=> $URL_BASENAME,
            'USER_DATA'=>$userData,
            'products'=>$products,
            'request'=>$request
        ]);
    });

    $app->get('/login', function(Request $request, Response $response) {

        // TWIG VIEW
        $view = Twig::fromRequest($request);

        // SESSION: Get token from user cookies
        $token = getTokenFromCookie($request);

        // CORE: User authentication results
        $userData = authenticate($token);

        $URL_BASENAME = $_ENV['URL_BASENAME'];

        // CONDITION: User is already authenticated
        if ($userData) {
            return $view->render($response, 'index.twig', [
                'URL_BASENAME'=>$URL_BASENAME,
                'USER_DATA'=>$userData,
                'request'=>$request
            ]);
        }

        // RENDER: View the login page
        return $view->render($response, 'login.twig', [
            'URL_BASENAME'=>$URL_BASENAME,
            'request'=>$request
        ]);
    });

    $app->get('/signup', function(Request $request, Response $response) {

        // TWIG VIEW
        $view = Twig::fromRequest($request);

        // SESSION: Get token from user cookies
        $token = getTokenFromCookie($request);

        // CORE: User authentication results
        $userData = authenticate($token);

        $URL_BASENAME = $_ENV['URL_BASENAME'];

        // CONDITION: User is already authenticated
        if ($userData) {
            return $view->render($response, 'index.twig', [
                'URL_BASENAME'=>$URL_BASENAME,
                'USER_DATA'=>$userData,
                'request'=>$request
            ]);
        }

        // RENDER: View the signup page
        return $view->render($response, 'signup.twig', [
            'URL_BASENAME'=>$URL_BASENAME,
            'request'=>$request
        ]);
    });

    // NO TEMPLATES ROUTES
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
