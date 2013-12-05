<?php
    
	//****************CONNEXION BDD****************
	$PARAM_hote = 'localhost'; 
    $PARAM_nom_bd = 'td1'; 
    $PARAM_utilisateur = 'root'; 
    $PARAM_mot_passe = ''; 
    try {
        $pdo = new PDO('mysql:host='.$PARAM_hote, $PARAM_utilisateur, $PARAM_mot_passe);
		echo '***Connexion a localhost reussie***<br/>';
    }
	catch(Exception $e) {
		echo 'Erreur : '.$e->getMessage().'<br />';
		die();
	}

		
	//*****************INITIALISATION BDD**************
	$pdo->exec("		
	CREATE DATABASE `td1` ;
	USE `td1`;
	DROP TABLE IF EXISTS `utilisateur`;
	CREATE TABLE IF NOT EXISTS `utilisateur` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `login` varchar(50) NOT NULL,
	  `password` varchar(50) NOT NULL,
	  `salt` varchar(50) NOT NULL,
	  `nbFailsPwd` int(11) NOT NULL,
	  `timeBanishement` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  PRIMARY KEY (`id`)
	)");
	echo '***Creation BDD et tables reussie***<br/>';
	
	
	//*****************REMPLISSAGE BDD*******************
	function salt() {
		return substr(str_shuffle(implode(array_merge(range(0,9),range('A','Z'),range('a','z')))),0, 10);
	}
	define('PWD_TRAN','pass1');
	define('PWD_ISAAC','pass2');
	$salt_tran = salt();
	$salt_isaac = salt();
	$password_tran = md5(PWD_TRAN.$salt_tran);
	$password_isaac = md5(PWD_ISAAC.$salt_isaac);
	$pdo->exec("
	INSERT INTO `utilisateur` (`login`, `password`, `salt`) VALUES
	('tran', '$password_tran', '$salt_tran'),
	('isaac', '$password_isaac', '$salt_isaac')");
	echo '***Insertion des donnees reussie***<br/>';
	
?>