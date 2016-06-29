<?php
namespace wcf\data\box\content;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit box content.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Box\Content
 * @since	3.0
 * 
 * @method	BoxContent	getDecoratedObject()
 * @mixin	BoxContent
 */
class BoxContentEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = BoxContent::class;
}
