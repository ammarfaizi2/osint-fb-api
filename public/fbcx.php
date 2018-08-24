<?php

require __DIR__."/../vendor/autoload.php";
require __DIR__."/../credential.tmp";

use Fphp\Fphp;
use Fphp\Exceptions\FphpException;

try {
	$data = [
		"user_info" => [
			"name" => null,
			"profile_picture" => null
		],
		"user_posts" => []
	];

	$fb = new Fphp($email, $pass, $cookieFile);
	// $login = $fb->login();

	// switch ($login) {
	// 	case Fphp::LOGIN_SUCCESS:
	// 		// echo "Login success!\n";
	// 		break;
	// 	case Fphp::LOGIN_FAILED:
	// 		echo "Login failed\n";
	// 	case Fphp::LOGIN_CHECKPOINT:
	// 		echo "Checkpoint!\n";
	// 	default:
	// 		exit(1);
	// 		break;
	// }

	// $out = $fb->go("https://mobile.facebook.com/slankers.ketinggalanjaman?v=timeline", [CURLOPT_FOLLOWLOCATION => true]);
	// $out["out"] = gzdecode($out["out"]);

	$out["out"] = file_get_contents("a.tmp");
	
	if (preg_match(
		"/(?:<title>)(.*)(?:<\/title>)/Usi",
		$out["out"],
		$m
	)) {
		$data["user_info"]["name"] = trim(fe($m[1]));
	}

	if (preg_match_all(
		"/(?:<table class=\"ba\" role=\"presentation\">)(.+)(?:<abbr>)/Usi",
		$out["out"],
		$m
	)) {
		foreach ($m[1] as $mv) {
			$mv = explode("<table class=\"ba\" role=\"presentation\">", $mv);
			if (count($mv) > 1) {
				$mv = end($mv);
			} else {
				$mv = $mv[0];
			}

			if (preg_match(
				"/<h3 class=\".. .. .. ..\">(.*)<\/h3>/Usi",
				$mv,
				$h3
			)) {
				$h3 = fe(strip_tags($h3[1]));
				if (preg_match(
					"/<img.+>/Usi",
					$mv,
					$mc
				)) {
					$alt = $photoUrl = $description = null;

					if (preg_match(
						"/(?:src=\")(.*)(?:\")/Usi",
						$mc[0],
						$tmp
					)) {
						$photoUrl = fe($tmp[1]);
					}

					if (preg_match(
						"/(?:alt=\")(.*)(?:\")/Usi",
						$mc[0],
						$tmp
					)) {
						$alt = fe($tmp[1]);
					}

					$data["user_posts"][] = [
						"type" => "photo",
						"info" => $h3,
						"photo_url" => $photoUrl,
						"description" => "",
						"alt_predict" => $alt
					];
				}
			}
			
		}
	}

	// if (preg_match(
	// 	"/(?: width=\"320\" height=\"200\".+<a href=\")(.*)(?:\")/Usi",
	// 	$out["out"],
	// 	$m
	// )) {
	// 	$photoUrl = fe($m[1]);
	// 	$out = $fb->go("https://mobile.facebook.com/{$photoUrl}");
	// 	$out["out"] = gzdecode($out["out"]);

	// 	if (preg_match(
	// 		"/(?:src=\")([^\"]+scontent[^\"]+)(?:\")/Usi",
	// 		$out["out"],
	// 		$m
	// 	)) {
	// 		$data["user_info"]["profile_picture"] = trim(fe($m[1]));
	// 	}
	// }

	print json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (FphpException $e) {
	echo "Error: ". $e->getMessage()."\n";
	exit(1);
}
