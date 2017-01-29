INSERT INTO `produits` (`id`, `typeProduit_id`, `nom`, `prix`, `photo`, `dispo`, `stock`)
VALUES ('1', '1', 'Maillot de l\'équipe de France Domicile', '65', 'maillot_edf.jpg', '12', '50'),
 ('2', '1', 'Maillot de l\'équipe de France Exterieur', '65', 'maillot_edf_ext.jpg', '28', '65'),
 ('3', '2', 'Ballon de handball Kempa', '22', 'Balon_kempa.jpg', '23', '40'),
 ('4', '2', 'Ballon de handball Hummel', '27', 'ballon_hummel.jpg', '17', '28'),
 ('5', '2', 'Ballon de handball Kempa', '35', 'Ballon_molten.jpg', '14', '20'),
 ('6', '3', 'Chausure handball Hummel', '55', 'hummel_choose.jpg', '13', '19'),
 ('7', '3', 'Chausure handball Kempa', '52', 'kempa_choose.jpg', '19', '36'),
 ('8', '3', 'Chausure handball Addidas', '79', 'addidas_choose.jpg', '45', '96');
UPDATE `typeproduits` SET `libelle` = 'Maillot' WHERE `typeproduits`.`id` = 1;
UPDATE `typeproduits` SET `libelle` = 'Ballon' WHERE `typeproduits`.`id` = 2;
UPDATE `typeproduits` SET `libelle` = 'Chaussures' WHERE `typeproduits`.`id` = 3;
INSERT INTO `typeproduits` (`id`, `libelle`) VALUES ('4', 'Autres');
INSERT INTO `produits` (`id`, `typeProduit_id`, `nom`, `prix`, `photo`, `dispo`, `stock`)
VALUES ('9', '4', 'Genouillères', '12', 'genouillères.jpg', '45', '78'),
 ('10', '4', 'Resine', '18', 'resine.jpg', '15', '19');
