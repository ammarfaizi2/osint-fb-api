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
}
