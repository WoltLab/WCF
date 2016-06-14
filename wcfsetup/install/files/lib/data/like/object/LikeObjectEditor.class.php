<?php
namespace wcf\data\like\object;
use wcf\data\DatabaseObjectEditor;

/**
 * Extends the LikeObject object with functions to create, update and delete liked objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Like\Object
 * 
 * @method	LikeObject	getDecoratedObject()
 * @mixin	LikeObject
 */
class LikeObjectEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = LikeObject::class;
}
