<?php

namespace App\Model;

use Silex\Application;
use Doctrine\DBAL\Query\QueryBuilder;

class ProduitModel {
	private $db;
	public function __construct(Application $app) {
		$this->db = $app ['db'];
	}
	public function getAllProduits() {
		$queryBuilder = new QueryBuilder ( $this->db );
		$queryBuilder->select ( 'p.id', 't.libelle', 'p.nom', 'p.prix', 'p.photo' )->from ( 'produits', 'p' )->innerJoin ( 'p', 'typeProduits', 't', 'p.typeProduit_id=t.id' )->addOrderBy ( 'p.nom', 'ASC' );
		return $queryBuilder->execute ()->fetchAll ();
	}
	public function getProduct($id) {
		$queryBuilder = new QueryBuilder ( $this->db );
		$queryBuilder->select ( 'p.id', 't.libelle', 'p.nom', 'p.prix', 'p.photo','p.typeProduit_id' )->from ( 'produits', 'p' )->innerJoin ( 'p', 'typeProduits', 't', 'p.typeProduit_id=t.id' )->addOrderBy ( 'p.nom', 'ASC' )->where ( 'p.id = :id' );
		$queryBuilder->setParameter ( "id", $id );
		return $queryBuilder->execute ()->fetchAll () [0];
	}
	public function insertProduit($donnees) {
		$queryBuilder = new QueryBuilder ( $this->db );
		$queryBuilder->insert ( 'produits' )->values ( [ 
				'nom' => '?',
				'typeProduit_id' => '?',
				'prix' => '?',
				'photo' => '?' 
		] )->setParameter ( 0, $donnees ['nom'] )->setParameter ( 1, $donnees ['typeProduit_id'] )->setParameter ( 2, $donnees ['prix'] )->setParameter ( 3, $donnees ['photo'] );
		return $queryBuilder->execute ();
	}
	public function deleteProduit($id) {
		$qb = new QueryBuilder ( $this->db );
		$qb->delete ( 'produits' );
		$qb->where ( 'id = :id' );
		$qb->setParameter ( 'id', $id );
		return $qb->execute ();
	}
	public function editProduit($donnees) {
		$queryBuilder = new QueryBuilder ( $this->db );
		$queryBuilder->update ( 'produits' )->set ( 'nom', ':nom' )->set ( 'prix', ':prix' )->set ( 'photo', ':photo' )->setParameter ( 'nom', $donnees ['nom'] )->setParameter ( 'prix', $donnees ['prix'] )->setParameter ( 'photo', $donnees ['photo'] )->setParameter ( "id", $donnees ["id"] )->where ( "id = :id" );
		return $queryBuilder->execute ();
	}
}