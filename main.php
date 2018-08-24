<?php

require __DIR__."/vendor/autoload.php";
require __DIR__."/credential.tmp";

use Fphp\Fphp;
use Fphp\Exceptions\FphpException;

try {
	$fb = new Fphp($email, $pass, $cookieFile);
	$login = $fb->login();

	switch ($login) {
		case Fphp::LOGIN_SUCCESS:
			echo "Login success!\n";
			break;
		case Fphp::LOGIN_FAILED:
			echo "Login failed\n";
		case Fphp::LOGIN_CHECKPOINT:
			echo "Checkpoint!\n";
		default:
			exit(1);
			break;
	}

	$this->go("https://mobile.facebook.com/peterjkambey");
	
} catch (FphpException $e) {
	echo "Error: ". $e->getMessage()."\n";
	exit(1);
}
