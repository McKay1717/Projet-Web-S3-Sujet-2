<?php
// ***************************************
// Montage des contrôleurs sur le routeur
$app->mount ( "/", new App\Controller\OperationsController ( $app ) );
$app->mount ( "/type", new App\Controller\TypeOperationsController( $app ) );
$app->mount("/user", new App\Controller\UserController($app));
