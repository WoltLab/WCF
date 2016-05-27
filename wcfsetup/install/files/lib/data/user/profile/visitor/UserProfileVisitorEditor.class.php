<?php
namespace wcf\data\user\profile\visitor;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit profile visitors.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.profile.visitor
 * @category	Community Framework
 * 
 * @method	UserProfileVisitor	getDecoratedObject()
 * @mixin	UserProfileVisitor
 */
class UserProfileVisitorEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserProfileVisitor::class;
}
