<?php

require __DIR__."/../vendor/autoload.php";
require __DIR__."/../credential.tmp";

use Fphp\Fphp;
use Fphp\Exceptions\FphpException;

try {
	$user_ = "ammarfaizi2";

	$data = [
		"user_info" => [
			"name" => null,
			"profile_picture" => null,
			"profile_url" => null
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

	$out = $fb->go("https://mobile.facebook.com/{$user_}?v=timeline", [CURLOPT_FOLLOWLOCATION => true]);
	$out["out"] = gzdecode($out["out"]);
	$url = explode("?", $out["info"]["url"], 2);
	$url = str_replace(["https://mobile.", "https://m."], "https://www.", $url[0]);
	$data["user_info"]["profile_url"] = $url;
	// print $out["out"];die;
	// $out["out"] = file_get_contents("a.tmp");
	
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
		foreach ($m[1] as $k => $mv) {

			if ($k === 0) {
				$mv = explode("<table class=\"ba\" role=\"presentation\">", $mv);
				if (count($mv) > 1) {
					$mv = end($mv);
				} else {
					$mv = $mv[0];
				}
			}

			if (preg_match(
				"/<h3 class=\".. .. .. ..\">(.*)<\/h3>/Usi",
				$mv,
				$h3
			)) {

				$h3 = fe(strip_tags($h3[1]));

				if (preg_match(
					"/href=\"\/video_redirect/", 
					$mv
				) && preg_match(
					"/<img.+>/Usi",
					$mv,
					$mc
				)) {
					$fbid = $alt = $photoUrl = $caption = null;

					if (preg_match(
						"/(?:src=\")(.*)(?:\")/Usi",
						$mc[0],
						$tmp
					)) {
						$photoUrl = fe($tmp[1]);
					}

					if (preg_match(
						"/(?:<span>[^<>]+<p>)(.+)(?:<\/p>)/Usi",
						$mv,
						$tmp
					)) {
						$caption = trim(fe(strip_tags(str_replace(["<wbr/>", "<br/>"], "\n", $tmp[1]))));
					}

					if (preg_match(
						"/(?:alt=\")(.*)(?:\")/Usi",
						$mc[0],
						$tmp
					)) {
						$alt = fe($tmp[1]);
					}

					if (preg_match(
						"/(?:story_fbid=)(.+)(?:&)/Usi",
						$mv,
						$tmp
					)) {
						$fbid = trim(fe($tmp[1]));
					} elseif (preg_match(
						"/(?:story_fbid\.)(\d+)(?:%)/Usi",
						$mv,
						$tmp
					)) {
						$fbid = trim(fe($tmp[1]));
					}

					$data["user_posts"][] = [
						"post_url" => (isset($fbid) ? "https://www.facebook.com/{$fbid}" : null),
						"story_fbid" => $fbid,
						"type" => "video",
						"title" => $h3,
						"photo_url" => $photoUrl,
						"caption" => $caption,
						"alt_predict" => $alt
					];
				} elseif (preg_match(
					"/<img.+>/Usi",
					$mv,
					$mc
				)) {
					$fbid = $alt = $photoUrl = $caption = null;

					if (preg_match(
						"/(?:src=\")(.*)(?:\")/Usi",
						$mc[0],
						$tmp
					)) {
						$photoUrl = fe($tmp[1]);
					}

					if (preg_match(
						"/(?:<span>[^<>]+<p>)(.+)(?:<\/p>)/Usi",
						$mv,
						$tmp
					)) {
						$caption = trim(fe(strip_tags(str_replace(["<wbr/>", "<br/>"], "\n", $tmp[1]))));
					}

					if (preg_match(
						"/(?:alt=\")(.*)(?:\")/Usi",
						$mc[0],
						$tmp
					)) {
						$alt = fe($tmp[1]);
					}

					if (preg_match(
						"/(?:story_fbid=)(.+)(?:&)/Usi",
						$mv,
						$tmp
					)) {
						$fbid = trim(fe($tmp[1]));
					} elseif (preg_match(
						"/(?:story_fbid\.)(\d+)(?:%)/Usi",
						$mv,
						$tmp
					)) {
						$fbid = trim(fe($tmp[1]));
					}

					$data["user_posts"][] = [
						"post_url" => (isset($fbid) ? "https://www.facebook.com/{$fbid}" : null),
						"story_fbid" => $fbid,
						"type" => "photo",
						"title" => $h3,
						"photo_url" => $photoUrl,
						"caption" => $caption,
						"alt_predict" => $alt
					];
				} elseif (preg_match(
					"/(?:<span>([^<>]+)?<p>)(.+)(?:<\/p>)/si",
					$mv,
					$tmp
				)) {
					$fbid = null;

					$tmp = explode("\n", trim(fe(strip_tags(str_replace(["<wbr/>", "<br/>", "<br />", "<p>"], "\n", $tmp[2])))));
					array_walk($tmp, function (&$tmp) {
						$tmp = trim($tmp);
					});
					$text = implode("\n", $tmp);


					if (preg_match(
						"/(?:story_fbid=)(.+)(?:&)/Usi",
						$mv,
						$tmp
					)) {
						$fbid = trim(fe($tmp[1]));
					} elseif (preg_match(
						"/(?:story_fbid\.)(\d+)(?:%)/Usi",
						$mv,
						$tmp
					)) {
						$fbid = trim(fe($tmp[1]));
					}

					$data["user_posts"][] = [
						"post_url" => (isset($fbid) ? "https://www.facebook.com/{$fbid}" : null),
						"story_fbid" => $fbid,
						"type" => "text",
						"title" => $h3,
						"text" => $text
					];
				}

			}
		}
	}

	if (preg_match(
		"/(?: width=\"320\" height=\"200\".+<a href=\")(.*)(?:\")/Usi",
		$out["out"],
		$m
	)) {
		$photoUrl = fe($m[1]);
		$out = $fb->go("https://mobile.facebook.com/{$photoUrl}");
		$out["out"] = gzdecode($out["out"]);

		if (preg_match(
			"/(?:src=\")([^\"]+scontent[^\"]+)(?:\")/Usi",
			$out["out"],
			$m
		)) {
			$data["user_info"]["profile_picture"] = trim(fe($m[1]));
		}
	}

	print json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (FphpException $e) {
	echo "Error: ". $e->getMessage()."\n";
	exit(1);
}
