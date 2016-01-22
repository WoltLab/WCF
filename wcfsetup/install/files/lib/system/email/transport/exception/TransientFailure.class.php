<?php
namespace wcf\system\email\transport\exception;

/**
 * Denotes a transient failure during delivery. It may be retried later.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.email.transport.exception
 * @category	Community Framework
 * @since	2.2
 */
class TransientFailure extends \Exception { }
