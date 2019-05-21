<?php

require_once __DIR__.'/../vendor/autoload.php';

use Dionchaika\View\View;

$view = new View(
    __DIR__.'/views',
    __DIR__.'/compiled'
);

echo $view->render('test', [

    'lang'           => 'en',
    'error'          => false,
    'success'        => true,
    'errorMessage'   => 'Error!',
    'successMessage' => 'Success!',

    'users' => [

        [ 'name' => 'Max',   'email' => 'max@example.com'   ],
        [ 'name' => 'John',  'email' => 'john@example.com'  ],
        [ 'name' => 'Steve', 'email' => 'steve@example.com' ],
        [ 'name' => 'Julia', 'email' => 'julia@example.com' ]

    ]

]);
