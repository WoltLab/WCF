<?php
namespace wcf\data\reaction\type;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;

/**
 * A reaction type editor.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Reaction\Type
 * @since	5.2
 * 
 * @method      static	        ReactionType	create(array $parameters = [])
 * @method		        ReactionType	getDecoratedObject()
 * @mixin	                ReactionType
 */
class ReactionTypeEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ReactionType::class;
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		ReactionTypeCache::getInstance()->clearCache();
	}
}
