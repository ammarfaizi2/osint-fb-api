<?php

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 * @version 0.0.1
 */

if (! function_exists("fe")) {

	/**
	 * @param string $string
	 * @return string
	 */
	function fe(string $string): string
	{
		return html_entity_decode($string, ENT_QUOTES, "UTF-8");
	}	
}
