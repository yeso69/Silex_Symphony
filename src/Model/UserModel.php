<?php
namespace App\Model;

use Silex\Application;
use Doctrine\DBAL\Query\QueryBuilder;;

class UserModel {

	private $db;

	public function __construct(Application $app) {
		$this->db = $app['db'];
	}

	public function verif_login_mdp_Utilisateur($login,$mdp){
		$sql = "SELECT id,username,motdepasse,roles FROM users WHERE username = ? AND motdepasse = ?";
		$res=$this->db->executeQuery($sql,[$login,$mdp]);   //md5($mdp);
		if($res->rowCount()==1)
			return $res->fetch();
		else
			return false;
	}

    public function insertUser($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->insert('users')
            ->values([
                'username' => '?',
                'password' => '?',
                'motdepasse' => '?',
                'roles' => '?',
                'email' => '?',
                'isEnabled' => '?',


            ])
            ->setParameter(0, $donnees['username'])
            ->setParameter(1, $donnees['password'])
            ->setParameter(2, $donnees['motdepasse'])
            ->setParameter(3, "ROLE_CLIENT")
            ->setParameter(4, $donnees['email'])
            ->setParameter(5, true)
        ;
        return $queryBuilder->execute();
    }

	public function getUser($user_id) {
		$queryBuilder = new QueryBuilder($this->db);
		$queryBuilder
			->select('*')
			->from('users')
			->where('id = :idUser')
			->setParameter('idUser', $user_id);
		return $queryBuilder->execute()->fetch();

	}
}