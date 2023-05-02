<?php

class rdv {
	
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
		$sql = "SELECT * FROM rdv";
		
		$req = $this->pdo->prepare($sql);
		$req->execute();
		
		return $req->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function getRdvMed($idM, $date) {
		$date = $date."%";
		$sql = "SELECT * FROM rdv WHERE idMedecin = :id AND dateHeureRdv LIKE :date";
		
		$req = $this->pdo->prepare($sql);
		$req->bindParam(':id', $idM, PDO::PARAM_STR);
		$req->bindParam(':date', $date, PDO::PARAM_STR);
		$req->execute();
		
		return $req->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function getRdv($idP) {
		$sql = "SELECT * FROM rdv WHERE idPatient = :id";
		
		$req = $this->pdo->prepare($sql);
		$req->bindParam(':id', $idP, PDO::PARAM_INT);
		$req->execute();
		
		return $req->fetch(\PDO::FETCH_ASSOC);
	}

	public function createRdv($laDateHeure, $lIdPatient, $lIdMedecin) {
		$sql = "INSERT INTO rdv (dateHeureRdv, idPatient, idMedecin) VALUES (:laDateHeure, :lIdPatient, :lIdMedecin)";
		
		$req = $this->pdo->prepare($sql);
		$req->bindParam(':laDateHeure', $laDateHeure, PDO::PARAM_STR);
		$req->bindParam(':lIdPatient', $lIdPatient, PDO::PARAM_INT);
		$req->bindParam(':lIdMedecin', $lIdMedecin, PDO::PARAM_STR);

		return $req->execute();

	}

	public function updateRdv($dateHeureAct, $laDateHeure, $lIdPatient, $lIdMedecin) {
		$sql = "UPDATE rdv SET dateHeureRdv = :laDateHeure WHERE dateHeureRdv = :dateHeureAct AND idMedecin = :leMedecin AND idPatient = :lePatient";
		
		$req = $this->pdo->prepare($sql);
		$req->bindParam(':laDateHeure', $laDateHeure, PDO::PARAM_STR);
		$req->bindParam(':dateHeureAct', $dateHeureAct, PDO::PARAM_STR);
		$req->bindParam(':leMedecin', $lIdMedecin, PDO::PARAM_STR);
		$req->bindParam(':lePatient', $lIdPatient, PDO::PARAM_INT);
		return $req->execute();
	}

	public function deleteRdv($id) {
		$sql = "DELETE FROM rdv WHERE idRdv = :id";
		
		$req = $this->pdo->prepare($sql);
		$req->bindParam(':id', $id, PDO::PARAM_INT);
		return $req->execute();
	}

	public function exists($date, $idM) {
		$sql = "SELECT COUNT(*) AS nb FROM rdv WHERE dateHeureRdv = :dateHeureRdv AND idMedecin = :medecin";
		
		$req = $this->pdo->prepare($sql);
		$req->bindParam(':dateHeureRdv', $date, PDO::PARAM_STR);
		$req->bindParam(':medecin', $idM, PDO::PARAM_STR);
		$req->execute();
		
		$nb = $req->fetch(\PDO::FETCH_ASSOC)["nb"];
		if($nb > 0) {
			return true;
		}
		else {
			return false;
		}
	}

	public function addCpt($id, $txt){
		$sql = "UPDATE rdv SET cptRendu = :txt WHERE idRdv = :id";
	
		$req = $this->pdo->prepare($sql);
		$req->bindParam(':id', $id, PDO::PARAM_INT);
		$req->bindParam(':txt', $txt, PDO::PARAM_STR);

		return $req->execute();
	}


	public function verifBan($id){

		$sql = "SELECT status FROM patient WHERE idPatient = :id";
		
		$req = $this->pdo->prepare($sql);
		$req->bindParam(':id', $id, PDO::PARAM_INT);
		$req->execute();
		
		$stat = $req->fetch(\PDO::FETCH_ASSOC)["status"];


		if($stat != 1){
			return false;
		}
		else{
			return true;
		}

	}
	
}