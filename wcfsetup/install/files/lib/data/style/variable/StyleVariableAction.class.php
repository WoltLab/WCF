<?php
namespace wcf\data\style\variable;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes style variable-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Style\Variable
 * 
 * @method	StyleVariable		create()
 * @method	StyleVariableEditor[]	getObjects()
 * @method	StyleVariableEditor	getSingleObject()
 */
class StyleVariableAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = StyleVariableEditor::class;
}
