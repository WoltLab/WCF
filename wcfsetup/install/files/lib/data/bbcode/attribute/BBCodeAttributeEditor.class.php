<?php
namespace wcf\data\bbcode\attribute;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit bbcode attributes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.bbcode.attribute
 * @category	Community Framework
 */
class BBCodeAttributeEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	public static $baseClass = 'wcf\data\bbcode\attribute\BBCodeAttribute';
}
