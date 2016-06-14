<?php
namespace wcf\data\bbcode\attribute;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit bbcode attributes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Bbcode\Attribute
 * 
 * @method	BBCodeAttribute		getDecoratedObject()
 * @mixin	BBCodeAttribute
 */
class BBCodeAttributeEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	public static $baseClass = BBCodeAttribute::class;
}
