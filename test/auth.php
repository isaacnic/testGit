<?php
    
    //****************CONNEXION BDD****************
    $PARAM_hote = 'localhost'; 
    $PARAM_nom_bd = 'td1'; 
    $PARAM_utilisateur = 'root'; 
    $PARAM_mot_passe = ''; 
    try {
        $pdo = new PDO('mysql:host='.$PARAM_hote.';dbname='.$PARAM_nom_bd, $PARAM_utilisateur, $PARAM_mot_passe);
		//echo '***Connexion a la base <strong>'. $PARAM_nom_bd .'</strong> reussie***<br/>';
    }
	catch(Exception $e) {
		echo 'Erreur : '.$e->getMessage().'<br />';
		die();
	}

 	//****************AUTHENTIFICATION**************
    if(!empty($_GET)) {
        if(isset($_GET["login"]) && isset($_GET['password'])) {
			$login = mysql_real_escape_string($_GET['login']);
			$password = mysql_real_escape_string($_GET['password']);
			$req = $pdo->prepare("SELECT * FROM utilisateur WHERE login = ?");
			$req->bindParam(1,$login,PDO::PARAM_STR);
			$req->execute();
			if($req->rowCount() == 1) { //Bon Login
				$user = $req->fetch(PDO::FETCH_OBJ);
				define('BANISHEMENT_DURATION',30);
				$timeGap = time()-strtotime($user->timeBanishement);
				if($timeGap > BANISHEMENT_DURATION) { //NON banni
					$req_1 = $pdo->prepare("UPDATE utilisateur SET nbFailsPwd = ? WHERE login = ?");
					$req_1->bindParam(2,$user->login,PDO::PARAM_STR);
					if(md5($password.$user->salt) == $user->password) { //Bon password
						$req_1->bindValue(1,0,PDO::PARAM_INT);
						if($user->nbFailsPwd != 0) $req_1->execute();
						//echo '***Authentifiation du user <strong>' . $login .'</strong> reussie***<br/>';
						$tab = array("error" => "false");
						echo json_encode($tab);
					}else { //Mauvais password
						$nbFailsPwdUpdate = $user->nbFailsPwd + 1;
						$req_1->bindValue(1,$nbFailsPwdUpdate,PDO::PARAM_INT);
						if($user->nbFailsPwd < 3) $req_1->execute();
						$req->execute();
						$user = $req->fetch(PDO::FETCH_OBJ);
						if($user->nbFailsPwd == 3) {
							$time = date('YmdHis');
							$pdo->exec('UPDATE utilisateur SET timeBanishement = '.$time.' WHERE login = \''.$user->login.'\'');
						}
						//echo '***Mot de passe incorrect pour le user <strong>'. $login .'</strong> : '.$user->nbFailsPwd.' echec(s)***<br/>';
						$tab = array("error" => "true");
						echo json_encode($tab);
					}
				}else { //Banni
					$timeLeft = BANISHEMENT_DURATION-$timeGap;
					echo '***Le user <strong>' . $login .'</strong>  est banni pour encore <strong>'.$timeLeft.'</strong>  secondes***<br/>';
				}		
			}else { //Mauvais Login
				echo '***User <strong>'. $login .'</strong> inexistant***<br/>';
			}
        }else {
            echo "***Renseigner login et password en GET***";
        }
    }else {
        echo "***Absence des parametres de login***";
    }   
    

?>