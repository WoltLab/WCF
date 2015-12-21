<?php
namespace wcf\system\email\transport\exception;
use wcf\system\exception\SystemException;

/**
 * Denotes a permanent failure during delivery. It should not be retried later.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.email.transport.exception
 * @category	Community Framework
 * @since	2.2
 */
class PermanentFailure extends SystemException { }
