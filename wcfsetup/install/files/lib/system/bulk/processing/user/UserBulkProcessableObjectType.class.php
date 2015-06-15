<?php
namespace wcf\system\bulk\processing\user;
use wcf\system\bulk\processing\AbstractBulkProcessableObjectType;

/**
 * Bulk processable object type implementation for users.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bulk.processing.user
 * @category	Community Framework
 */
class UserBulkProcessableObjectType extends AbstractBulkProcessableObjectType {
	/**
	 * @see	\wcf\system\bulk\processing\AbstractBulkProcessableObjectType::$templateName
	 */
	protected $templateName = 'userConditions';
}
