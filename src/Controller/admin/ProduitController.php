<?php
namespace App\Controller\admin;

use Doctrine\Common\Collections\Selectable;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;   // modif version 2.0

use Silex\Provider\CsrfServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;   // pour utiliser request

use App\Model\ProduitModel;
use App\Model\TypeProduitModel;

use Symfony\Component\Validator\Constraints as Assert;   // pour utiliser la validation
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Security;

class ProduitController implements ControllerProviderInterface
{
    private $produitModel;
    private $typeProduitModel;


    public function initModel(Application $app){  //  ne fonctionne pas dans le const
        $this->produitModel = new ProduitModel($app);
        $this->typeProduitModel = new TypeProduitModel($app);
    }

    public function index(Application $app) {
        return $this->show($app);
    }

    public function show(Application $app) {
        $this->produitModel = new ProduitModel($app);
        $produits = $this->produitModel->getAllProduits();
        $this->typeProduitModel = new TypeProduitModel($app);
        $typeProduit = $this->typeProduitModel->getAllTypeProduits();
        return $app["twig"]->render('backOff/Produit/show.html.twig',['data'=>$produits,'type'=>$typeProduit]);
    }

    public function add(Application $app) {
        $this->typeProduitModel = new TypeProduitModel($app);
        $typeProduits = $this->typeProduitModel->getAllTypeProduits();
        return $app["twig"]->render('backOff/Produit/add.html.twig',['typeProduits'=>$typeProduits]);
    }

    public function validFormAdd(Application $app, Request $req)
    {
        // var_dump($app['request']->attributes);
//		return $app["twig"]->render('login.html.twig');

        $app->register(new FormServiceProvider());
        $app->register(new CsrfServiceProvider());
        $app->register(new ValidatorServiceProvider());
        $app->register(new TranslationServiceProvider(), array(
            'translator.domains' => array(),
            'locale' => 'fr',
        ));
        $this->typeProduitModel = new TypeProduitModel($app);
        $typeProduits = $this->typeProduitModel->getAllTypeProduits();
        $tab = array();
        $tab[0]= "Saisisez une valeur";
        for ($i=0;$i<sizeof($typeProduits);$i++){
            $tab[$i+1]=$typeProduits[$i]['libelle'];
        }
        $tab = array_flip($tab);
        //création du formulaire de conexion
        $form = $app['form.factory']->createBuilder(FormType::class)
            ->add('nom')
            ->add('typeProduit_id')
            ->add('prix')
            ->add('photo')
            ->add('dispo')
            ->add('stock')
            ->add('nom', TextType::class, array(
                'constraints' => array(new NotBlank(), new Length(array('min' => 3))),
                'attr' => array('class' => 'form-control', 'placeholder' => 'Nom'),
                'required' => true,
                'label' => 'Nom'
            ))
            ->add('typeProduit_id', ChoiceType::class, array(
                'constraints' => array(new NotBlank()),
                'choices'  => $tab,
                'attr' => array('class' => 'form-control', 'placeholder' => 'Type'),
                'required' => true,
                'label' => 'Type de produit'
            ))
            ->add('prix', NumberType::class, array(
                'constraints' => array(new NotBlank(), new Length(array('min' => 1))),
                'attr' => array('class' => 'form-control', 'placeholder' => 'Prix'),
                'required' => true,
                'label' => 'Prix'
            ))
            ->add('photo', FileType::class, array(
                'constraints' => array(new NotBlank(), new Length(array('min' => 2))),
                'attr' => array('class' => 'form-control', 'placeholder' => ''),
                'label' => "Photo"
            ))
            ->add('dispo', NumberType::class, array(
                'constraints' => array(new NotBlank(), new Length(array('min' => 1))),
                'attr' => array('class' => 'form-control', 'placeholder' => 'dispo'),
                'required' => true,
                'label' => 'Dispo'
            ))
            ->add('stock', NumberType::class, array(
                'constraints' => array(new NotBlank(), new Length(array('min' => 1))),
                'attr' => array('class' => 'form-control', 'placeholder' => 'Stock'),
                'required' => true,
                'label' => 'Stock'
            ))
            ->getForm();

        //recupération des donnes recues en post
        $form->handleRequest($req);
        $donnees = $form->getData();
//        $webPath = $this->get('kernel')->getRootDir().'/../web';
//        var_dump($webPath); die;
        if ($form->isSubmitted() && $form->isValid()) {//Si le formulaire est ok on vérifie si les logins existent et sont ok
//            $dir='C:\UwAmp\www\Silex_Symphonyy\web\assets\img';
//            var_dump($form);
//            $donnees['photo']->move($dir, $donnees['photo']);
            $this->ProduitModel = new ProduitModel($app);
            $this->ProduitModel->insertProduit($donnees);
            return $app->redirect($app["url_generator"]->generate("admin.produit.index"));
        }
        $this->typeProduitModel = new TypeProduitModel($app);
        $typeProduits = $this->typeProduitModel->getAllTypeProduits();
        return $app["twig"]->render('backOff/Produit/add.html.twig', array( 'form' => $form->createView() , 'typeProduits' => $typeProduits));

    }

    public function delete(Application $app, $id) {
        $this->typeProduitModel = new TypeProduitModel($app);
        $typeProduits = $this->typeProduitModel->getAllTypeProduits();
        $this->produitModel = new ProduitModel($app);
        $donnees = $this->produitModel->getProduit($id);
        return $app["twig"]->render('backOff/Produit/delete.html.twig',['typeProduits'=>$typeProduits,'donnees'=>$donnees]);
    }

