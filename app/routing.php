<?php
// ***************************************
// Montage des contrôleurs sur le routeur
$app->mount ( "/", new App\Controller\OperationsController ( $app ) );
//$app->mount("/produit", new App\Controller\ProduitController($app));
