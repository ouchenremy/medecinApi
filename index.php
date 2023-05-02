<?php
session_start();

// Test de connexion à la base
$config = parse_ini_file("config.ini");
try {
	$pdo = new \PDO("mysql:host=".$config["host"].";dbname=".$config["database"].";charset=utf8", $config["user"], $config["password"]);
} catch(Exception $e) {
	http_response_code(500);
	header('Content-Type: application/json');
	header("Access-Control-Allow-Origin: *");
	echo '{ "message":"Erreur de connexion à la base de données" }';
	exit;
}

// Chargement des fichiers MVC
require("control/controleur.php");
require("view/vue.php");
require("model/patient.php");
require("model/rdv.php");
require("model/connexion.php");

// Routes et méthodes HTTP associées
if(isset($_GET["action"])) {
	switch($_GET["action"]) {
		case "patient":
			switch($_SERVER["REQUEST_METHOD"]) {
				case "POST":
					
					(new controleur)->ajouterPatient();
					break;
				default:
					(new controleur)->erreur404();
					break;
			}
			break;

		case "rdv":
			switch($_SERVER["REQUEST_METHOD"]) {
				case "GET":
					(new controleur)->getRdv();
					break;
				case "POST":
					(new controleur)->creationRdv();
					break;
				case "PUT":
					(new controleur)->modificationRdv();
					break;
				case "DELETE":
					(new controleur)->supprimerRdv();
					break;
				default:
					(new controleur)->erreur404();
					break;
			}
			break;
		
		
		 case "connexion":
			switch($_SERVER["REQUEST_METHOD"]) {
				case "POST":
					(new controleur)->connexionUser();
					break;
				default:
					(new controleur)->erreur404();
					break;
			}
			break;
		
		// Route par défaut : erreur 404
		default:
			(new controleur)->erreur404();
			break;
	}
}
else {
	// Pas d'action précisée = erreur 404
	(new controleur)->erreur404();
}