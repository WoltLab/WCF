<?php
namespace wcf\data\application\group;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit application groups.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.application.group
 * @category 	Community Framework
 */
class ApplicationGroupEditor extends DatabaseObjectEditor {
	/**
	 * @see	DatabaseObjectEditor::$baseClass
	 */
	protected static $baseClass = 'wcf\data\application\group\ApplicationGroup';
}
?>
