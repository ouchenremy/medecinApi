<?php

class connexion {
	
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

	public function log($login, $mdp) {
		$sql = "SELECT idPatient, mdpPatient FROM patient WHERE loginPatient = :logi";
		
		$req = $this->pdo->prepare($sql);
        $req->bindParam(':logi', $login, PDO::PARAM_STR);
		$req->execute();

        $res = $req->fetch();
        $verification = password_verify($mdp, $res["mdpPatient"]);

        if($verification === true){
            $token = bin2hex(random_bytes(30));
            $ip = $_SERVER["REMOTE_ADDR"];

            $sql2 = "INSERT INTO authentification(token, idPatient, ipAppareil) VALUES (:tok, :id, :ip)";
    
            $req2 = $this->pdo->prepare($sql2);
            $req2->bindParam(':tok', $token, PDO::PARAM_STR);
            $req2->bindParam(':id', $res["idPatient"], PDO::PARAM_INT);
            $req2->bindParam(':ip', $ip, PDO::PARAM_STR);
            $req2->execute();

            return $token;
        }

        else{
            return null;
        }
	}

    public function convertToken($leToken){
		$sql = "SELECT idPatient FROM authentification WHERE token = :letoken";
		
		$req = $this->pdo->prepare($sql);
		$req->bindParam(':letoken', $leToken, PDO::PARAM_STR);
		$req->execute();
		
		$id = $req->fetch(\PDO::FETCH_ASSOC)["idPatient"];
		return $id;
	}

    public function verifToken($leToken){
		$sql = "SELECT COUNT(*) AS nb FROM authentification WHERE token = :letoken";
		
		$req = $this->pdo->prepare($sql);
		$req->bindParam(':letoken', $leToken, PDO::PARAM_STR);
		$req->execute();
		
		$nb = $req->fetch(\PDO::FETCH_ASSOC)["nb"];
		return $nb;
	}

    public function verifIp($leToken){
        $ip = $_SERVER["REMOTE_ADDR"];
		$sql = "SELECT COUNT(*) AS nb FROM authentification WHERE token = :letoken AND ipAppareil = :ip";
		
		$req = $this->pdo->prepare($sql);
		$req->bindParam(':letoken', $leToken, PDO::PARAM_STR);
        $req->bindParam(':ip', $ip, PDO::PARAM_STR);
		$req->execute();
		
		$nb = $req->fetch(\PDO::FETCH_ASSOC)["nb"];
		return $nb;
	}
}