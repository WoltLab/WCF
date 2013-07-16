<?php
namespace wcf\system\importer;
use wcf\data\user\object\watch\UserObjectWatchAction;

/**
 * Imports watched objects.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class AbstractWatchedObjectImporter implements IImporter {
	/**
	 * object type id for watched objects
	 * @var integer
	 */
	protected $objectTypeID = 0;
	
	/**
	 * @see wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		$data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
		if (!$data['userID']) return 0;
		
		$action = new UserObjectWatchAction(array(), 'create', array(
			'data' => array_merge($data, array('objectTypeID' => $this->objectTypeID))		
		));
		$returnValues = $action->executeAction();
		return $returnValues['returnValues']->watchID;
	}
}
