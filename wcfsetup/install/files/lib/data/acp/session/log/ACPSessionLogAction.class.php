<?php
namespace wcf\data\acp\session\log;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes ACP session log-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session.log
 * @category	Community Framework
 * 
 * @method	ACPSessionLog		create()
 * @method	ACPSessionLogEditor[]	getObjects()
 * @method	ACPSessionLogEditor	getSingleObject()
 */
class ACPSessionLogAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = ACPSessionLogEditor::class;
}
