<?php

namespace Fphp\Utils;

use Fphp\UtilsFoundation;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @version 0.0.1
 * @package \Fphp
 */
class GetUserInfo extends UtilsFoundation
{	
	/**
	 * @param string $user
	 * @return array
	 */
	public function run(string $user): array
	{
		$data = [
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
		];

		

		return $data;
	}
}
