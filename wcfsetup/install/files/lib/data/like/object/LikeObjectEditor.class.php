<?php
namespace wcf\data\like\object;
use wcf\data\DatabaseObjectEditor;

/**
 * Extends the LikeObject object with functions to create, update and delete liked objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.like.object
 * @category	Community Framework
 */
class LikeObjectEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectEditor::$baseClass
	 */
	protected static $baseClass = 'wcf\data\like\object\LikeObject';
}
