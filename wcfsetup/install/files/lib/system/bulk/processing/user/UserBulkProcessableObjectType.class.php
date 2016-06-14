<?php
namespace wcf\system\bulk\processing\user;
use wcf\system\bulk\processing\AbstractBulkProcessableObjectType;

/**
 * Bulk processable object type implementation for users.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bulk\Processing\User
 * @since	3.0
 */
class UserBulkProcessableObjectType extends AbstractBulkProcessableObjectType {
	/**
	 * @inheritDoc
	 */
	protected $templateName = 'userConditions';
}
