<?php
namespace wcf\data\spider;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes spider-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Spider
 * 
 * @method	Spider		create()
 * @method	SpiderEditor[]	getObjects()
 * @method	SpiderEditor	getSingleObject()
 */
class SpiderAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = SpiderEditor::class;
}
