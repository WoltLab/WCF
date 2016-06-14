<?php
namespace wcf\data\box;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit boxes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Box
 * @since	3.0
 * 
 * @method	Box	getDecoratedObject()
 * @mixin	Box
 */
class BoxEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Box::class;
}
