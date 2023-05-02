<?php
class controleur {
	
	public function erreur404() {
		http_response_code(404);
		(new vue)->erreur404();
	}

	public function verifierAttributsJson($objetJson, $listeDesAttributs) {
		$verifier = true;
		foreach($listeDesAttributs as $unAttribut) {
			if(!isset($objetJson->$unAttribut)) {
				$verifier = false;
			}
		}
		return $verifier;
	}


	//Ajout patient


	public function ajouterPatient() {
		$donnees = json_decode(file_get_contents("php://input"));		
		$renvoi = null;
		if($donnees === null) {
			http_response_code(400);
			$renvoi = array("message" => "JSON envoyé incorrect");
		}
		else{ //Ajout de compte rendu et vérification du patient venu ou non

			if(isset($donnees->estVenu)){
				if(!$donnees->estVenu){
					$attributsRequis = array("estVenu", "idR", "cptRendu");

					if($this->verifierAttributsJson($donnees, $attributsRequis)) {
						$renvoi = (new patient)->nonVenu($donnees->idR);
						(new rdv)->addCpt($donnees->idR, $donnees->cptRendu);
					}
				}
				else{
					$attributsRequis = array("estVenu", "idR", "cptRendu");

					if($this->verifierAttributsJson($donnees, $attributsRequis)) {
						$renvoi = (new rdv)->addCpt($donnees->idR, $donnees->cptRendu);
					}
				}
				
			}

			else{
				$attributsRequis = array("nom", "prenom", "rue", "cp", "ville", "tel", "login", "mdp");

				if($this->verifierAttributsJson($donnees, $attributsRequis)) {

					if((new patient)->exists($donnees->login)) {
						http_response_code(400);
						$renvoi = array("message" => "Login déjà utilisé");
						
					}

					else {
						$resultat = (new patient)->inscriptionPatient($donnees->nom, $donnees->prenom, $donnees->rue, $donnees->cp, $donnees->ville, $donnees->tel, $donnees->login, $donnees->mdp);
						
						if($resultat != false) {
							http_response_code(201);
							$renvoi = array("message" => "Ajout effectué avec succès", "idpatient" => $resultat);
							$co = (new connexion)->log($donnees->login, $donnees->mdp);

							if($co != null){
								http_response_code(201);
								$renvoi = array("message" => "Connexion établie", "token" => $co);
							}
						}
						else {
							http_response_code(500);
							$renvoi = array("message" => "Une erreur interne est survenue");
						}
					}
				}
			}
		}
		(new vue)->transformerJson($renvoi);
	}


	//Creation Rdv

	public function creationRdv() {
		$donnees = json_decode(file_get_contents("php://input"));
		$renvoi = null;
		if($donnees === null) {
			http_response_code(400);
			$renvoi = array("message" => "JSON envoyé incorrect");
		}
		else {
			$attributsRequis = array("date", "token", "idMedecin");
			$verifTok = (new connexion)->verifToken($donnees->token);
			//$verifIp = (new connexion)->verifIp($donnees->token);
			if ($verifTok == 1 /*and $verifIp == 1*/){
				$id = (new connexion)->convertToken($donnees->token);

				if($this->verifierAttributsJson($donnees, $attributsRequis)) {

					if((new rdv)-> verifBan($id) == false){

						if((new rdv)->exists($donnees->date, $donnees->idMedecin, $id)) {
							http_response_code(400);
							$renvoi = array("message" => "Impossible de prendre le rdv");
						}
						else {

							$resultat = (new rdv)->createRdv($donnees->date, $id, $donnees->idMedecin);
							if($resultat != false) {
								http_response_code(201);
								$renvoi = array("message" => "Ajout effectué avec succès", "token" => $resultat);
							}
							else {
								http_response_code(500);
								$renvoi = array("message" => "Une erreur interne est survenue");
							}
						}
					}
					else{
						
						$renvoi = array("message" => "Trop de rendez-vous ont étés manqués");
					}
				}
				else {
					http_response_code(400);
					$renvoi = array("message" => "Données manquantes");
				}
			}
			else {
				http_response_code(400);
				$renvoi = array("message" => "Le token spécifié ou l'id de votre la machine n'existe pas");
			}
				
		}

		(new vue)->transformerJson($renvoi);
	}


	//Modifictations Rdv

