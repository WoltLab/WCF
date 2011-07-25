<?php
namespace wcf\system\exception;

/**
 * @todo	Ableitung von welcher Exception und wie soll die
 * 		konkrete Anzeige sein, wenn diese Exception nicht
 * 		ordnungsgemäß abgefangen wird?
 */
class ValidateActionException extends \Exception {
	public function __construct($message) {
		die($message);
	}
}
