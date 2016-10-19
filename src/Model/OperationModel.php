<?php

namespace App\Model;

use Silex\Application;
use Doctrine\DBAL\Query\QueryBuilder;

class OperationModel {
	private $db;
	public function __construct(Application $app) {
		$this->db = $app ['db'];
	}
	public function getAllOperations() {
		$queryBuilder = new QueryBuilder ( $this->db );
		$queryBuilder->select ( 'o.id_operation', 't.libelle_operation', 'o.date_effet', 'o.montant', 'o.type' )->from ( 'operation', 'o' )->innerJoin ( 'o', 'type_operation', 't', 'o.id_libelle_operation=t.id_type' )->addOrderBy ( 'o.id_operation', 'ASC' );
		return $queryBuilder->execute ()->fetchAll ();
	}
	public function insertOperation($data) {
		$queryBuilder = new QueryBuilder ( $this->db );
		$queryBuilder->insert ( 'operation' )->values ( [ 
				'type' => '?',
				'id_libelle_operation' => '?',
				'montant' => '?',
				'date_effet' => '?' 
		] )->setParameter ( 0, $data ['type'] )->setParameter ( 1, $data ['id_libelle_operation'] )->setParameter ( 2, $data ['montant'] )->setParameter ( 3, $data ['date_effet'] );
		return $queryBuilder->execute ();
	}
	public function deleteOperation($id) {
		$qb = new QueryBuilder ( $this->db );
		$qb->delete ( 'operation' );
		$qb->where ( 'id_operation = :id' );
		$qb->setParameter ( 'id', $id );
		return $qb->execute ();
	}
	public function getOperation($id) {
		$queryBuilder = new QueryBuilder ( $this->db );
		$queryBuilder->select ( 'o.id_operation', 'o.date_effet', 'o.montant', 'o.type', 'o.id_libelle_operation' )->from ( 'operation', 'o' )->where ( "o.id_operation = :id" );
		$queryBuilder->setParameter ( "id", $id );
		return $queryBuilder->execute ()->fetchAll () [0];
	}
	public function editOperation($donnees) {
		$queryBuilder = new QueryBuilder ( $this->db );
		$queryBuilder->update ( 'operation' )->set ( 'date_effet', ':date_effet' )->set ( 'montant', ':montant' )->set ( 'type', ':type' )->set ( 'id_libelle_operation', ':id_libelle_operation' )->where ( "id_operation = :id" )->setParameter ( 'date_effet', $donnees ['date_effet'] )->setParameter ( 'montant', $donnees ['montant'] )->setParameter ( 'type', $donnees ['type'] )->setParameter ( "id", $donnees ["id_operation"] )->setParameter ( "id_libelle_operation", $donnees ["id_libelle_operation"] );
		return $queryBuilder->execute ();
	}
}