	public function modificationRdv() {
		$donnees = json_decode(file_get_contents("php://input"));
		$renvoi = null;
		if($donnees === null) {
			http_response_code(400);
			$renvoi = array("message" => "JSON envoyé incorrect");
		}
		else {
			$attributsRequis = array("dateAct", "token", "idMedecin", "newDate");
			$verifTok = (new connexion)->verifToken($donnees->token);
			$verifIp = (new connexion)->verifIp($donnees->token);
			if ($verifTok == 1 and $verifIp == 1){
				$id = (new connexion)->convertToken($donnees->token);
				if($this->verifierAttributsJson($donnees, $attributsRequis)) {
					if((new rdv)->exists($donnees->dateAct, $donnees->idMedecin)) {
						$resultat = (new rdv)->updateRdv($donnees->dateAct, $donnees->newDate, $id, $donnees->idMedecin);
						
						if($resultat != false) {
							http_response_code(200);
							$renvoi = array("message" => "Modification effectuée avec succès");
						}
						else {
							http_response_code(500);
							$renvoi = array("message" => "Une erreur interne est survenue");
						}
					}
					else {
						http_response_code(400);
						$renvoi = array("message" => "Le rdv spécifié n'existe pas");
					}
				}
				else {
					http_response_code(400);
					$renvoi = array("message" => "Données manquantes");
				}
			}
			else {
				http_response_code(400);
				$renvoi = array("message" => "Le token spécifié ou l'id de votre la machine n'existe pas");
			}
		}

		(new vue)->transformerJson($renvoi);
	}


	//Suppression rdv


	public function supprimerRdv() {
		$donnees = json_decode(file_get_contents("php://input"));
		$renvoi = null;
		if($donnees === null) {
			http_response_code(400);
			$renvoi = array("message" => "JSON envoyé incorrect");
		}
		else {
			$attributsRequis = array("date", "token", "idMedecin");
			$verifTok = (new connexion)->verifToken($donnees->token);
			$verifIp = (new connexion)->verifIp($donnees->token);
			if ($verifTok == 1 and $verifIp == 1){
				$id = (new connexion)->convertToken($donnees->token);
		
				if($this->verifierAttributsJson($donnees, $attributsRequis)) {
					if((new rdv)->exists($donnees->date, $donnees->idMedecin)) {
						$resultat = (new rdv)->deleteRdv($donnees->date, $id, $donnees->idMedecin);
						
						if($resultat != false) {
							http_response_code(200);
							$renvoi = array("message" => "Suppression effectuée avec succès");
						}
						else {
							http_response_code(500);
							$renvoi = array("message" => "Une erreur interne est survenue");
						}
					}
					else {
						http_response_code(400);
						$renvoi = array("message" => "Le rdv spécifié n'existe pas");
					}
				}
				else {
					http_response_code(400);
					$renvoi = array("message" => "Données manquantes");
				}
			}
			else {
				http_response_code(400);
				$renvoi = array("message" => "Le token spécifié ou l'id de votre la machine n'existe pas");
			}
		}
	
		(new vue)->transformerJson($renvoi);
	}

	//Affichage rendez-vous

	public function getRdv() {
		$donnees = null;
		if(isset($_GET["tok"])) {
			$verifTok = (new connexion)->verifToken($_GET["tok"]);
			$verifIp = (new connexion)->verifIp($_GET["tok"]);
			if ($verifTok == 1 && $verifIp == 1){
				$id = (new connexion)->convertToken($_GET["tok"]);
				http_response_code(200);
				$donnees = (new rdv)->getRdv($id);
			}
			else {
				http_response_code(400);
				$donnees = array("message" => "Le token spécifié ou l'ip de votre la machine n'existe pas");
			}
		}

		else if(isset($_GET["idMed"])) { //Récupération de tout les rdv d'un medecin à une date
			http_response_code(200);
			$donnees=(new rdv)->getRdvMed($_GET["idMed"], $_GET["date"]);
		}

		else{
			http_response_code(200);
			$donnees=(new rdv)->getAll();
		}
		(new vue)->transformerJson($donnees);
	}


	//Connexion


	public function connexionUser(){

		$donnees = json_decode(file_get_contents("php://input"));
		$renvoi = null;
		if($donnees === null) {
			http_response_code(400);
			$renvoi = array("message" => "JSON envoyé incorrect");
		}
		else {
			$attributsRequis = array("login", "mdp");
	
			if($this->verifierAttributsJson($donnees, $attributsRequis)) {
				$resultat = (new connexion)->log($donnees->login, $donnees->mdp);
				
				if($resultat != null) {
					http_response_code(200);
					$renvoi = array("message" => "Connexion accéptée !", "token"=>$resultat);
				}
				else {
					http_response_code(500);
					$renvoi = array("message" => "Une erreur interne est survenue");
				}
			}
			else {
				http_response_code(400);
				$renvoi = array("message" => "Données manquantes");
			}
		}
	
		(new vue)->transformerJson($renvoi);


	}
}