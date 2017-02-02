<?php
namespace App\Controller\admin;

use App\Model\CommandeModel;
use App\Model\EtatModel;
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
    private $etatModel;
    private $commandeModel;
    private $panierModel;


    public function initModel(Application $app){  //  ne fonctionne pas dans le const
        $this->produitModel = new ProduitModel($app);
        $this->typeProduitModel = new TypeProduitModel($app);
    }
    public function index(Application $app) {
        return $this->show($app);
    }
    public function show(Application $app) {
        $this->commandeModel = new CommandeModel($app);
        //recupération des commandes de l'utilisateur
        $commandes = $this->commandeModel->getAllCommande();
         //   var_dump( $commandes);die;
        return $app["twig"]->render('backOff/Commande/show.html.twig',['commandes'=>$commandes]);
    }
    public function showetat(Application $app,$id) {
        $this->etatModel = new EtatModel($app);
        //recupération des commandes de l'utilisateur
        $etat = $this->etatModel->getAllEtat();
        $this->commandeModel = new CommandeModel($app);
        $commandes = $this->commandeModel->getCommande($id);
        return $app["twig"]->render('backOff/Commande/etat.html.twig',['etats'=>$etat,'id'=>$id,'commandes'=>$commandes]);
    }
    public function updateState(Application $app,$id) {
        $this->commandeModel = new CommandeModel($app);
        $this->commandeModel->updateStateCommande($id,$_POST['etats']);
        return $this->show($app);
    }
    public function showDetails(Application $app, $id){
        $this->commandeModel = new CommandeModel($app);
        $this->panierModel = new PanierModel($app);

        $commande = $this->commandeModel->getCommande($id);

        $paniers = $this->panierModel->getAllPanier($id); //recup des paniers
//        var_dump($commande);die;
        return $app["twig"]->render('backOff/Commande/detail.html.twig',['commande'=>$commande, 'produits'=>$paniers]);

    }
    public function connect(Application $app) {  //http://silex.sensiolabs.org/doc/providers.html#controller-providers
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'App\Controller\admin\commandeController::index')->bind('admin.commande.index');
        $controllers->get('/show', 'App\Controller\admin\commandeController::show')->bind('admin.commande.show');
        $controllers->get('/showetat/{id}', 'App\Controller\admin\commandeController::showetat')->bind('admin.commande.showetat');
        $controllers->post('/showetat/{id}', 'App\Controller\admin\commandeController::updateState')->bind('admin.commande.updateState');
        $controllers->get('/showdetails/{id}', 'App\Controller\admin\commandeController::showDetails')->bind('admin.commande.showdetails');
        return $controllers;
    }
}