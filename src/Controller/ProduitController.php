<?php

namespace App\Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use App\Model\ProduitModel;
use App\Model\TypeProduitModel;

class ProduitController implements ControllerProviderInterface {
	private $produitModel;
	private $typeProduitModel;
	public function index(Application $app) {
		return $this->show ( $app ); // appel de la méthode show
	}
	public function show(Application $app) {
		$this->produitModel = new ProduitModel ( $app );
		$produits = $this->produitModel->getAllProduits ();
		return $app ["twig"]->render ( 'produit/v_table_produit.twig', [ 
				'data' => $produits 
		] );
		;
	}
	public function connect(Application $app) { // http://silex.sensiolabs.org/doc/providers.html#controller-providers
		$controllers = $app ['controllers_factory'];
		
		$controllers->get ( '/', 'App\Controller\produitController::index' )->bind ( 'produit.index' );
		$controllers->get ( '/show', 'App\Controller\produitController::show' )->bind ( 'produit.show' );
		
		$controllers->get ( '/add', 'App\Controller\produitController::add' )->bind ( 'produit.add' );
		$controllers->post ( '/add', 'App\Controller\produitController::validFormAdd' )->bind ( 'produit.validFormAdd' );
		
		$controllers->get ( '/delete/{id}', 'App\Controller\produitController::delete' )->bind ( 'produit.delete' );
		$controllers->post ( '/delete', 'App\Controller\produitController::validFormDelete' )->bind ( 'produit.validFormDelete' );
		
		$controllers->get ( '/edit/{id}', 'App\Controller\produitController::edit' )->bind ( 'produit.edit' );
		$controllers->post ( '/edit', 'App\Controller\produitController::validFormEdit' )->bind ( 'produit.validFormEdit' );
		
		return $controllers;
	}
	public function validFormAdd(Application $app) {
		if (isset ( $_POST ['nom'] ) && isset ( $_POST ['typeProduit_id'] ) and isset ( $_POST ['nom'] ) and isset ( $_POST ['photo'] )) {
			$donnees = [ 
					'nom' => htmlspecialchars ( $_POST ['nom'] ),
					'typeProduit_id' => htmlspecialchars ( $_POST ['typeProduit_id'] ),
					'prix' => htmlspecialchars ( $_POST ['prix'] ),
					'photo' => htmlspecialchars ( $_POST ['photo'] ) 
			];
			if ((! preg_match ( "/^[A-Za-z ]{2,}/", $donnees ['nom'] )))
				$erreurs ['nom'] = 'nom composé de 2 lettres minimum';
			if (! is_numeric ( $donnees ['typeProduit_id'] ))
				$erreurs ['typeProduit_id'] = 'veuillez saisir une valeur';
			if (! is_numeric ( $donnees ['prix'] ))
				$erreurs ['prix'] = 'saisir une valeur numérique';
			if (! preg_match ( "/[A-Za-z0-9]{2,}.(jpeg|jpg|png)/", $donnees ['photo'] ))
				$erreurs ['photo'] = 'nom de fichier incorrect (extension jpeg , jpg ou png)';
			
			if (! empty ( $erreurs )) {
				$this->typeProduitModel = new TypeProduitModel ( $app );
				$typeProduits = $this->typeProduitModel->getAllTypeProduits ();
				return $app ["twig"]->render ( 'produit/v_form_create_produit.twig', [ 
						'donnees' => $donnees,
						'erreurs' => $erreurs,
						'typeProduits' => $typeProduits 
				] );
			} else {
				$this->ProduitModel = new ProduitModel ( $app );
				$this->ProduitModel->insertProduit ( $donnees );
				return $app->redirect ( $app ["url_generator"]->generate ( "produit.index" ) );
			}
		} else
			return "error ????? PB data form";
	}
	public function add(Application $app) {
		$this->typeProduitModel = new TypeProduitModel ( $app );
		$typeProduits = $this->typeProduitModel->getAllTypeProduits ();
		return $app ["twig"]->render ( 'produit/v_form_create_produit.twig', [ 
				'typeProduits' => $typeProduits 
		] );
	}
	public function validFormDelete(Application $app) {
		$id = intval ( $_POST ['id'] );
		
		$this->ProduitModel = new ProduitModel ( $app );
		
		$this->ProduitModel->deleteProduit ( $id );
		return $app->redirect ( $app ["url_generator"]->generate ( "produit.index" ) );
	}
	public function delete(Application $app, $id) {
		$this->produitModel = new ProduitModel ( $app );
		$data = $this->produitModel->getProduct ( $id );
		return $app ["twig"]->render ( 'produit/v_form_delete_produit.twig', [ 
				'donnees' => $data 
		] );
	}
	public function edit(Application $app, $id) {
		$this->produitModel = new ProduitModel ( $app );
		$data = $this->produitModel->getProduct ( $id );
		$this->typeProduitModel = new TypeProduitModel ( $app );
		$typeProduits = $this->typeProduitModel->getAllTypeProduits ();
		return $app ["twig"]->render ( 'produit/v_form_edit_produit.twig', [ 
				'donnees' => $data,
				"erreurs" => array (),
				'typeProduits' => $typeProduits 
		] );
	}
	public function validFormEdit(Application $app) {
		$this->typeProduitModel = new TypeProduitModel ( $app );
		$typeProduits = $this->typeProduitModel->getAllTypeProduits ();
		if (isset ( $_POST ['nom'] ) && isset ( $_POST ['id'] ) and isset ( $_POST ['nom'] ) and isset ( $_POST ['photo'] )) {
			$donnees = [ 
					'nom' => htmlspecialchars ( $_POST ['nom'] ),
					'id' => htmlspecialchars ( $_POST ['id'] ),
					'typeProduit_id' => htmlspecialchars ( $_POST ['typeProduit_id'] ),
					'prix' => htmlspecialchars ( $_POST ['prix'] ),
					'photo' => htmlspecialchars ( $_POST ['photo'] ) 
			];
			if ((! preg_match ( "/^[A-Za-z ]{2,}/", $donnees ['nom'] )))
				$erreurs ['nom'] = 'nom composé de 2 lettres minimum';
			if (! is_numeric ( $donnees ['id'] ))
				$erreurs ['id'] = 'veuillez saisir une valeur';
			if (! is_numeric ( $donnees ['prix'] ))
				$erreurs ['prix'] = 'saisir une valeur numérique';
			if (! preg_match ( "/[A-Za-z0-9]{2,}.(jpeg|jpg|png)/", $donnees ['photo'] ))
				$erreurs ['photo'] = 'nom de fichier incorrect (extension jpeg , jpg ou png)';
			
			if (! empty ( $erreurs )) {
				return $app ["twig"]->render ( 'produit/v_form_edit_produit.twig', [ 
						'donnees' => $donnees,
						'erreurs' => $erreurs,
						'typeProduits' => $typeProduits 
				] );
			} else {
				$this->ProduitModel = new ProduitModel ( $app );
				$this->ProduitModel->editProduit ( $donnees );
				return $app->redirect ( $app ["url_generator"]->generate ( "produit.index" ) );
							}
		} else
			return "error ????? PB data form";
	}
}