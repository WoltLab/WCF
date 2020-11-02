<?php
namespace wcf\system\exception;

/**
 * Exception implementation instances where an API allows passing generic objects but a concrete
 * implementation requires the objects to be instances of a specific (sub)class.
 * 
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Exception
 * @since       5.4
 */
class InvalidObjectArgument extends \InvalidArgumentException {
	/**
	 * InvalidObjectArgument constructor.
	 */
	public function __construct(object $object, string $expectedClass, string $objectName = 'Object') {
		parent::__construct("{$objectName} is no instance of '{$expectedClass}', instance of '" . get_class($object) . "' given.");
	}
}
