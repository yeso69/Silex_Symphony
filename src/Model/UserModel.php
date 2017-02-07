<?php
namespace App\Model;

use Silex\Application;
use Doctrine\DBAL\Query\QueryBuilder;;

class UserModel {

	private $db;

	public function __construct(Application $app) {
		$this->db = $app['db'];
	}

	public function verif_login_mdp_Utilisateur($username,$password){
        $queryBuilder = new QueryBuilder($this->db);

        $queryBuilder
            ->select('*')
            ->from('users')
            ->where('username = :username')
            ->andWhere('password = :password')
            ->setParameter('username', $username)
            ->setParameter('password', $password)
            ;
        return $queryBuilder->execute()->fetch();
//
//		$res=$this->db->executeQuery($sql,[$login,$mdp]);   //md5($mdp);
//		if($res->rowCount()==1)
//			return $res->fetch();
//		else
//			return false;
	}

    public function insertUser($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->insert('users')
            ->values([
                'username' => '?',
                'password' => '?',
                'roles' => '?',
                'email' => '?',
                'fname' => '?',
                'lname' => '?',
                'address' => '?',
                'city' => '?',
                'zip' => '?',
                'isEnabled' => '?',
            ])
            ->setParameter(0, $donnees['username'])
            ->setParameter(1, $donnees['password'])
            ->setParameter(2, "ROLE_CLIENT")
            ->setParameter(3, $donnees['email'])
            ->setParameter(4, $donnees['fname'])
            ->setParameter(5, $donnees['lname'])
            ->setParameter(6, $donnees['address'])
            ->setParameter(7, $donnees['city'])
            ->setParameter(8, $donnees['zip'])
            ->setParameter(9, true)
        ;
        return $queryBuilder->execute();
    }

    public function updateUser($user){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('users')
            ->set('username','?')
            ->set('password','?')
            ->set('email','?')
            ->set('fname','?')
            ->set('lname','?')
            ->set('address','?')
            ->set('city','?')
            ->set('zip','?')
            ->set('isEnabled','?')
            ->where('id = ?')
            ->setParameter(0, $user['username'])
            ->setParameter(1, $user['password'])
            ->setParameter(2, $user['email'])
            ->setParameter(3, $user['fname'])
            ->setParameter(4, $user['lname'])
            ->setParameter(5, $user['address'])
            ->setParameter(6, $user['city'])
            ->setParameter(7, $user['zip'])
            ->setParameter(8, true)
            ->setParameter(9, $user['id'])

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