<?php
namespace wcf\data\user\profile\visitor;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit profile visitors.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.profile.visitor
 * @category	Community Framework
 */
class UserProfileVisitorEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\profile\visitor\UserProfileVisitor';
}
