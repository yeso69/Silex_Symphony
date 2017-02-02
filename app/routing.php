<?php
//***************************************
// Montage des controleurs sur le routeur
$app->mount("/", new App\Controller\IndexController($app));
$app->mount("/produit", new App\Controller\ProduitController($app));
$app->mount("/panier", new App\Controller\PanierController($app));
$app->mount("/commande", new App\Controller\CommandeController($app));
$app->mount("/connexion", new App\Controller\UserController($app));
$app->mount("/admin/commande", new App\Controller\admin\CommandeController($app));
$app->mount("/admin/produit", new App\Controller\admin\ProduitController($app));
