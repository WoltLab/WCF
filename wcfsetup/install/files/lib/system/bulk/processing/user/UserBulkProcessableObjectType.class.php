<?php
namespace wcf\system\bulk\processing\user;
use wcf\system\bulk\processing\AbstractBulkProcessableObjectType;

/**
 * Bulk processable object type implementation for users.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bulk.processing.user
 * @category	Community Framework
 * @since	2.2
 */
class UserBulkProcessableObjectType extends AbstractBulkProcessableObjectType {
	/**
	 * @see	\wcf\system\bulk\processing\AbstractBulkProcessableObjectType::$templateName
	 */
	protected $templateName = 'userConditions';
}
