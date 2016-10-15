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
}