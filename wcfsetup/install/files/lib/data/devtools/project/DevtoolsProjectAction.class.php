<?php
namespace wcf\data\devtools\project;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes devtools project related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Devtools\Project
 * @since	3.1
 * 
 * @method	DevtoolsProjectEditor[]	getObjects()
 * @method	DevtoolsProjectEditor	getSingleObject()
 */
class DevtoolsProjectAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = DevtoolsProjectEditor::class;
}
