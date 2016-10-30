<?php

namespace App\Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class UserController implements ControllerProviderInterface {
	public function connect(Application $app) { // http://silex.sensiolabs.org/doc/providers.html#controller-providers
		$controllers = $app ['controllers_factory'];
		
		$controllers->get ( '/login', 'App\Controller\UserController::login' )->bind ( 'user.login' );
		
		return $controllers;
	}

	public function login(Application $app, Request $request) {
		return $app ["twig"]->render ( 'login.twig', array (
				'error' => $app ['security.last_error'] ( $request ),
				'last_username' => $app ['session']->get ( '_security.last_username' ) 
		) );
	}
}