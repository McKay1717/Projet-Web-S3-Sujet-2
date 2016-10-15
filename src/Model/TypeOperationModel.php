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
}