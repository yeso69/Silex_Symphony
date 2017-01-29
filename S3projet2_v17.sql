DROP TABLE  IF EXISTS paniers,commandes, produits, users, typeProduits, etats;

-- --------------------------------------------------------
-- Structure de la table typeproduits
--
CREATE TABLE IF NOT EXISTS typeProduits (
  id int(10) NOT NULL,
  libelle varchar(50) DEFAULT NULL,
  PRIMARY KEY (id)
)  DEFAULT CHARSET=utf8;
-- Contenu de la table typeproduits
INSERT INTO typeProduits (id, libelle) VALUES
(1, 'type 1'),
(2, 'type 2'),
(3, 'type 3');

-- --------------------------------------------------------
-- Structure de la table etats

CREATE TABLE IF NOT EXISTS etats (
  id int(11) NOT NULL AUTO_INCREMENT,
  libelle varchar(20) NOT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 ;
-- Contenu de la table etats
INSERT INTO etats (id, libelle) VALUES
(1, 'A préparer'),
(2, 'Expédié');

-- --------------------------------------------------------
-- Structure de la table produits

CREATE TABLE IF NOT EXISTS produits (
  id int(10) NOT NULL AUTO_INCREMENT,
  typeProduit_id int(10) DEFAULT NULL,
  nom varchar(50) DEFAULT NULL,
  prix float(6,2) DEFAULT NULL,
  photo varchar(50) DEFAULT NULL,
  dispo tinyint(4) NOT NULL,
  stock int(11) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_produits_typeProduits FOREIGN KEY (typeProduit_id) REFERENCES typeProduits (id)
) DEFAULT CHARSET=utf8 ;

INSERT INTO produits (id,typeProduit_id,nom,prix,photo,dispo,stock) VALUES
(1,1, 'produit 1','100','imageProduit.jpeg',1,5),
(2,1, 'produit 2','5.5','imageProduit.jpeg',1,4),
(3,2, 'produit 3','8.5','imageProduit.jpeg',1,10),
(4,2, 'produit 4','8','imageProduit.jpeg',1,5),
(5,2, 'produit 5','55','imageProduit.jpeg',1,4),
(6,3, 'produit 6','5','imageProduit.jpeg',1,10);


-- --------------------------------------------------------
-- Structure de la table user
-- valide permet de rendre actif le compte (exemple controle par email )


# Structure de la table `utilisateur`
DROP TABLE IF EXISTS users;

# <http://silex.sensiolabs.org/doc/2.0/providers/security.html#defining-a-custom-user-provider>
# Contenu de la table `utilisateur`

CREATE TABLE users (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(100) NOT NULL DEFAULT '',
  password VARCHAR(255) NOT NULL DEFAULT '',
  motdepasse VARCHAR(255) NOT NULL DEFAULT '',
  roles VARCHAR(255) NOT NULL DEFAULT '',
  email  VARCHAR(255) NOT NULL DEFAULT '',
  isEnabled TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# mot de passe crypté avec security.encoder.bcrypt

INSERT INTO users (id,username,password,motdepasse,email,roles) VALUES
(1, 'admin', '$2y$13$mJK5hyDNAY9rcDuEBofjJ.h3d7xBwlApfMoknBDO0AvXLr1AaJM02', 'admin', 'admin@gmail.com','ROLE_ADMIN'),
(2, 'invite', '$2y$13$j5rdj5QL3fd.IZlA5JNbc.kTRaa1YbJK/G7h2mB51ySzaDdgEbo8W', 'invite', 'admin@gmail.com','ROLE_INVITE'),
(3, 'vendeur', '$2y$13$/gwC0Iv6ssewrr9JeUDDuOcRTWD.uIEjJpH1HUWPAxe.5EwY98OEO','vendeur', 'vendeur@gmail.com','ROLE_VENDEUR'),
(4, 'client', '$2y$13$bhuMlUWdfc5mAhVumuKUG.etahlJ399DEwuQPhbdXjiCdKIeX2nii', 'client', 'client@gmail.com','ROLE_CLIENT'),
(5, 'client2', '$2y$13$SYEM3Tk/5G.C85pIAm0cSOd8BFrFTEnLHBSWsW96Q3k9gCdFXRmvm','client2', 'client2@gmail.com','ROLE_CLIENT');



-- --------------------------------------------------------
-- Structure de la table commandes
CREATE TABLE IF NOT EXISTS commandes (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11)  UNSIGNED  NOT NULL,
  prix float(6,2) NOT NULL,
  date_achat  timestamp default CURRENT_TIMESTAMP,
  etat_id int(11) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_commandes_users FOREIGN KEY (user_id) REFERENCES users (id),
  CONSTRAINT fk_commandes_etats FOREIGN KEY (etat_id) REFERENCES etats (id)
) DEFAULT CHARSET=utf8 ;



-- --------------------------------------------------------
-- Structure de la table paniers
CREATE TABLE IF NOT EXISTS paniers (
  id int(11) NOT NULL AUTO_INCREMENT,
  quantite int(11) NOT NULL,
  prix float(6,2) NOT NULL,
  dateAjoutPanier timestamp default CURRENT_TIMESTAMP,
  user_id int(11)  UNSIGNED  NOT NULL,
  produit_id int(11) NOT NULL,
  commande_id int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_paniers_users FOREIGN KEY (user_id) REFERENCES users (id),
  CONSTRAINT fk_paniers_produits FOREIGN KEY (produit_id) REFERENCES produits (id),
  CONSTRAINT fk_paniers_commandes FOREIGN KEY (commande_id) REFERENCES commandes (id)
) DEFAULT CHARSET=utf8 ;

