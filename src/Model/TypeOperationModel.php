<?php

namespace App\Model;

use Silex\Application;
use Doctrine\DBAL\Query\QueryBuilder;

class TypeOperationModel {
	private $db;
	public function __construct(Application $app) {
		$this->db = $app ['db'];
	}
	public function getAllTypeOperations() {
		$queryBuilder = new QueryBuilder ( $this->db );
		$queryBuilder->select ( 'p.id_type', 'p.libelle_operation' )->from ( 'type_operation', 'p' )->addOrderBy ( 'p.libelle_operation', 'ASC' );
		return $queryBuilder->execute ()->fetchAll ();
	}
	public function insertOperationType($data) {
		$queryBuilder = new QueryBuilder ( $this->db );
		$queryBuilder->insert ( 'type_operation' )->values ( [
				'libelle_operation' => '?'
		] )->setParameter ( 0, $data ['libelle_operation'] );
		return $queryBuilder->execute ();
	}
	public function deleteOperationType($id) {
		$qb = new QueryBuilder ( $this->db );
		$qb->delete ( 'type_operation' );
		$qb->where ( 'id_type = :id' );
		$qb->setParameter ( 'id', $id );
		return $qb->execute ();
	}
	public function getOperationType($id) {
		$queryBuilder = new QueryBuilder ( $this->db );
		$queryBuilder->select ( 'p.id_type', 'p.libelle_operation' )->from ( 'type_operation', 'p' )->where ( "p.id_type = :id" );
		$queryBuilder->setParameter ( "id", $id );
		return $queryBuilder->execute ()->fetchAll () [0];
	}
	public function editOperationType($donnees) {
		$queryBuilder = new QueryBuilder ( $this->db );
		$queryBuilder->update ( 'type_operation' )->set ( 'libelle_operation', ':libelle_operation' )->where ( "id_type = :id" )->setParameter ( 'libelle_operation', $donnees ['libelle_operation'] )->setParameter ( "id", $donnees ["id_type"] );
		return $queryBuilder->execute ();
	}
}