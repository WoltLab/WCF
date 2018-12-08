<?php
namespace wcf\data\trophy;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;

/**
 * A trophy editor.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Trophy
 * @since	3.1
 * 
 * @mixin	Trophy
 */
class TrophyEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Trophy::class;
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		TrophyCache::getInstance()->clearCache();
	}
}
