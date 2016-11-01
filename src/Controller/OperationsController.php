<?php

namespace App\Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use App\Model\OperationModel;
use App\Model\TypeOperationModel;
use Symfony\Component\HttpFoundation\Response;
use App\Helper\helper_date;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class OperationsController implements ControllerProviderInterface {
	private $operationModel;
	private $typeOperationModel;
	private $dateHelper;
	public function index(Application $app) {
		return $this->show ( $app ); // appel de la méthode show
	}
	public function show(Application $app) {
		$this->dateHelper = new helper_date ();
		$this->operationModel = new OperationModel ( $app );
		$produits = $this->operationModel->getAllOperations ();
		foreach ( $produits as $key => $value )
			
			$produits [$key] ['date_effet'] = $this->dateHelper->date_us_to_fr ( $value ['date_effet'] );
		
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
	public function validFormAdd(Application $app, Request $request) {
		/*
		 * $this->dateHelper = new helper_date ();
		 *
		 * if (isset ( $_POST ['type'] ) && isset ( $_POST ['id_libelle_operation'] ) and isset ( $_POST ['montant'] ) and isset ( $_POST ['date_effet'] )) {
		 * $donnees = [
		 * 'type' => htmlspecialchars ( $_POST ['type'] ),
		 * 'id_libelle_operation' => htmlspecialchars ( $_POST ['id_libelle_operation'] ),
		 * 'montant' => htmlspecialchars ( $_POST ['montant'] ),
		 * 'date_effet' => htmlspecialchars ( $_POST ['date_effet'] ),
		 * '_csrf_token' => htmlspecialchars ( $_POST ['_csrf_token'] )
		 * ];
		 * if (! $app ['csrf.token_manager']->isTokenValid ( new CsrfToken ( 'addOp', $donnees ['_csrf_token'] ) )) {
		 * return new Response ( 'Error', 401 /* ignored , array (
		 * 'X-Status-Code' => 401
		 * ) );
		 * }
		 * if ((! preg_match ( "/^[A-Za-z ]{2,}/", $donnees ['type'] )))
		 * $erreurs ['type'] = 'nom composé de 2 lettres minimum';
		 * if (! is_numeric ( $donnees ['id_libelle_operation'] ))
		 * $erreurs ['id_libelle_operation'] = 'veuillez saisir une valeur';
		 * if (! is_numeric ( $donnees ['montant'] ))
		 * $erreurs ['montant'] = 'saisir une valeur numérique';
		 * if ($this->dateHelper->validateDate ( $donnees ['date_effet'] ))
		 * $erreurs ['date_effet'] = 'Date incorrect';
		 *
		 * if (! empty ( $erreurs )) {
		 * $this->typeOperationModel = new TypeOperationModel ( $app );
		 * $type_operations = $this->typeOperationModel->getAllTypeOperations ();
		 * return $app ["twig"]->render ( 'operation/v_form_create_operation.twig', [
		 * 'donnees' => $donnees,
		 * 'erreurs' => $erreurs,
		 * 'type_operations' => $type_operations
		 * ] );
		 * } else {
		 * $this->operationModel = new OperationModel ( $app );
		 * $donnees ['date_effet'] = $this->dateHelper->formatForDb ( $donnees ['date_effet'] );
		 * $this->operationModel->insertOperation ( $donnees );
		 * return $app->redirect ( $app ["url_generator"]->generate ( "operation.index" ) );
		 * }
		 * } else
		 * return "error ????? PB data form";
		 */
		$this->typeOperationModel = new TypeOperationModel ( $app );
		$type_operations = $this->typeOperationModel->getAllTypeOperations ();
		$prepared_array = array ();
		foreach ( $type_operations as $key => $value ) {
			$prepared_array [$value ['libelle_operation']] = $value ['id_type'];
		}
		
		$initial_data = array (
				'montant' => '0' 
		);
		
		$form = $app ['form.factory']->createBuilder ( FormType::class, $initial_data );
		
		$form = $form->add ( 'Date', DateType::class, array (
				'widget' => 'single_text',
				'input' => 'string',
				'required' => true,
				'constraints' => array (
						new Assert\Date () 
				) 
		) );
		$form = $form->add ( 'Type_operation', ChoiceType::class, array (
				'required' => true,
				'choices' => $prepared_array,
				'constraints' => array (
						new Assert\Type ( array (
								'type' => 'numeric' 
						) ) 
				) 
		) );
		$form = $form->add ( 'montant', MoneyType::class, array (
				'required' => true,
				'constraints' => array (
						new Assert\Type ( array (
								'type' => 'numeric' 
						) ) 
				) 
		) );
		$form = $form->add ( 'Nature', TextType::class, array (
				'required' => true,
				'constraints' => array (
						new Assert\NotBlank (),
						new Assert\Length ( array (
								'min' => 3 
						) ) 
				) 
		) );
		
		$form = $form->getForm ();
		$form->handleRequest ( $request );
		if ($form->isValid ()) {
			$data = $form->getData ();
			
			$this->operationModel = new OperationModel ( $app );
			
			$this->operationModel->insertOperation ( $data );
			
			$app ['session']->getFlashBag ()->add ( 'success', array (
					'message' => 'operation created!' 
			) );
			return $app->redirect ( $app ['url_generator']->generate ( 'operation.index' ) );
		}
		
		return $app ['twig']->render ( 'operation/index.twig', array (
				'form' => $form->createView () 
		) );
	}
	public function add(Application $app) {
		/*
		 * Old version
		 * $csrf = $app ['csrf.token_manager']->getToken ( 'addOp' );
		 * $this->typeOperationModel = new TypeOperationModel ( $app );
		 * $type_operations = $this->typeOperationModel->getAllTypeOperations ();
		 * return $app ["twig"]->render ( 'operation/v_form_create_operation.twig', [
		 * 'type_operations' => $type_operations,
		 * 'csrf' => $csrf
		 * ] );
		 */
		$this->typeOperationModel = new TypeOperationModel ( $app );
		$type_operations = $this->typeOperationModel->getAllTypeOperations ();
		$prepared_array = array ();
		foreach ( $type_operations as $key => $value ) {
			$prepared_array [$value ['libelle_operation']] = $value ['id_type'];
		}
		
		$initial_data = array (
				'date_effet' => '',
				'type' => '',
				'montant' => '0',
				'id_libelle_operation' => '' 
		);
		
		$form = $app ['form.factory']->createBuilder ( FormType::class, $initial_data );
		
		$form = $form->add ( 'Date', DateType::class, array (
				'widget' => 'single_text',
				'input' => 'string',
				'required' => true,
				'constraints' => array (
						new Assert\Date () 
				) 
		) );
		$form = $form->add ( 'Type_operation', ChoiceType::class, array (
				'required' => true,
				'choices' => $prepared_array,
				'constraints' => array (
						new Assert\Type ( array (
								'type' => 'numeric' 
						) ) 
				) 
		) );
		$form = $form->add ( 'montant', MoneyType::class, array (
				'required' => true,
				'constraints' => array (
						new Assert\Type ( array (
								'type' => 'numeric' 
						) ) 
				) 
		) );
		$form = $form->add ( 'Nature', TextType::class, array (
				'required' => true,
				'constraints' => array (
						new Assert\NotBlank (),
						new Assert\Length ( array (
								'min' => 3 
						) ) 
				) 
		) );
		
		$form = $form->getForm ();
		return $app ['twig']->render ( 'operation/index.twig', array (
				'form' => $form->createView () 
		) );
	}
	public function validFormDelete(Application $app) {
		$id = intval ( $_POST ['id'] );
		$_csrf_token = htmlspecialchars ( $_POST ['_csrf_token'] );
		if (! $app ['csrf.token_manager']->isTokenValid ( new CsrfToken ( 'delOp', $_csrf_token ) )) {
			return new Response ( 'Error', 401 /* ignored */, array (
					'X-Status-Code' => 401 
			) );
		}
		
		$this->operationModel = new OperationModel ( $app );
		
		$this->operationModel->deleteOperation ( $id );
		
		return $app->redirect ( $app ["url_generator"]->generate ( "operation.index" ) );
	}
	public function delete(Application $app, $id) {
		$this->operationModel = new OperationModel ( $app );
		$data = array (
				'id_operation' => $id,
				'_csrf_token' => $app ['csrf.token_manager']->getToken ( 'delOp' ) 
		);
		return $app ["twig"]->render ( 'operation/v_form_delete_operation.twig', [ 
				'donnees' => $data 
		] );
	}
	public function validFormEdit(Application $app) {
		$this->dateHelper = new helper_date ();
		if (isset ( $_POST ['id_operation'] ) && isset ( $_POST ['type'] ) && isset ( $_POST ['id_libelle_operation'] ) and isset ( $_POST ['montant'] ) and isset ( $_POST ['date_effet'] )) {
			$donnees = [ 
					'type' => htmlspecialchars ( $_POST ['type'] ),
					'id_libelle_operation' => htmlspecialchars ( $_POST ['id_libelle_operation'] ),
					'montant' => htmlspecialchars ( $_POST ['montant'] ),
					'date_effet' => htmlspecialchars ( $_POST ['date_effet'] ),
					'id_operation' => htmlspecialchars ( $_POST ['id_operation'] ),
					'_csrf_token' => htmlspecialchars ( $_POST ['_csrf_token'] ) 
			];
			if (! $app ['csrf.token_manager']->isTokenValid ( new CsrfToken ( 'editOp', $donnees ['_csrf_token'] ) )) {
				return new Response ( 'Error', 401 /* ignored */, array (
						'X-Status-Code' => 401 
				) );
			}
			if (! is_numeric ( $donnees ['id_operation'] )) {
				return new Response ( 'Error', 400 /* ignored */, array (
						'X-Status-Code' => 400 
				) );
			}
			
			if (count ( $app ['validator']->validate ( $donnees ['type'], array (
					new Assert\NotBlank (),
					new Assert\Length ( array (
							'min' => 3 
					) ) 
			) ) ))
				$erreurs ['type'] = 'Valeur invalide';
			
			if (count ( $app ['validator']->validate ( $donnees ['id_libelle_operation'], array (
					new Assert\Type ( array (
							'type' => 'numeric' 
					) ) 
			) ) ))
				$erreurs ['id_libelle_operation'] = "Valeur invalide";
			
			if (count ( $app ['validator']->validate ( $donnees ['montant'], array (
					new Assert\Type ( array (
							'type' => 'numeric' 
					) ) 
			) ) ))
				$erreurs ['montant'] = "Valeur invalide";
			$donnees ['date_effet'] = $this->dateHelper->formatForDb ( $donnees ['date_effet'] );
			if (count ( $app ['validator']->validate ( $donnees ['date_effet'], array (
					new Assert\Date () 
			) ) ) > 0)
				$erreurs ['date_effet'] = "Date Invalide";
			
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
				$donnees ['date_effet'] = $this->dateHelper->formatForDb ( $donnees ['date_effet'] );
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
		$this->dateHelper = new helper_date ();
		$this->typeOperationModel = new TypeOperationModel ( $app );
		$type_operations = $this->typeOperationModel->getAllTypeOperations ();
		$this->operationModel = new OperationModel ( $app );
		
		$donnees = $this->operationModel->getOperation ( $id );
		$donnees ['date_effet'] = $this->dateHelper->date_us_to_fr ( $donnees ['date_effet'] );
		$donnees ['_csrf_token'] = $app ['csrf.token_manager']->getToken ( 'editOp' );
		return $app ["twig"]->render ( 'operation/v_form_edit_operation.twig', [ 
				'type_operations' => $type_operations,
				'donnees' => $donnees 
		] );
	}
}