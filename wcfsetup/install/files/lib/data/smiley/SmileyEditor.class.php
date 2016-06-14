<?php
namespace wcf\data\smiley;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\SmileyCacheBuilder;

/**
 * Provides functions to edit smilies.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Smiley
 * 
 * @method	Smiley		getDecoratedObject()
 * @mixin	Smiley
 */
class SmileyEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	public static $baseClass = Smiley::class;
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		SmileyCacheBuilder::getInstance()->reset();
	}
}
