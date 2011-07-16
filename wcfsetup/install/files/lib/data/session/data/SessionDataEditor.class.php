<?php
namespace wcf\data\session\data;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit session data.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2010 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.session.data
 * @category 	Community Framework
 */
class SessionDataEditor extends DatabaseObjectEditor {
	/**
	 * @see	wcf\data\DatabaseObjectEditor::$baseClass
	 */
	protected static $baseClass = 'wcf\data\session\data\SessionData';
}
