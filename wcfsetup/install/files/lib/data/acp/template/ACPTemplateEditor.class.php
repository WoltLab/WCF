<?php
namespace wcf\data\acp\template;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit ACP templates.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.template
 * @category 	Community Framework
 */
class ACPTemplateEditor extends DatabaseObjectEditor {
	/**
	 * @see	DatabaseObjectEditor::$baseClass
	 */
	protected static $baseClass = 'wcf\data\acp\template\ACPTemplate';
}
