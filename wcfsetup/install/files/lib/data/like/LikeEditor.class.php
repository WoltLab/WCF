<?php
namespace wcf\data\like;
use wcf\data\DatabaseObjectEditor;

/**
 * Extends the like object with functions to create, update and delete likes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.like
 * @category	Community Framework
 */
class LikeEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectEditor::$baseClass
	 */
	protected static $baseClass = 'wcf\data\like\Like';
}
