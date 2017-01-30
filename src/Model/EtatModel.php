<?php

namespace App\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;

class EtatModel
{

    private $db;

    public function __construct(Application $app)
    {
        $this->db = $app['db'];
    }
    public function getAllEtat() {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('e.id','e.libelle')
            ->from('etats', 'e');

        return $queryBuilder->execute()->fetchAll();

    }




}