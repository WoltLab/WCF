<?php
namespace wcf\data\like;
use wcf\data\DatabaseObjectEditor;

/**
 * Extends the like object with functions to create, update and delete likes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Like
 * 
 * @method	Like	getDecoratedObject()
 * @mixin	Like
 */
class LikeEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Like::class;
}
