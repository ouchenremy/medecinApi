<?php

class patient {
	// Objet PDO servant à la connexion à la base
	private $pdo;

	// Connexion à la base de données
	public function __construct() {
		$config = parse_ini_file("config.ini");
		
		try {
			$this->pdo = new \PDO("mysql:host=".$config["host"].";dbname=".$config["database"].";charset=utf8", $config["user"], $config["password"]);
		} catch(Exception $e) {
			echo $e->getMessage();
		}
	}
	
	public function getAll() {
		$sql = "SELECT * FROM patient";
		
		$req = $this->pdo->prepare($sql);
		$req->execute();
		
		return $req->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function get($id) {
		$sql = "SELECT * FROM patient WHERE idPatient = :id";
		
		$req = $this->pdo->prepare($sql);
		$req->bindParam(':id', $id, PDO::PARAM_INT);
		$req->execute();
		
		return $req->fetch(\PDO::FETCH_ASSOC);
	}

	public function inscriptionPatient($leNom, $lePnom, $laRue, $leCp, $laVille, $leTel, $leLogin, $leMdp) {
		$sql = "INSERT INTO patient (nomPatient, prenomPatient, ruePatient, cpPatient, villePatient, telPatient, loginPatient, mdpPatient) 
				VALUES (:leNom, :lePnom, :laRue, :leCp, :laVille, :leTel, :leLogin, :leMdp)";

		$hash = password_hash($leMdp, PASSWORD_BCRYPT);
		
		$req = $this->pdo->prepare($sql);
		$req->bindParam(':leNom', $leNom, PDO::PARAM_STR);
		$req->bindParam(':lePnom', $lePnom, PDO::PARAM_STR);
		$req->bindParam(':laRue', $laRue, PDO::PARAM_STR);
		$req->bindParam(':leCp', $leCp, PDO::PARAM_STR);
		$req->bindParam(':laVille', $laVille, PDO::PARAM_STR);
		$req->bindParam(':leTel', $leTel, PDO::PARAM_STR);
		$req->bindParam(':leLogin', $leLogin, PDO::PARAM_STR);
		$req->bindParam(':leMdp', $hash, PDO::PARAM_STR);

		if (preg_match('/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$ %^&*-]).{13,}$/', $leMdp)){
			return $req->execute();
		}

		else{
			return false;
		}
	}

	public function exists($login) {
		$sql = "SELECT COUNT(*) AS nombreCount FROM patient WHERE loginPatient = :logi";
		
		$req = $this->pdo->prepare($sql);
		$req->bindParam(':logi', $login, PDO::PARAM_STR);

		$req->execute();
		
		$nb = $req->fetch(\PDO::FETCH_ASSOC)["nombreCount"];
		if($nb == 1) {
			return true;
		}
		else {
			return false;
		}
	}

	public function nonVenu($id){
		$sql = "UPDATE rdv SET manque = 1 WHERE idRdv = :id" ;
	
		$req = $this->pdo->prepare($sql);
		$req->bindParam(':id', $id, PDO::PARAM_INT);

		$req->execute();



		$sql2 = "SELECT rdvManque FROM patient WHERE idPatient = (SELECT idPatient FROM rdv WHERE idRdv = :id)" ;
	
		$req2 = $this->pdo->prepare($sql2);
		$req2->bindParam(':id', $id, PDO::PARAM_INT);

		$req2->execute();

		$res = $req2->fetchAll(\PDO::FETCH_ASSOC);

		if($res[0]["rdvManque"] == 1){
			$sql3 = "UPDATE patient SET status = 1 WHERE idPatient = (SELECT idPatient FROM rdv WHERE idRdv = :id)";
	
			$req3 = $this->pdo->prepare($sql3);
			$req3->bindParam(':id', $id, PDO::PARAM_INT);

			return $req3->execute();
		}

		else{
			$sql3 = "UPDATE patient SET rdvManque = 1 WHERE idPatient = (SELECT idPatient FROM rdv WHERE idRdv = :id)";
	
			$req3 = $this->pdo->prepare($sql3);
			$req3->bindParam(':id', $id, PDO::PARAM_INT);

			return $req3->execute();
		}
	}

	
}