    public function validFormDelete(Application $app, Request $req) {
        $id=$app->escape($req->get('id'));
        if (is_numeric($id)) {
            $this->produitModel = new ProduitModel($app);
            $this->produitModel->deleteProduit($id);
            return $app->redirect($app["url_generator"]->generate("produit.index"));
        }
        else
            return $app->abort(404, 'error Pb id form Delete');
    }


    public function edit(Application $app, $id) {
        $this->typeProduitModel = new TypeProduitModel($app);
        $typeProduits = $this->typeProduitModel->getAllTypeProduits();
        $this->produitModel = new ProduitModel($app);
        $donnees = $this->produitModel->getProduit($id);
        return $app["twig"]->render('backOff/Produit/edit.html.twig',['typeProduits'=>$typeProduits,'donnees'=>$donnees]);
    }

    public function validFormEdit(Application $app,Request $req) {
        // var_dump($app['request']->attributes);
        if (isset($_POST['nom']) && isset($_POST['typeProduit_id']) and isset($_POST['prix']) and isset($_POST['photo']) and isset($_POST['id'])) {
            $donnees = [
                'nom' => htmlspecialchars($_POST['nom']),                    // echapper les entrées
                'typeProduit_id' => htmlspecialchars($req->get('typeProduit_id')),  //$app['request']-> ne focntionne plus
                'prix' => htmlspecialchars($req->get('prix')),
                'photo' => $app->escape($req->get('photo')),  //$req->query->get('photo')-> ne focntionne plus
                'id' => $app->escape($req->get('id'))//$req->query->get('photo')
            ];
            if ((! preg_match("/^[A-Za-z ]{2,}/",$donnees['nom']))) $erreurs['nom']='nom composé de 2 lettres minimum';
            if(! is_numeric($donnees['typeProduit_id']))$erreurs['typeProduit_id']='veuillez saisir une valeur';
            if(! is_numeric($donnees['prix']))$erreurs['prix']='saisir une valeur numérique';
            if (! preg_match("/[A-Za-z0-9]{2,}.(jpeg|jpg|png)/",$donnees['photo'])) $erreurs['photo']='nom de fichier incorrect (extension jpeg , jpg ou png)';
            if(! is_numeric($donnees['id']))$erreurs['id']='saisir une valeur numérique';
            $contraintes = new Assert\Collection(
                [
                    'id' => [new Assert\NotBlank(),new Assert\Type('digit')],
                    'typeProduit_id' => [new Assert\NotBlank(),new Assert\Type('digit')],
                    'nom' => [
                        new Assert\NotBlank(['message'=>'saisir une valeur']),
                        new Assert\Length(['min'=>2, 'minMessage'=>"Le nom doit faire au moins {{ limit }} caractères."])
                    ],
                    //http://symfony.com/doc/master/reference/constraints/Regex.html
                    'photo' => [
                        new Assert\Length(array('min' => 5)),
                        new Assert\Regex([ 'pattern' => '/[A-Za-z0-9]{2,}.(jpeg|jpg|png)/',
                        'match'   => true,
                        'message' => 'nom de fichier incorrect (extension jpeg , jpg ou png)' ]),
                    ],
                    'prix' => new Assert\Type(array(
                        'type'    => 'numeric',
                        'message' => 'La valeur {{ value }} n\'est pas valide, le type est {{ type }}.',
                    ))
                ]);
            $errors = $app['validator']->validate($donnees,$contraintes);  // ce n'est pas validateValue

        //    $violationList = $this->get('validator')->validateValue($req->request->all(), $contraintes);
//var_dump($violationList);

          //   die();
            if (count($errors) > 0) {
                // foreach ($errors as $error) {
                //     echo $error->getPropertyPath().' '.$error->getMessage()."\n";
                // }
                // //die();
                //var_dump($erreurs);

            // if(! empty($erreurs))
            // {
                $this->typeProduitModel = new TypeProduitModel($app);
                $typeProduits = $this->typeProduitModel->getAllTypeProduits();
                return $app["twig"]->render('backOff/Produit/edit.html.twig',['donnees'=>$donnees,'errors'=>$errors,'erreurs'=>$erreurs,'typeProduits'=>$typeProduits]);
            }
            else
            {
                $this->ProduitModel = new ProduitModel($app);
                $this->ProduitModel->updateProduit($donnees);
                return $app->redirect($app["url_generator"]->generate("admin.produit.index"));
            }

        }
        else
            return $app->abort(404, 'error Pb id form edit');

    }

    public function connect(Application $app) {  //http://silex.sensiolabs.org/doc/providers.html#controller-providers
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'App\Controller\admin\produitController::index')->bind('admin.produit.index');
        $controllers->get('/show', 'App\Controller\admin\produitController::show')->bind('admin.produit.show');
        $controllers->get('/add', 'App\Controller\admin\produitController::validFormAdd')->bind('admin.produit.add');
        $controllers->post('/add', 'App\Controller\admin\produitController::validFormAdd')->bind('admin.produit.validFormAdd');

        $controllers->get('/delete/{id}', 'App\Controller\admin\produitController::delete')->bind('admin.produit.delete')->assert('id', '\d+');
        $controllers->delete('/delete', 'App\Controller\admin\produitController::validFormDelete')->bind('admin.produit.validFormDelete');

        $controllers->get('/edit/{id}', 'App\Controller\admin\produitController::edit')->bind('admin.produit.edit');
        $controllers->post('/edit/{id}', 'App\Controller\admin\produitController::validFormEdit')->bind('admin.produit.validFormEdit');

        return $controllers;
    }

    private function getParameter($string)
    {
    }
}
