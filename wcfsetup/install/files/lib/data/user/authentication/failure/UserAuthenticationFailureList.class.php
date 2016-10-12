<?php
namespace wcf\data\user\authentication\failure;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user authentication failures.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Authentication\Failure
 *
 * @method	UserAuthenticationFailure		current()
 * @method	UserAuthenticationFailure[]		getObjects()
 * @method	UserAuthenticationFailure|null		search($objectID)
 * @property	UserAuthenticationFailure[]		$objects
 */
class UserAuthenticationFailureList extends DatabaseObjectList { }
