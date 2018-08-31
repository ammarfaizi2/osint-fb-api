<?php

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
	$user_ = $_GET["user"];

	$data = [
		"user_info" => [
			"name" => null,
			"profile_picture" => null,
			"profile_url" => null,
			"extended_info" => [
				"work" => [],
				"education" => [],
				"living" => [],
				"contact_info" => [
					"mobile" => [],
					"address" => [],
					"mobile" => null,
					"address" => [],
					"facebook" => null,
					"github" => null,
					"youtube" => null,
					"yahoo!_messenger" => null,
					"line" => null,
					"bbm" => null,
					"instagram" => null,
					"twitter" => null,
					"websites" => [],
					"email" => [],
				],
				"basic_info" => [
					"birthday" => null,
					"gender" => null,
					"interested_in" => null,
					"languages" => null,
					"religious_views" => null,
					"political_views" => null,
				]
			]
		],
		"user_posts" => []
	];

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

	$out = $fb->go("https://m.facebook.com/{$user_}?v=timeline", [CURLOPT_FOLLOWLOCATION => true]);
	$fbout = gzdecode($out["out"]);
	//$fbout = file_get_contents("a.tmp");
	
	$url = explode("?", $out["info"]["url"], 2);
	$realurl = $url[0];
	$url = str_replace(["https://mobile.", "https://m."], "https://www.", $url[0]);
	$data["user_info"]["profile_url"] = $url;
	
	// print $out["out"];die;
	// $out["out"] = file_get_contents("a.tmp");
	
	/**
	 * Get name.
	 */
	if (preg_match(
		"/(?:<title>)(.*)(?:<\/title>)/Usi",
		$fbout,
		$m
	)) {
		$data["user_info"]["name"] = trim(fe($m[1]));
	}

	/**
	 * Get profile picture.
	 */
	if (preg_match(
		"/(?: width=\"320\" height=\"200\".+<a href=\")(.*)(?:\")/Usi",
		$fbout,
		$m
	)) {
		$photoUrl = fe($m[1]);
		$out = $fb->go("https://m.facebook.com/{$photoUrl}", [CURLOPT_FOLLOWLOCATION=>true]);
		$out["out"] = gzdecode($out["out"]);

		if (preg_match(
			"/(?:src=\")([^\"]+scontent[^\"]+)(?:\")/Usi",
			$out["out"],
			$m
		)) {
			$data["user_info"]["profile_picture"] = trim(fe($m[1]));
		}
	}

	for ($i=0; $i < $endpage; $i++) {

		if ($i > 0 && !empty($nextPage)) {
			$out = $fb->go("https://m.facebook.com/{$nextPage}", [CURLOPT_FOLLOWLOCATION=>true]);
			$fbout = gzdecode($out["out"]);
		}

		if (preg_match(
			"/(?:href=\")([^\"]*)(?:\">Show more)/Usi",
			$fbout,
			$tmp
		)) {
			$nextPage = trim(fe($tmp[1]));
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
							"text" => $text,
							"abbr_time" => $postedTime
						];
					}
				}
			}
		}

		unset($tmp, $mv, $text, $fbid, $h3, $out, $photoUrl, $caption, $alt);
	}

	$out = $fb->go("{$realurl}/about", [CURLOPT_FOLLOWLOCATION=>true]);
	$fbout = gzdecode($out["out"]);

	/**
	 * Get profile picture.
	 */
	if (!isset($data["user_info"]["profile_picture"]) && preg_match(
		"/(?: width=\"320\" height=\"200\".+<a href=\")(.*)(?:\")/Usi",
		$fbout,
		$m
	)) {
		$photoUrl = fe($m[1]);
		$out = $fb->go("https://m.facebook.com/{$photoUrl}", [CURLOPT_FOLLOWLOCATION=>true]);
		$out["out"] = gzdecode($out["out"]);

		if (preg_match(
			"/(?:src=\")([^\"]+scontent[^\"]+)(?:\")/Usi",
			$out["out"],
			$m
		)) {
			$data["user_info"]["profile_picture"] = trim(fe($m[1]));
		}
	}


	$prr = ["work", "education"];

	foreach ($prr as $pgr) {
		$data["user_info"]["extended_info"][$pgr] = [];
		/**
		 * Get extended info.
		 */
		if (preg_match(
			"/(?:<div id=\"{$pgr}\">)(.+)(?:<div id=)/Usi",
			$fbout,
			$m
		)) {
			if (preg_match_all(
				"/(?:<span class=\".. .. .. ..\"><a.+>)(.*)(?:<\/a>)/Usi",
				$m[1],
				$mv
			)) {
				foreach ($mv[1] as $vpgr) {
					$data["user_info"]["extended_info"][$pgr][] = trim(fe($vpgr));
				}
			}
		}
	}

	$data["user_info"]["extended_info"]["living"] = [];
	/**
	 * Get extended info.
	 */
	if (preg_match(
		"/(?:<div id=\"living\">)(.+)(?:<div id=)/Usi",
		$fbout,
		$m
	)) {
		if (preg_match_all(
			"/(?:<div class=\".. ..\" title=\")(.*)(?:\".+<div.+<a.+>)(.*)(<\/a>)/Usi",
			$m[1],
			$mv
		)) {
			foreach ($mv[1] as $k => $v) {
				$data["user_info"]["extended_info"]["living"][strtolower(str_replace(" ", "_", trim(fe($v))))] = trim(fe($mv[2][$k]));
			}
		}
	}

	$data["user_info"]["extended_info"]["contact_info"] = [];
	/**
	 * Get extended info.
	 */
	if (preg_match(
		"/(?:<div id=\"contact-info\">)(.+)(?:<div id=)/Usi",
		$fbout,
		$m
	)) {

		if (preg_match_all(
			"/(?:<div class=\".. .. ..\" title=\")(.*)(?:\".+<td.+>.+<td.+>)(.*)(<\/td>)/Usi",
			$m[1],
			$mv
		)) {
			foreach ($mv[1] as $k => $v) {
				$key = strtolower(str_replace(" ", "_", trim(fe($v))));
				if (in_array($key, ["websites", "email", "address", "phone"])) {
					$data["user_info"]["extended_info"]["contact_info"][$key][] = trim(fe(strip_tags($mv[2][$k])));
				} else {
					$data["user_info"]["extended_info"]["contact_info"][$key] = trim(fe(strip_tags($mv[2][$k])));
				}
			}
		}
	}

	$data["user_info"]["extended_info"]["basic_info"] = [];
	/**
	 * Get extended info.
	 */
	if (preg_match(
		"/(?:<div id=\"basic-info\">)(.+)(?:<div id=)/Usi",
		$fbout,
		$m
	)) {

		if (preg_match_all(
			"/(?:<div class=\".. .. ..\" title=\")(.*)(?:\".+<td.+>.+<td.+>)(.*)(<\/td>)/Usi",
			$m[1],
			$mv
		)) {
			foreach ($mv[1] as $k => $v) {
				$key = strtolower(str_replace(" ", "_", trim(fe($v))));
				$data["user_info"]["extended_info"]["basic_info"][$key] = trim(fe(strip_tags($mv[2][$k])));
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
