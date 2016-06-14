<?php
namespace wcf\system\exception;

/**
 * This is a custom implementation of the default \InvalidArgumentException.
 * It is used for backwards compatibility reasons. Do not rely on it
 * inheriting \wcf\system\exception\SystemException.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Exception
 * @since	3.0
 */
class InvalidArgumentException extends SystemException { }
