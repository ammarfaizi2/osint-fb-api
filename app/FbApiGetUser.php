<?php

namespace App;

use Fphp\Fphp;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @version 0.0.1
 * @package \App
 */
final class FbApi
{
	/**
	 * @param \Fphp\Fphp
	 */
	private $fb;

	/**
	 * @param \Fphp\Fphp $fb
	 * 
	 * Constructor.
	 */
	public function __construct(Fphp $fb)
	{
		$this->fb = $fb;
	}

	private function getUserInfo()
	{
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
	}

	private function getUserPost()
	{

	}
}
