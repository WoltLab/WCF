<?php
namespace wcf\system\email\transport\exception;

/**
 * Denotes a permanent failure during delivery. It should not be retried later.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Email\Transport\Exception
 * @since	3.0
 */
class PermanentFailure extends \Exception { }
