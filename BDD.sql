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
(1, 'Maillot'),
(2, 'Ballon'),
(3, 'Chaussures'),
(4, 'Autres');

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


INSERT INTO `produits` (`id`, `typeProduit_id`, `nom`, `prix`, `photo`, `dispo`, `stock`)
VALUES ('1', '1', 'Maillot de l\'équipe de France Domicile', '65', 'maillot_edf.jpg', '12', '50'),
 ('2', '1', 'Maillot de l\'équipe de France Exterieur', '65', 'maillot_edf_ext.jpg', '28', '65'),
 ('3', '2', 'Ballon de handball Kempa', '22', 'Balon_kempa.jpg', '23', '40'),
 ('4', '2', 'Ballon de handball Hummel', '27', 'ballon_hummel.jpg', '17', '28'),
 ('5', '2', 'Ballon de handball Kempa', '35', 'Ballon_molten.jpg', '14', '20'),
 ('6', '3', 'Chausure handball Hummel', '55', 'hummel_choose.jpg', '13', '19'),
 ('7', '3', 'Chausure handball Kempa', '52', 'kempa_choose.jpg', '19', '36'),
 ('8', '3', 'Chausure handball Addidas', '79', 'addidas_choose.jpg', '45', '96'),
 ('9', '4', 'Genouillères', '12', 'genouillères.jpg', '45', '78'),
 ('10', '4', 'Resine', '18', 'resine.jpg', '15', '19');


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
  password VARCHAR(255) NOT NULL,
  roles VARCHAR(255) NOT NULL DEFAULT 'ROLE_INVITE',
  email  VARCHAR(255) NOT NULL,
  lname VARCHAR(100) NOT NULL ,
  fname VARCHAR(100) NOT NULL,
  city VARCHAR(100) NOT NULL,
  address VARCHAR(100) NOT NULL,
  zip VARCHAR(5) NOT NULL,
  isEnabled TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO users (id,username,password,roles,email,lname,fname,city,address,zip) VALUES
(1, 'admin', 'admin','ROLE_ADMIN','admin@gmail.com', 'admin', 'admin', 'Lyon', '14 Avenue Saint-Exupéry', '69007'),
(2, 'invite', 'invite','ROLE_INVITE','invite@gmail.com', 'invite', 'invite', 'Lyon', '14 Rue 42', '69000'),
(3, 'vendeur', 'vendeur','ROLE_VENDEUR','vendeur@gmail.com', 'vendeur', 'vendeur', 'Belfort', '14 Avenue Jean Jaurès', '90000'),
(4, 'client','client','ROLE_CLIENT','client@gmail.com', 'client', 'client', 'Paris', '1 Avenue des Champs-Elysées', '75000');



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

INSERT INTO commandes (user_id,prix,etat_id) VALUES
(1, 20, 1),
(2, 50, 2),
(3, 100, 1),
(4, 500, 1),
(5, 20, 1);



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


INSERT INTO paniers (quantite,prix,user_id,produit_id,commande_id) VALUES
(2, 20, 1, 1, 1),
(1, 12, 2, 1, 1),
(4, 14, 3, 1, 1),
(6, 16, 4, 1, 1),
(3, 52, 5, 1, 1),
(1, 36, 5, 1, 1);