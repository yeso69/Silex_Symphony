<?php
namespace App\Controller;

use App\Model\PanierModel;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;   // modif version 2.0

use Symfony\Component\HttpFoundation\Request;   // pour utiliser request

use App\Model\ProduitModel;
use App\Model\TypeProduitModel;

use Symfony\Component\Validator\Constraints as Assert;   // pour utiliser la validation
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Security;

class PanierController implements ControllerProviderInterface
{
    private $produitModel;
    private $typeProduitModel;
    private $panierModel;


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


        public function show(Application $app) {
            $panier = $app['session']->get('panier');

            if(!isset($panier))//si c'est un panier vide créer un array vide pr eviter les erreurs
                $app['session']->set('panier', array());

        $this->panierModel = new PanierModel($app);
        $this->produitModel = new ProduitModel($app);

        // boucle qui va recupérer les infos des produits dans le panier
        $produits = array();
        if(count($panier) > 0) { // si ce n'est pas un panier vide
            foreach ($panier as $id => $qte) {
                $produit = $this->produitModel->getProduit($id);
                $produit['qte'] = $qte;//ajout de la qte pr chaque produit
                array_push($produits, $produit);//ajout du produit dans le tableau
            }
        }
        //var_dump($produits);die();
        return $app["twig"]->render('frontOff/Panier/show.html.twig',['data'=>$produits]);
    }


    public function add(Application $app, $id) {
        $panier = $app['session']->get('panier');

        if($panier == null){
            $panier = array();
            $panier[$id]= 1;
        }
        else{
            if(isset($panier[$id]))
                $panier[$id]+= 1;
            else
                $panier[$id] = 1;
        }

        $app['session']->set('panier',$panier);
        //var_dump($panier);die();
        return $app->redirect($app["url_generator"]->generate("panier.show"));

//        var_dump("hello");
//        $this->panierModel = new PanierModel($app);
//        $paniers = $this->produitModel->getProduit($id);
//        var_dump($paniers);
//        $this->panierModel = new PanierModel($app);
//        $paniers = $this->panierModel->insertPanier();
//        return $app["twig"]->render('frontOff/Produit/show.html.twig',['path'=>BASE_URL]);
    }

    public function connect(Application $app) {  //http://silex.sensiolabs.org/doc/providers.html#controller-providers
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'App\Controller\panierController::index')->bind('panier.index');
        $controllers->get('/show', 'App\Controller\panierController::show')->bind('panier.show');
        $controllers->post('/update', 'App\Controller\panierController::update')->bind('panier.update');
        $controllers->get('/remove/{id}', 'App\Controller\panierController::remove')->bind('panier.remove');
        $controllers->get('/add/{id}', 'App\Controller\panierController::add')->bind('panier.add');

        return $controllers;
    }
}
