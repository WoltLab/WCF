<?php
namespace wcf\data\user\authentication\failure;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user authentication failures.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.authentication.failure
 * @category	Community Framework
 *
 * @method	UserAuthenticationFailure		current()
 * @method	UserAuthenticationFailure[]		getObjects()
 * @method	UserAuthenticationFailure|null		search($objectID)
 * @property	UserAuthenticationFailure[]		$objects
 */
class UserAuthenticationFailureList extends DatabaseObjectList { }
