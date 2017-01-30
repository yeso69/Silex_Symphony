<?php

namespace App\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;

class CommandeModel {

    private $db;

    public function __construct(Application $app) {
        $this->db = $app['db'];
    }
    // http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/query-builder.html#join-clauses
    public function getAllCommandes($user_id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('c.id', 'c.prix', 'c.date_achat', 'c.etat_id', 'e.libelle')
            ->from('commandes', 'c')
            ->where('c.user_id= :id')
            ->addOrderBy('c.date_achat', 'DESC')
            ->innerJoin('c', 'etats', 'e', 'c.etat_id=e.id')
            ->setParameter('id', $user_id);

        return $queryBuilder->execute()->fetchAll();

    }

    public function insertCommande($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->insert('commandes')
            ->values([
                'user_id' => '?',
                'prix' => '?',
                'etat_id' => '?'
            ])
            ->setParameter(0, $donnees['user_id'])
            ->setParameter(1, $donnees['prix'])
            ->setParameter(2, 1)
        ;
        $queryBuilder->execute();
        return $this->getLast($donnees['user_id']);
    }

    public function getLast($user_id){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('id')
            ->from('commandes')
            ->addOrderBy('date_achat', 'DESC')
            ->where('user_id= :id')
            ->setParameter('id', $user_id);

        $commande = $queryBuilder->execute()->fetch();
        return $commande['id'];
    }

    public function getCommande($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('c.id', 'c.prix', 'c.date_achat', 'c.etat_id', 'e.libelle')
            ->from('commandes', 'c')
            ->where('c.id= :id')
            ->innerJoin('c', 'etats', 'e', 'c.etat_id=e.id')
            ->setParameter('id', $id);
        return $queryBuilder->execute()->fetchAll();
    }

    public function updateProduit($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('produits')
            ->set('nom', '?')
            ->set('typeProduit_id','?')
            ->set('prix','?')
            ->set('photo','?')
            ->where('id= ?')
            ->setParameter(0, $donnees['nom'])
            ->setParameter(1, $donnees['typeProduit_id'])
            ->setParameter(2, $donnees['prix'])
            ->setParameter(3, $donnees['photo'])
            ->setParameter(4, $donnees['id']);
        return $queryBuilder->execute();
    }

    public function deleteProduit($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->delete('produits')
            ->where('id = :id')
            ->setParameter('id',(int)$id);
        return $queryBuilder->execute();
    }



}