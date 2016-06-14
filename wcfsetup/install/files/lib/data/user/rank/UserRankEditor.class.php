<?php
namespace wcf\data\user\rank;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\user\storage\UserStorageHandler;

/**
 * Provides functions to edit user ranks.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Rank
 * 
 * @method	UserRank	getDecoratedObject()
 * @mixin	UserRank
 */
class UserRankEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserRank::class;
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		UserStorageHandler::getInstance()->resetAll('userRank');
	}
}
