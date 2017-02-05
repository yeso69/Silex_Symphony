<?php
namespace App\Controller;

use App\Model\UserModel;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;   // modif version 2.0

use Silex\Provider\CsrfServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;   // pour utiliser request

class UserController implements ControllerProviderInterface {

	private $userModel;

	public function index(Application $app) {
		return $this->connexionUser($app);
	}

	public function connexionUser(Application $app, Request $req)
	{
//		return $app["twig"]->render('login.html.twig');

        $app->register(new FormServiceProvider());
        $app->register(new CsrfServiceProvider());
        $app->register(new ValidatorServiceProvider());
        $app->register(new TranslationServiceProvider(), array(
            'translator.domains' => array(),
            'locale'            => 'fr',
        ));

        //création du formulaire de conexion
        $form = $app['form.factory']->createBuilder(FormType::class)
            ->add('login')
            ->add('password')
            ->add('login', TextType::class, array(
                'constraints' => array(new NotBlank(), new Length(array('min' => 5))),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'Identifiant'),
                'required'    => true,
                'label' => false
            ))
            ->add('password', PasswordType::class, array(
                'constraints' => array(new NotBlank(), new Length(array('min' => 5))),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'Mot de passe'),
                'label' => false
            ))

            ->getForm();

        //recupération des donnes recues en post
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {//Si le formulaire est ok on vérifie si les logins existent et sont ok
            $this->userModel = new UserModel($app);
            $donnees = $form->getData();

            $data=$this->userModel->verif_login_mdp_Utilisateur($donnees['login'],$donnees['password']);

            if($data != NULL)//si l'authentification est ok on met les infos utilisateur en session
            {
                $app['session']->set('roles', $data['roles']);  //dans twig {{ app.session.get('roles') }}
                $app['session']->set('username', $data['username']);
                $app['session']->set('logged', 1);
                $app['session']->set('user_id', $data['id']);
                $app['session']->getFlashBag()->add('notifications',
                    array('type' => 'success', 'message' =>'Bienvenue '.$data['username'].', vous être désormais connecté !'));
                return $app->redirect($app["url_generator"]->generate("accueil"));
            }
            else
            {
                $app['session']->getFlashBag()->add('notifications',
                    array('type' => 'danger', 'message' =>'Login ou mot de passe incorrect !'));
            }
        }

        // display the form
        return $app['twig']->render('login.html.twig', array( 'form' => $form->createView() ));
	}

    public function register(Application $app, Request $req){
        $app->register(new FormServiceProvider());
        $app->register(new CsrfServiceProvider());
        $app->register(new TranslationServiceProvider(), array(
            'translator.domains' => array(),
            'locale'            => 'fr',
        ));

        $form = $app['form.factory']->createBuilder(FormType::class)
            ->add('username', TextType::class, array(
                'constraints' => array(new NotBlank(), new Length(array('min' => 5))),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'Identifiant'),
                'required'    => true,
                'label' => false
            ))
            ->add('password', PasswordType::class, array(
                'constraints' => array(new NotBlank(), new Length(array('min' => 5))),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'Mot de passe'),
                'label' => false
            ))
            ->add('motdepasse', PasswordType::class, array(
                'constraints' => array(new NotBlank(), new Length(array('min' => 5))),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'Confirmation'),
                'label' => false
            ))
            ->add('email', EmailType::class, array(
                'constraints' => new Email(),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'mon@mail.com'),
                'label' => false
            ))

            ->getForm();

        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {//Si le formulaire est ok on vérifie si les logins existent et sont ok
            $donnees = $form->getData();
            if(!($donnees['password'] == $donnees['motdepasse'])){
                $app['session']->getFlashBag()->add('notifications',
                    array('type' => 'warning', 'message' =>'Les mots de passe ne correspondent pas.'));
                // display the form
                return $app['twig']->render('register.html.twig', array('form' => $form->createView()));
            }

            $donnees['password'] = $app['security.encoder.digest']->encodePassword($donnees['password'], '');
            // do something with the data
            $this->userModel = new UserModel($app);
            $data=$this->userModel->insertUser($donnees);

            // redirect somewhere
            $app['session']->getFlashBag()->add('notifications',
                array('type' => 'success', 'message' =>'Vous êtes désormais inscrit, connectez-vous !'));
            return $app->redirect($app["url_generator"]->generate("user.login"));
        }

        // display the form
        return $app['twig']->render('register.html.twig', array('form' => $form->createView()));

    }

//	public function validFormConnexionUser(Application $app, Request $req)
//	{
//
//		$app['session']->clear();
//		$donnees['login']=$req->get('login');
//		$donnees['password']=$req->get('password');
//		$this->userModel = new UserModel($app);
//		$data=$this->userModel->verif_login_mdp_Utilisateur($donnees['login'],$donnees['password']);
//
//		if($data != NULL)
//		{
//			$app['session']->set('roles', $data['roles']);  //dans twig {{ app.session.get('roles') }}
//			$app['session']->set('username', $data['username']);
//			$app['session']->set('logged', 1);
//			$app['session']->set('user_id', $data['id']);
//			return $app->redirect($app["url_generator"]->generate("accueil"));
//		}
//		else
//		{
//			$app['session']->set('erreur','Login ou mot de passe incorrect');
//			//return $app["twig"]->render('login.html.twig');
//		}
//	}
	public function deconnexionSession(Application $app)
	{
		$app['session']->clear();
        $app['session']->getFlashBag()->add('notifications',
            array('type' => 'info', 'message' =>'Vous êtes désormais déconnecté.'));
		return $app->redirect($app["url_generator"]->generate("accueil"));
	}

	public function connect(Application $app) {
		$controllers = $app['controllers_factory'];
		$controllers->match('/', 'App\Controller\UserController::index')->bind('user.index');
		$controllers->get('/login', 'App\Controller\UserController::connexionUser')->bind('user.login');
		$controllers->post('/login', 'App\Controller\UserController::connexionUser')->bind('user.validFormlogin');
		$controllers->get('/logout', 'App\Controller\UserController::deconnexionSession')->bind('user.logout');
        $controllers->get('/register', 'App\Controller\UserController::register')->bind('user.register');
        $controllers->post('/register', 'App\Controller\UserController::register')->bind('user.validInscription');


        return $controllers;
	}
}