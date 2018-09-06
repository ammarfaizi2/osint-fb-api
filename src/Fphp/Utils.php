<?php

namespace Fphp;

use Fphp\Exceptions\FphpException;
use Fphp\Utils\GetUserInfo;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @version 0.0.1
 * @package \Fphp
 */
final class Utils
{
	/**
	 * @var \Fphp\Fphp
	 */
	private $fphp;

	/**
	 * @param \Fphp\Fphp $fphp
	 *
	 * Constructor.
	 */
	public function __construct(Fphp $fphp)
	{
		$this->fphp = $fphp;
	}

	/**
	 * @param string $method
	 * @param array  $parameters
	 * @return mixed
	 */
	public function __call(string $method, array $parameters)
	{
		switch (strtolower($method)) {
			case 'getuserinfo':
				$st = new GetUserInfo($this->fphp);
				break;
			default:
				break;
		}

		return $this->invokeRun($st, $parameters);
	}

	/**
	 * @param \Fphp\UtilsFoundation
	 * @return mixed
	 */
	private function invokeRun(UtilsFoundation $st, array $parameters)
	{
		return $st->run(...$parameters);
	}
}
