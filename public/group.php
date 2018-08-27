<?php

require __DIR__."/../vendor/autoload.php";
require __DIR__."/../credential.tmp";

use Fphp\Fphp;
use Fphp\Exceptions\FphpException;

header("Content-Type: application/json");

if (! isset($_GET["id"])) {
	print json_encode(["error" => "\"id\" parameter must be provided!"]);
	exit(1);
}

if (! is_string($_GET["id"])) {
	print json_encode(["error" => "\"id\" parameter must be a string!"]);
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
	$group_ = $_GET["id"];

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

	$data = [
		"group_info" => [
			"group_name" => null,
			"group_url" => null,
		],
		"group_posts" => []

	];

	$out = $fb->go("https://m.facebook.com/groups/{$group_}", [CURLOPT_FOLLOWLOCATION => true]);
	$fbout = gzdecode($out["out"]);
	// $fbout = file_get_contents("a.tmp");
	$url = explode("?", $out["info"]["url"], 2);
	$url = str_replace(["https://mobile.", "https://m."], "https://www.", $url[0]);
	$data["group_info"]["group_url"] = $url;
	
	if (preg_match(
		"/(?:<title>)(.*)(<\/title>)/Usi",
		$fbout,
		$m
	)) {
		$data["group_info"]["group_name"] = trim(fe($m[1]));
	}

	/**
	 * Get posts.
	 */
	if (preg_match_all(
		"/(?:<table class=\".{1,2}\" role=\"presentation\">)(.+)(?:<\/abbr>)/Usi",
		$fbout,
		$m
	)) {
		foreach ($m[1] as $k => $mv) {

			$postedTime = null;
			if (preg_match(
				"/<abbr>(.*)<\/abbr>/Usi",
				$m[0][$k],
				$tmp
			)) {
				$postedTime = str_replace(["hrs"], ["hours"], trim(fe($tmp[1])));
			}

			if ($k === 0) {
				$mv = explode("<table class=\"..\" role=\"presentation\">", $mv);
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
					"/<img.+scontent.+>/Usi",
					$mv,
					$mc
				)) {
					$fbid = $alt = $photoUrl = $caption = null;

					if (preg_match(
						"/(?:src=\")([^\"]*scontent[^\"]*)(?:\")/Usi",
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
						"/(?:story_fbid=|post_id\.)(.+)(?:[\D])/Usi",
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

					$data["group_posts"][] = [
						"post_url" => (isset($fbid) ? "https://www.facebook.com/{$fbid}" : null),
						"story_fbid" => $fbid,
						"type" => "video",
						"title" => $h3,
						"thumnail_url" => $photoUrl,
						"caption" => $caption,
						"alt_predict" => $alt,
						"abbr_time" => $postedTime
					];
				} elseif (preg_match(
					"/<img.+scontent.+>/Usi",
					$mv,
					$mc
				)) {
					$fbid = $alt = $photoUrl = $caption = null;

					if (preg_match(
						"/(?:src=\")([^\"]*scontent[^\"]*)(?:\")/Usi",
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
						"/(?:[^\"]*scontent[^\"]*.+alt=\")(.*)(?:\")/Usi",
						$mc[0],
						$tmp
					)) {
						$alt = fe($tmp[1]);
					}

					if (preg_match(
						"/(?:story_fbid=|post_id\.)(.+)(?:[\D])/Usi",
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

					$data["group_posts"][] = [
						"post_url" => (isset($fbid) ? "https://www.facebook.com/{$fbid}" : null),
						"story_fbid" => $fbid,
						"type" => "photo",
						"title" => $h3,
						"photo_url" => $photoUrl,
						"caption" => $caption,
						"alt_predict" => $alt,
						"abbr_time" => $postedTime
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
						"/(?:story_fbid=|post_id\.)(.+)(?:[\D])/Usi",
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

					$data["group_posts"][] = [
						"post_url" => (isset($fbid) ? "https://www.facebook.com/{$fbid}" : null),
						"story_fbid" => $fbid,
						"type" => "text",
						"title" => $h3,
						"text" => $text,
						"abbr_time" => $postedTime
					];
				}
			}
		}
	}

	print json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (FphpException $e) {
	print json_encode(
		[
			"error" => $e->getMessage()
		],
		 JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
	);
	exit(1);
}
