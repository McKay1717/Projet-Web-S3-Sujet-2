<?php

namespace App\Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use App\Model\OperationModel;
use App\Model\TypeOperationModel;
use Symfony\Component\HttpFoundation\Response;

class OperationsController implements ControllerProviderInterface {
	private $operationModel;
	private $typeOperationModel;
	public function index(Application $app) {
		return $this->show ( $app ); // appel de la méthode show
	}
	public function show(Application $app) {
		
		$this->operationModel = new OperationModel ( $app );
		$produits = $this->operationModel->getAllOperations ();
		return $app ["twig"]->render ( 'operation/v_table_operation.twig', [ 
				'data' => $produits 
		] );
		;
	}
	public function connect(Application $app) { // http://silex.sensiolabs.org/doc/providers.html#controller-providers
		$controllers = $app ['controllers_factory'];
		
		$controllers->get ( '/', 'App\Controller\OperationsController::index' )->bind ( 'operation.index' );
		$controllers->get ( '/show', 'App\Controller\OperationsController::show' )->bind ( 'operation.show' );
		
		$controllers->get ( '/add', 'App\Controller\OperationsController::add' )->bind ( 'operation.add' );
		$controllers->post ( '/add', 'App\Controller\OperationsController::validFormAdd' )->bind ( 'operation.validFormAdd' );
		
		$controllers->get ( '/delete/{id}', 'App\Controller\OperationsController::delete' )->bind ( 'operation.delete' );
		$controllers->post ( '/delete', 'App\Controller\OperationsController::validFormDelete' )->bind ( 'operation.validFormDelete' );
		
		$controllers->get ( '/edit/{id}', 'App\Controller\OperationsController::edit' )->bind ( 'operation.edit' );
		$controllers->post ( '/edit', 'App\Controller\OperationsController::validFormEdit' )->bind ( 'operation.validFormEdit' );
		
		return $controllers;
	}
	public function validFormAdd(Application $app) {
		if (isset ( $_POST ['type'] ) && isset ( $_POST ['id_libelle_operation'] ) and isset ( $_POST ['montant'] ) and isset ( $_POST ['date_effet'] )) {
			$donnees = [ 
					'type' => htmlspecialchars ( $_POST ['type'] ),
					'id_libelle_operation' => htmlspecialchars ( $_POST ['id_libelle_operation'] ),
					'montant' => htmlspecialchars ( $_POST ['montant'] ),
					'date_effet' => htmlspecialchars ( $_POST ['date_effet'] ) 
			];
			if ((! preg_match ( "/^[A-Za-z ]{2,}/", $donnees ['type'] )))
				$erreurs ['type'] = 'nom composé de 2 lettres minimum';
			if (! is_numeric ( $donnees ['id_libelle_operation'] ))
				$erreurs ['id_libelle_operation'] = 'veuillez saisir une valeur';
			if (! is_numeric ( $donnees ['montant'] ))
				$erreurs ['montant'] = 'saisir une valeur numérique';
			
			list ( $y, $m, $d ) = explode ( '-', $donnees ['date_effet'] );
			if (! checkdate ( $m, $d, $y ))
				$erreurs ['date_effet'] = 'Date incorrect';
			
			if (! empty ( $erreurs )) {
				$this->typeOperationModel = new TypeOperationModel ( $app );
				$type_operations = $this->typeOperationModel->getAllTypeOperations ();
				return $app ["twig"]->render ( 'operation/v_form_create_operation.twig', [ 
						'donnees' => $donnees,
						'erreurs' => $erreurs,
						'type_operations' => $type_operations 
				] );
			} else {
				$this->operationModel = new OperationModel ( $app );
				$this->operationModel->insertOperation ( $donnees );
				return $app->redirect ( $app ["url_generator"]->generate ( "operation.index" ) );
			}
		} else
			return "error ????? PB data form";
	}
	public function add(Application $app) {
		$this->typeOperationModel = new TypeOperationModel ( $app );
		$type_operations = $this->typeOperationModel->getAllTypeOperations ();
		return $app ["twig"]->render ( 'operation/v_form_create_operation.twig', [ 
				'type_operations' => $type_operations 
		] );
	}
	public function validFormDelete(Application $app) {
		$id = intval ( $_POST ['id'] );
		
		$this->operationModel = new OperationModel ( $app );
		
		$this->operationModel->deleteOperation ( $id );
		return $app->redirect ( $app ["url_generator"]->generate ( "operation.index" ) );
	}
	public function delete(Application $app, $id) {
		$this->operationModel = new OperationModel ( $app );
		$data = array (
				'id_operation' => $id 
		);
		return $app ["twig"]->render ( 'operation/v_form_delete_operation.twig', [ 
				'donnees' => $data 
		] );
	}
	public function validFormEdit(Application $app) {
		if (isset ( $_POST ['id_operation'] ) && isset ( $_POST ['type'] ) && isset ( $_POST ['id_libelle_operation'] ) and isset ( $_POST ['montant'] ) and isset ( $_POST ['date_effet'] )) {
			$donnees = [ 
					'type' => htmlspecialchars ( $_POST ['type'] ),
					'id_libelle_operation' => htmlspecialchars ( $_POST ['id_libelle_operation'] ),
					'montant' => htmlspecialchars ( $_POST ['montant'] ),
					'date_effet' => htmlspecialchars ( $_POST ['date_effet'] ),
					'id_operation' => htmlspecialchars ( $_POST ['id_operation'] ) 
			];
			if (! is_numeric ( $donnees ['id_operation'] )) {
				return new Response ( 'Error', 400 /* ignored */, array (
						'X-Status-Code' => 400 
				) );
			}
			
			if ((! preg_match ( "/^[A-Za-z ]{2,}/", $donnees ['type'] )))
				$erreurs ['type'] = 'nom composé de 2 lettres minimum';
			if (! is_numeric ( $donnees ['id_libelle_operation'] ))
				$erreurs ['id_libelle_operation'] = 'veuillez saisir une valeur';
			if (! is_numeric ( $donnees ['montant'] ))
				$erreurs ['montant'] = 'saisir une valeur numérique';
			
			list ( $y, $m, $d ) = explode ( '-', $donnees ['date_effet'] );
			if (! checkdate ( $m, $d, $y ))
				$erreurs ['date_effet'] = 'Date incorrect';
			
			if (! empty ( $erreurs )) {
				$this->typeOperationModel = new TypeOperationModel ( $app );
				$type_operations = $this->typeOperationModel->getAllTypeOperations ();
				return $app ["twig"]->render ( 'operation/v_form_edit_operation.twig', [ 
						'donnees' => $donnees,
						'erreurs' => $erreurs,
						'type_operations' => $type_operations 
				] );
			} else {
				$this->operationModel = new OperationModel ( $app );
				$this->operationModel->editOperation ( $donnees );
				return $app->redirect ( $app ["url_generator"]->generate ( "operation.index" ) );
			}
		} else {
			return new Response ( 'Error', 400 /* ignored */, array (
					'X-Status-Code' => 400 
			) );
		}
	}
	public function edit(Application $app, $id) {
		$this->typeOperationModel = new TypeOperationModel ( $app );
		$type_operations = $this->typeOperationModel->getAllTypeOperations ();
		$this->operationModel = new OperationModel ( $app );
		$donnees = $this->operationModel->getOperation ( $id );
		return $app ["twig"]->render ( 'operation/v_form_edit_operation.twig', [ 
				'type_operations' => $type_operations,
				'donnees' => $donnees 
		] );
	}
}