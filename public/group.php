<?php

require __DIR__."/../vendor/autoload.php";
require __DIR__."/../credential.tmp";

use Fphp\Fphp;
use Fphp\Exceptions\FphpException;

header("Content-Type: application/json");

if (! isset($_GET["group"])) {
	print json_encode(["error" => "\"group\" parameter must be provided!"]);
	exit(1);
}

if (! is_string($_GET["group"])) {
	print json_encode(["error" => "\"group\" parameter must be a string!"]);
	exit(1);
}

if (isset($_GET["end_page"])) {
	if (! is_numeric($_GET["end_page"]) && 0 > $_GET["endpage"]) {
		print json_encode(["error" => "\"end_page\" parameter must be a number and not less than zero!"]);
		exit(1);
	}
	$endpage = (int)$_GET["end_page"];
} else {
	$endpage = 3;
}

try {
	$group_ = $_GET["group"];

	$fb = new Fphp($email, $pass, $cookieFile);
	$login = $fb->login();

	switch ($login) {
		case Fphp::LOGIN_SUCCESS:
			// echo "Login success!\n";
			break;
		case Fphp::LOGIN_FAILED:
			echo "Login failed\n";
		case Fphp::LOGIN_CHECKPOINT:
			echo "Checkpoint!\n";
		default:
			exit(1);
			break;
	}


} catch (FphpException $e) {
	print json_encode(
		[
			"error" => $e->getMessage()
		],
		 JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
	);
	exit(1);
}
