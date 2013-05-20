<?php
namespace wcf\data\bbcode\attribute;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit bbcode attributes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.bbcode
 * @subpackage	data.bbcode.attribute
 * @category	Community Framework
 */
class BBCodeAttributeEditor extends DatabaseObjectEditor {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	public static $baseClass = 'wcf\data\bbcode\attribute\BBCodeAttribute';
}
