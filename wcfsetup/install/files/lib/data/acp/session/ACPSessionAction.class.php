<?php
namespace wcf\data\acp\session;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes ACP session-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Session
 * 
 * @method	ACPSession		create()
 * @method	ACPSessionEditor[]	getObjects()
 * @method	ACPSessionEditor	getSingleObject()
 */
class ACPSessionAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = ACPSessionEditor::class;
}
