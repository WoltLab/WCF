<?php
namespace wcf\data\acp\session\access\log;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes ACP session access log-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session.access.log
 * @category	Community Framework
 * 
 * @method	ACPSessionAccessLog		create()
 * @method	ACPSessionAccessLogEditor[]	getObjects()
 * @method	ACPSessionAccessLogEditor	getSingleObject()
 */
class ACPSessionAccessLogAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = ACPSessionAccessLogEditor::class;
}
