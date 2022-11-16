<?php 
// +------------------------------------------------------------------------+
// | @author        : Michael Arawole (Logad Networks)
// | @author_url    : https://logad.net
// | @author_email  : logadscripts@gmail.com
// | @date          : 27 Jan, 2022 06:56PM
// +------------------------------------------------------------------------+

// +----------------------------+
// | Core functions
// +----------------------------+

class app {
	public function __construct() {
		$this->generalErrorMessage = "Some error occurred";
	}

	// PDO connection
	protected function pdoConnect() {
		require 'config.php';
		try {
			$pdo = new PDO('mysql:host=' . $dbhost . ';dbname=' . $dbname . ';charset=utf8', $dbuser, $dbpass);
			return $pdo;
		} catch (PDOException $exception) {
			exit('Failed to connect to database!');
		}
	}

	// Clean string
	public function clean($string) {
		return htmlentities($string);
	}

	public function joinWaitList($data) {
		$response['status'] = false;
		$response['msg'] = $this->generalErrorMessage;

		// Validate email
		if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
		    $response['msg'] = "Invalid email format";
		    return $response;
		}
		// type must be investor or asset lister
		if ($data->type != "investor" && $data->type != "asset_lister") {
			$response['msg'] = "Type must only be Investor or Asset Lister";
			return $response;
		}
		// if asset lister, asset descripion is required
		if ($data->type == "asset_lister" && empty($data->asset_description)) {
			$response['msg'] = "Asset description is required";
			return $response;
		}


		$pdo = $this->pdoConnect();

		// check email
		$check = $pdo->prepare("SELECT id FROM waitlist WHERE email = ?");
		$check->execute([$data->email]);
		if ($check->rowCount() != 0) {
			$response['msg'] = "Email already exists";
			return $response;
		}

		if ($data->type == "investor") {
			$addType = "Investor";
		} else {
			$addType = "Asset Lister";
		}

		$date = time();
		$description = (!empty($data->asset_description)) ? $data->asset_description : null;
		$stmt = $pdo->prepare("INSERT INTO waitlist (name, email, type, asset_description, date) VALUES (?, ?, ?, ?, ?)");
		$stmt->execute([$data->fullName, $data->email, $data->type, $description, $date]);
		// print_r($check->rowCount());
		if ($stmt->rowCount() == 1) {
			$response['status'] = true;
			$response['msg'] = "You've been successfully added to the waitlist as an $addType";
		}

		return $response;
	}
}
