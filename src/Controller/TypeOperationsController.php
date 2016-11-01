<?php

namespace App\Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use App\Model\TypeOperationModel;
use Symfony\Component\HttpFoundation\Response;
use App\Model\OperationModel;
use Symfony\Component\Security\Csrf\CsrfToken;

class TypeOperationsController implements ControllerProviderInterface {
	private $typeOperationModel;
	public function index(Application $app) {
		return $this->show ( $app ); // appel de la méthode show
	}
	public function show(Application $app) {
		$this->typeOperationModel = new TypeOperationModel ( $app );
		$produits = $this->typeOperationModel->getAllTypeOperations ();
		return $app ["twig"]->render ( 'typeOperationModel/v_table_typeOperationModel.twig', [ 
				'data' => $produits 
		] );
		;
	}
	public function connect(Application $app) { // http://silex.sensiolabs.org/doc/providers.html#controller-providers
		$controllers = $app ['controllers_factory'];
		
		$controllers->get ( '/', 'App\Controller\TypeOperationsController::index' )->bind ( 'typeoperation.index' );
		$controllers->get ( '/show', 'App\Controller\TypeOperationsController::show' )->bind ( 'typeoperation.show' );
		
		$controllers->get ( '/add', 'App\Controller\TypeOperationsController::add' )->bind ( 'typeoperation.add' );
		$controllers->post ( '/add', 'App\Controller\TypeOperationsController::validFormAdd' )->bind ( 'typeoperation.validFormAdd' );
		
		$controllers->get ( '/delete/{id}', 'App\Controller\TypeOperationsController::delete' )->bind ( 'typeoperation.delete' );
		$controllers->post ( '/delete', 'App\Controller\TypeOperationsController::validFormDelete' )->bind ( 'typeoperation.validFormDelete' );
		
		$controllers->get ( '/edit/{id}', 'App\Controller\TypeOperationsController::edit' )->bind ( 'typeoperation.edit' );
		$controllers->post ( '/edit', 'App\Controller\TypeOperationsController::validFormEdit' )->bind ( 'typeoperation.validFormEdit' );
		
		return $controllers;
	}
	public function validFormAdd(Application $app) {
		if (isset ( $_POST ['libelle_operation'] )) {
			$donnees = [ 
					'libelle_operation' => htmlspecialchars ( $_POST ['libelle_operation'] ),
					'id_type' => htmlspecialchars ( $_POST ['id_type'] ),
					'_csrf_token' => htmlspecialchars ( $_POST ['_csrf_token'] ) 
			];
			if (! $app ['csrf.token_manager']->isTokenValid ( new CsrfToken ( 'addType', $donnees ['_csrf_token'] ) )) {
				return new Response ( 'Error', 401 /* ignored */, array (
						'X-Status-Code' => 401 
				) );
			}
			if ((! preg_match ( "/^[A-Za-z ]{2,}/", $donnees ['libelle_operation'] )))
				$erreurs ['libelle_operation'] = 'nom composé de 2 lettres minimum';
			;
			
			if (! empty ( $erreurs )) {
				return $app ["twig"]->render ( 'typeOperationModel/v_form_create_typeOperationModel.twig', [ 
						'donnees' => $donnees,
						'erreurs' => $erreurs 
				] );
			} else {
				$this->typeOperationModel = new TypeOperationModel ( $app );
				$this->typeOperationModel->insertOperationType ( $donnees );
				return $app->redirect ( $app ["url_generator"]->generate ( "typeoperation.index" ) );
			}
		} else
			return "error ????? PB data form";
	}
	public function add(Application $app) {
		$csrf = $app ['csrf.token_manager']->getToken ( 'addType' );
		return $app ["twig"]->render ( 'typeOperationModel/v_form_create_typeOperationModel.twig', [ 
				'csrf' => $csrf 
		] );
	}
	public function validFormDelete(Application $app) {
		$id = intval ( $_POST ['id'] );
		
		$this->typeOperationModel = new TypeOperationModel ( $app );
		$_csrf_token = htmlspecialchars ( $_POST ['_csrf_token'] );
		if (! $app ['csrf.token_manager']->isTokenValid ( new CsrfToken ( 'delType', $_csrf_token ) )) {
			return new Response ( 'Error', 401 /* ignored */, array (
					'X-Status-Code' => 401
			) );
		}
		$tmp = new OperationModel ( $app );
		
		if ($tmp->CountOperationByType ( $id ) == 0) {
			$this->typeOperationModel->deleteOperationType ( $id );
			return $app->redirect ( $app ["url_generator"]->generate ( "typeoperation.index" ) );
		} else {
			$data = array (
					'id_type' => $id,
					'_csrf_token' => $_csrf_token,
					'error' => '' 
			);
			
			return $app ["twig"]->render ( 'typeOperationModel/v_form_delete_typeOperationModel.twig', [ 
					'donnees' => $data 
			] );
		}
	}
	public function delete(Application $app, $id) {
		$this->typeOperationModel = new TypeOperationModel ( $app );
		$data = array (
				'id_type' => $id ,
				'_csrf_token' => $app ['csrf.token_manager']->getToken ( 'delType' )
		);
		return $app ["twig"]->render ( 'typeOperationModel/v_form_delete_typeOperationModel.twig', [ 
				'donnees' => $data 
		] );
	}
	public function validFormEdit(Application $app) {
		if (isset ( $_POST ['id_type'] ) && isset ( $_POST ['libelle_operation'] )) {
			$donnees = [ 
					'libelle_operation' => htmlspecialchars ( $_POST ['libelle_operation'] ),
					'id_type' => htmlspecialchars ( $_POST ['id_type'] ),
					'_csrf_token' => htmlspecialchars ( $_POST ['_csrf_token'] ) 
			];
			if (! $app ['csrf.token_manager']->isTokenValid ( new CsrfToken ( 'editType', $donnees ['_csrf_token'] ) )) {
				return new Response ( 'Error', 401 /* ignored */, array (
						'X-Status-Code' => 401 
				) );
			}
			if (! is_numeric ( $donnees ['id_type'] )) {
				return new Response ( 'Error', 400 /* ignored */, array (
						'X-Status-Code' => 400 
				) );
			}
			
			if ((! preg_match ( "/^[A-Za-z ]{2,}/", $donnees ['libelle_operation'] )))
				$erreurs ['libelle_operation'] = 'nom composé de 2 lettres minimum';
			if (! is_numeric ( $donnees ['id_type'] ))
				$erreurs ['id_type'] = 'veuillez saisir une valeur';
			
			if (! empty ( $erreurs )) {
				
				return $app ["twig"]->render ( 'typeOperationModel/v_form_edit_typeOperationModel.twig', [ 
						'donnees' => $donnees,
						'erreurs' => $erreurs 
				] );
			} else {
				$this->typeOperationModel = new TypeOperationModel ( $app );
				$this->typeOperationModel->editOperationType ( $donnees );
				return $app->redirect ( $app ["url_generator"]->generate ( "typeoperation.index" ) );
			}
		} else {
			return new Response ( 'Error 2', 400 /* ignored */, array (
					'X-Status-Code' => 400 
			) );
		}
	}
	public function edit(Application $app, $id) {
		$this->typeOperationModel = new TypeOperationModel ( $app );
		$donnees = $this->typeOperationModel->getOperationType ( $id );
		$donnees ['_csrf_token'] = $app ['csrf.token_manager']->getToken ( 'editType' );
		return $app ["twig"]->render ( 'typeOperationModel/v_form_edit_typeOperationModel.twig', [ 
				'donnees' => $donnees 
		] );
	}
}