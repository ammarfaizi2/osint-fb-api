<?php

namespace Fphp;

use Fphp\Exceptions\FphpException;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @version 0.0.1
 * @package \Fphp
 */
final class Fphp
{
	const LOGIN_SUCCESS = 1;

	const LOGIN_FAILED = 2;

	const LOGIN_CHECKPOINT = 3;

	/**
	 * @var string
	 */
	private $email;

	/**
	 * @var string
	 */
	private $pass;

	/**
	 * @var string
	 */
	private $cookieFile;

	/**
	 * @var \Fphp\HttpClient
	 */
	private $http;

	/**
	 * @var \Fphp\Utils
	 */

	/**
	 * @param string $email
	 * @param string $pass
	 * @param string $cookieFile
	 *
	 * Constructor.
	 */
	public function __construct(string $email, string $pass, string $cookieFile)
	{
		$this->email = $email;
		$this->pass  = $pass;
		$this->cookieFile = $cookieFile;

		file_exists($this->cookieFile) or file_put_contents($this->cookieFile, "");
		
		if (! file_exists($this->cookieFile)) {
			throw new FphpException("Could not create the cookie file");
		}
		if (! is_readable($this->cookieFile)) {
			throw new FphpException("Cookie file is not readable: {$this->cookieFile}");
		}
		if (! is_writable($this->cookieFile)) {
			throw new FphpException("Cookie file is not writeable: {$this->cookieFile}");
		}

		$this->http = new HttpClient(
			[
				CURLOPT_COOKIEFILE => $this->cookieFile,
				CURLOPT_COOKIEJAR => $this->cookieFile,
				CURLOPT_HTTPHEADER => [
					"Accept-Encoding: gzip",
					"Accept-Language: en-US,en;q=0.5",
					"Connection: keep-alive",
					"DNT: 1",
					"Upgrade-Insecure-Requests: 1"
				]
			]
		);


		$this->utils = new Utils($this);
	}

	/**
	 * @param string $url
	 * @param array  $opt
	 * @return array
	 */
	public function go(string $url, array $opt = []): array
	{
		return $this->http->exec($url, $opt);
	}

	/**
	 * @param bool $force
	 * @throws \Fphp\Exceptions\FphpException
	 * @return int
	 */
	public function login(bool $force = false): int
	{
		if ($force) {
			if (unlink($this->cookieFile)) {
				$this->__construct($this->email, $this->pass, $this->cookieFile);
			} else {
				throw new FphpException("Could not delete cookie file in {$this->cookieFile}");
			}
		}

		$l = $this->http->exec("https://m.facebook.com/login.php");

		$l["out"] = @gzdecode($l["out"]);

		if ($l["errno"]) {
			throw new FphpException($l["error"]);
		}

		// $l["out"] = file_get_contents("out.tmp");

		if (preg_match("/(?:<form method=\"post\" action=\")(.*)(?:\")/Usi", $l["out"], $m)) {
			
			$actionUrl = fe($m[1]);
			$postData = [
				"email" => $this->email,
				"pass" => $this->pass
			];

			/**
			 * Get hidden input values.
			 */
			if (preg_match_all("/<input[^\>]+type=\"hidden\".+>/Usi", $l["out"], $m)) {
				foreach ($m[0] as $v) {
					if (preg_match("/(?:name=\")(.*)(?:\")/Usi", $v, $m)) {
						$key = fe($m[1]);
						if (preg_match("/(?:value=\")(.*)(?:\")/Usi", $v, $m)) {
							$val = fe($m[1]);
						} else {
							$val = "";
						}
						$postData[trim($key)] = trim($val);
					}
				}
			} else {
				throw new FphpException("Could not find the hidden input");
			}

			/**
			 * Get submit button value.
			 */
			if (preg_match("/<input[^\>]+name=\"login\".+/Usi", $l["out"], $m)) {
				if (preg_match("/(?:value=\")(.*)(?:\")/Usi", $m[0], $m)) {
					$postData["login"] = trim(fe($m[1]));
				} else {
					$postData["login"] = "Login";	
				}
			} else {
				$postData["login"] = "Login";
			}

			$this->http->exec($actionUrl, [
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => http_build_query($postData),
				CURLOPT_REFERER => $l["info"]["url"]
			]);

			if (! file_exists($this->cookieFile)) {
				return self::LOGIN_FAILED;
			}

			$cookie = file_get_contents($this->cookieFile);

			if (preg_match("/checkpoint/", $cookie)) {
				return self::LOGIN_CHECKPOINT;
			}

			if (preg_match("/c_user/", $cookie)) {
				return self::LOGIN_SUCCESS;
			} else {
				return self::LOGIN_FAILED;
			}
		}

		$cookie = file_get_contents($this->cookieFile);
		if (preg_match("/c_user/", $cookie)) {
			return self::LOGIN_SUCCESS;
		}
		
		throw new FphpException("Coult not find the login form");
	}

	public function getUserInfo()
	{
		
	}

	/**
	 * @return void
	 */
	public function httpStream()
	{
	}
}
