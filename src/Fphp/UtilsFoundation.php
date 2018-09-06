<?php

namespace Fphp;

use Fphp\Contracts\Utils as UtilsContract;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @version 0.0.1
 * @package \Fphp
 */
abstract class UtilsFoundation implements UtilsContract
{
	/**
	 * @var \Fphp\Fphp
	 */
	protected $fphp;

	/**
	 * @param \Fphp\Fphp $fphp
	 *
	 * Constructor.
	 */
	public function __construct(Fphp $fphp)
	{
		$this->fphp = $fphp;
	}
}
