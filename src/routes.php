<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;
use Slim\App;


return function(App $app) {

    $app->get('/', function(Request $request, Response $response, $args){

        // Data for services tables
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

        $view = Twig::fromRequest($request);
        return $view->render($response, 'index.twig', [
            'title'=>'Home',
            'isAuthorized'=>false,
            'services'=>$services,
            'request'=>$request
        ]);
    });

};
