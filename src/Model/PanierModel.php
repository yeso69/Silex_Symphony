<?php

namespace App\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;

class PanierModel {

    private $db;

    public function __construct(Application $app) {
        $this->db = $app['db'];
    }
    // http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/query-builder.html#join-clauses
    public function getAllPanier($id_commande) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.quantite', 'p.prix', 'p.produit_id', 'p.dateAjoutPanier', 'pr.nom', 'pr.photo', 't.libelle')
            ->from('paniers', 'p')
            ->where('p.commande_id= :id')
            ->innerJoin('p', 'produits', 'pr', 'p.produit_id=pr.id')
            ->innerJoin('pr', 'typeProduits', 't', 'pr.typeProduit_id=t.id')

            ->setParameter('id',$id_commande);

        return $queryBuilder->execute()->fetchAll();

    }

    public function insertPanier($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->insert('paniers')
            ->values([
                'quantite' => '?',
                'prix' => '?',
                'user_id' => '?',
                'produit_id' => '?',
                'commande_id' => '?'
            ])
            ->setParameter(0, $donnees['quantite'])
            ->setParameter(1, $donnees['prix'])
            ->setParameter(2, $donnees['user_id'])
            ->setParameter(3, $donnees['produit_id'])
            ->setParameter(4, $donnees['commande_id'])
        ;
        return $queryBuilder->execute();
    }
    public function getPanier($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.id', 't.quantite', 'p.prix', 'p.dateAjoutPanier', 'u.username', 'pr.nom', 'p.commande_id')
            ->from('paniers', 'p')
            ->innerJoin('p', 'users', 'u', 'p.user_id=u.username')
            ->innerJoin('p', 'produits', 'pr', 'p.produit_id=pr.nom')
            ->where('commande_id= :id');
        return $queryBuilder->execute()->fetchAll();
    }
    public function getStockProd($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('stock')
            ->from('produits', 'p')
            ->where('id= ?')
            ->setParameter(0, $id);
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
            ->setParameter('id',(int)$id)
        ;
        return $queryBuilder->execute();
    }



}