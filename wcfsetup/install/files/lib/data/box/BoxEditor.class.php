<?php
namespace wcf\data\box;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit boxes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.box
 * @category	Community Framework
 * @since	2.2
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
