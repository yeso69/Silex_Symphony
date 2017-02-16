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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
    public function captcha(Application $app, $code){
        var_dump($app['session']->get('random_number'));
        var_dump(strtolower($code));
        if($code || @strtolower($code) == strtolower($app['session']->get('random_number')))
        {

            // insert your name , email and text message to your table in db

            return 1;// submitted

        }
        else
        {
            return 0; // invalid code
        }
    }
    public function initcaptcha(){
        $string = '';
        for ($i = 0; $i < 5; $i++) {
            $string .= chr(rand(97, 122));
        }
        return $string;
    }

	public function connexionUser(Application $app, Request $req)//Connexion utilisateur
	{
//		return $app["twig"]->render('login.html.twig');
        $app->register(new FormServiceProvider());
        $app->register(new CsrfServiceProvider());
        $app->register(new ValidatorServiceProvider());
        $app->register(new TranslationServiceProvider(), array(
            'translator.domains' => array(),
            'locale'            => 'fr',
        ));
        $cap = $this->initcaptcha();
        $app['session']->set('random_number', $cap);
        //création du formulaire de conexion
        $form = $app['form.factory']->createBuilder(FormType::class)
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
            $captchaIsGood = $this->captcha($app, $_POST['code']);
            var_dump($captchaIsGood);
            $data=$this->userModel->verif_login_mdp_Utilisateur($donnees['login'],$donnees['password']);
            if($data != NULL && $captchaIsGood == 1)//si l'authentification est ok on met les infos utilisateur en session
            {
                $app['session']->set('roles', $data['roles']);  //dans twig {{ app.session.get('roles') }}
                $app['session']->set('username', $data['username']);
                $app['session']->set('fname', $data['fname']);
                $app['session']->set('lname', $data['lname']);
                $app['session']->set('logged', 1);
                $app['session']->set('user_id', $data['id']);

                $app['session']->getFlashBag()->add('notifications',
                    array('type' => 'success', 'message' =>'Bienvenue '.$data['username'].', vous être désormais connecté !'));
                return $app->redirect($app["url_generator"]->generate("accueil"));
            }
            else
            {
                $app['session']->getFlashBag()->add('notifications',
                    array('type' => 'danger', 'message' =>'Login, mot de passe ou captcha incorrect !'));
            }
        }

        // display the form
        return $app['twig']->render('login.html.twig', array('cap' => $cap, 'form' => $form->createView()));
	}

    public function register(Application $app, Request $req){//Incription
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
                'label' => false,
                'required'    => true
            ))
            ->add('motdepasse', PasswordType::class, array(
                'constraints' => array(new NotBlank(), new Length(array('min' => 5))),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'Confirmation'),
                'label' => false,
                'required'    => true
            ))
            ->add('email', EmailType::class, array(
                'constraints' => new Email(),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'mon@mail.com'),
                'label' => false,
                'required'    => true
            ))
            ->add('fname', TextType::class, array(
                'constraints' => array(new NotBlank(), new Length(array('min' => 2))),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'Prénom'),
                'label' => false,
                'required'    => true
            ))
            ->add('lname', TextType::class, array(
                'constraints' => array(new NotBlank(), new Length(array('min' => 2))),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'Nom'),
                'label' => false,
                'required'    => true
            ))
            ->add('address', TextType::class, array(
                'constraints' => array(new NotBlank(), new Length(array('min' => 5))),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'Adresse'),
                'label' => false,
                'required'    => true
            ))
            ->add('city', TextType::class, array(
                'constraints' => array(new NotBlank()),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'Ville'),
                'label' => false,
                'required'    => true
            ))
            ->add('zip', NumberType::class, array(
                'constraints' => array(new NotBlank(),new Length(array('max' => 5))),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'Code postal'),
                'label' => false,
                'required'    => true
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

            //$donnees['password'] = $app['security.encoder.digest']->encodePassword($donnees['password'], '');
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

    public function modifier(Application $app, Request $req){//Modifier mon compte
        $this->userModel = new UserModel($app);
        $user_id = $app['session']->get('user_id');
        $user=$this->userModel->getUser($user_id);

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
                'label_attr' => array('class' => ''),
                'required'    => true,
                'label' => 'Login',
                'data' => $user['username']
//                'value' => 'chèvre'
            ))
            ->add('password', PasswordType::class, array(
                'constraints' => array(new NotBlank(), new Length(array('min' => 5))),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'Mot de passe', 'value' => $user['password']),
                'label' => 'Mot de passe',
                'required'    => true
            ))
            ->add('motdepasse', PasswordType::class, array(
                'constraints' => array(new NotBlank(), new Length(array('min' => 5))),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'Confirmation'),
                'label' => 'Confirmation',
                'required'    => true
            ))
            ->add('email', EmailType::class, array(
                'constraints' => new Email(),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'mon@email.com', 'value' => $user['email']),
                'label' => 'Email',
                'required'    => true,
                'data' => $user['email'],
            ))
            ->add('fname', TextType::class, array(
                'constraints' => array(new NotBlank(), new Length(array('min' => 2))),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'Prénom', 'value' => $user['fname']),
                'label' => 'Prénom',
                'required'    => true,
                'data' => $user['fname'],

            ))
            ->add('lname', TextType::class, array(
                'constraints' => array(new NotBlank(), new Length(array('min' => 2))),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'Nom', 'value' => $user['lname']),
                'label' => 'Nom',
                'required'    => true,
                'data' => $user['lname'],
            ))
            ->add('address', TextType::class, array(
                'constraints' => array(new NotBlank(), new Length(array('min' => 5))),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'Adresse', 'value' => $user['address']),
                'label' => 'Email',
                'required'    => true,
                'data' => $user['address'],

            ))
            ->add('city', TextType::class, array(
                'constraints' => array(new NotBlank()),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'Ville', 'value' => $user['city']),
                'label' => 'Ville',
                'required'    => true,
                'data' => $user['city'],
            ))
            ->add('zip', NumberType::class, array(
                'constraints' => array(new NotBlank(),new Length(array('max' => 5))),
                'attr' => array('class' => 'form-control input-lg','placeholder' => 'Code postal', 'value' => $user['zip']),
                'label' => 'Code postal',
                'required'    => true,
                'data' => $user['zip'],
            ))
            ->add('id', HiddenType::class, array(
//                'constraints' => array(new NotBlank(),new Length(array('max' => 5))),
//                'attr' => array('class' => 'form-control input-lg','placeholder' => 'Code postal', 'value' => $user['zip']),
//                'label' => 'Code postal',
//                'required'    => true,
                'data' => $user['id'],
            ))

            ->getForm();

        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {//Si le formulaire est ok on vérifie si les logins existent et sont ok
            $donnees = $form->getData();
            if(!($donnees['password'] == $donnees['motdepasse'])){
                $app['session']->getFlashBag()->add('notifications',
                    array('type' => 'warning', 'message' =>'Les mots de passe ne correspondent pas.'));
                // display the form
                return $app['twig']->render('user.edit.html.twig', array('form' => $form->createView()));
            }

            //$donnees['password'] = $app['security.encoder.digest']->encodePassword($donnees['password'], '');
            // do something with the data
            $this->userModel = new UserModel($app);
            $data=$this->userModel->updateUser($donnees);

            // redirect somewhere
            $app['session']->getFlashBag()->add('notifications',
                array('type' => 'success', 'message' =>'Modifications effectuées !'));
            return $app->redirect($app["url_generator"]->generate("user.edit"));
        }

        // display the form
        return $app['twig']->render('frontOff/User/edit.html.twig', array('form' => $form->createView()));


    }


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
        $controllers->get('/logincap', 'App\Controller\UserController::initcaptcha')->bind('user.initcaptcha');
		$controllers->get('/login', 'App\Controller\UserController::connexionUser')->bind('user.login');
		$controllers->post('/login', 'App\Controller\UserController::connexionUser')->bind('user.validFormlogin');
        $controllers->get('/compte', 'App\Controller\UserController::modifier')->bind('user.edit');
        $controllers->post('/compte', 'App\Controller\UserController::modifier')->bind('user.validModif');
		$controllers->get('/logout', 'App\Controller\UserController::deconnexionSession')->bind('user.logout');
        $controllers->get('/register', 'App\Controller\UserController::register')->bind('user.register');
        $controllers->post('/register', 'App\Controller\UserController::register')->bind('user.validInscription');


        return $controllers;
	}
}