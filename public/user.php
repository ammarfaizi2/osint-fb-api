<?php

ini_set("display_errors", true);

require __DIR__."/../vendor/autoload.php";
require __DIR__."/../credential.tmp";

use Fphp\Fphp;
use Fphp\Exceptions\FphpException;

header("Content-Type: application/json");

if (! isset($_GET["user"])) {
	print json_encode(["error" => "\"user\" parameter must be provided!"]);
	exit(1);
}

if (! is_string($_GET["user"])) {
	print json_encode(["error" => "\"user\" parameter must be a string!"]);
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
	$user = $_GET["user"];

	$fb = new Fphp($email, $pass, $cookieFile);
	$login = $fb->login();

	switch ($login) {
		case Fphp::LOGIN_SUCCESS:
			//echo "Login success!\n";
			break;
		case Fphp::LOGIN_FAILED:
			exit([
				"status" => "error",
				"error_message" => "Login Failed"
			]);
		case Fphp::LOGIN_CHECKPOINT:
			exit([
				"status" => "error",
				"error_message" => "Login checkpoint"
			]);
		default:
			exit([
				"status" => "error", 
				"error_message" => 'Unknown error'
			]);
			break;
	}

	$userInfo = $fb->utils->getUserInfo($user);

	print json_encode(
		[
			"user_info" => $userInfo
		],
		JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
	);

} catch (FphpException $e) {
	print json_encode(
		[
			"status"  => "error",
			"error_message" => $e->getMessage()
		],
		JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
	);
}
