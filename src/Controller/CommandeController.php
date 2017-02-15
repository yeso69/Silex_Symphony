<?php
namespace App\Controller;

use App\Model\CommandeModel;
use App\Model\PanierModel;
use App\Model\ProduitModel;
use App\Model\TypeProduitModel;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;   // modif version 2.0

use Symfony\Component\HttpFoundation\Request;   // pour utiliser request



use Symfony\Component\Validator\Constraints as Assert;   // pour utiliser la validation
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Security;

class CommandeController implements ControllerProviderInterface
{
    private $produitModel;
    private $typeProduitModel;
    private $panierModel;
    private $commandeModel;


    public function initModel(Application $app){  //  ne fonctionne pas dans le const
        $this->produitModel = new ProduitModel($app);
        $this->typeProduitModel = new TypeProduitModel($app);
    }

    public function index(Application $app) {
        return $this->show($app);
    }

    public function update(Application $app) {
        if(isset($_POST['qte'] ) && isset($_POST['id'])){
            $id = $_POST['id'];
            $qte = $_POST['qte'];
            $panier = $app['session']->get('panier');

            if($qte > 0 && isset($panier[$id])) {
                $panier[$id] = $qte;
                $app['session']->set('panier',$panier);
            }
        }
        return $app->redirect($app["url_generator"]->generate("panier.show"));

}
    public function remove(Application $app, $id) {
        if(isset($id)){
            $panier = $app['session']->get('panier');
            unset($panier[$id]);//on enlève le produit du panier en session
            $app['session']->set('panier',$panier);
        }

        return $app->redirect($app["url_generator"]->generate("panier.show"));
    }


    public function showDetails(Application $app, $id){
        $this->commandeModel = new CommandeModel($app);
        $this->panierModel = new PanierModel($app);

        $commande = $this->commandeModel->getCommande($id);

        $paniers = $this->panierModel->getAllPanier($id); //recup des paniers
//        var_dump($commande);die;
        return $app["twig"]->render('frontOff/Commande/detail.html.twig',['commande'=>$commande, 'produits'=>$paniers]);

    }

        public function show(Application $app) {
            $this->commandeModel = new CommandeModel($app);
            $this->panierModel = new PanierModel($app);

            //recupération des commandes de l'utilisateur
            $commandes = $this->commandeModel->getAllCommandes($app['session']->get('user_id'));

//            var_dump( $commandes[$index]);die;
            return $app["twig"]->render('frontOff/Commande/show.html.twig',['commandes'=>$commandes]);
        }


    public function add(Application $app) {
        $panier = $app['session']->get('panier');
        if($panier == null){
            return $app->redirect($app["url_generator"]->generate("panier.show"));
        }
        $this->panierModel = new PanierModel($app);
        $this->produitModel = new ProduitModel($app);
        $this->commandeModel = new CommandeModel($app);

        $produits = array();
        $total = 0;
        $id_user = $app['session']->get('user_id');

        // boucle qui va recupérer les infos des produits dans le panier et calcul du total
        foreach ($panier as $id => $qte){
            $produit = $this->produitModel->getProduit($id);
            $produit['produit_id'] = $id;//format pr le model
            $produit['quantite'] = $qte;
            $produit['stock'] -= $qte;
            $this->produitModel->changeStock($produit);
            array_push($produits, $produit);//ajout du produit dans le tableau
            $total += $qte * $produit['prix'];
        }


        //creation de la commande et recuperation de son id
        $commande = [
            "user_id" => $id_user,
            "prix" => $total,
        ];
        $id_commande = $this->commandeModel->insertCommande($commande);


        foreach ($produits as $produit) {//Insertion des paniers en BDD
            $produit['user_id'] = $id_user;
            $produit['commande_id'] = $id_commande;
            $this->panierModel->insertPanier($produit);
        }


        $app['session']->remove('panier');
        return $app->redirect($app["url_generator"]->generate("commande.show"));

    }

    public function connect(Application $app) {  //http://silex.sensiolabs.org/doc/providers.html#controller-providers
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'App\Controller\commandeController::index')->bind('commande.index');
        $controllers->get('/show', 'App\Controller\commandeController::show')->bind('commande.show');
        $controllers->get('/show/{id}', 'App\Controller\commandeController::showDetails')->bind('commande.detail');

//        $controllers->post('/update', 'App\Controller\panierController::update')->bind('panier.update');
//        $controllers->get('/remove/{id}', 'App\Controller\panierController::remove')->bind('panier.remove');
        $controllers->get('/add', 'App\Controller\commandeController::add')->bind('commande.add');

        return $controllers;
    }
}
