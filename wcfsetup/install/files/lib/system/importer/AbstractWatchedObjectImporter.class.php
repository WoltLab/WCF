<?php
namespace wcf\system\importer;
use wcf\data\user\object\watch\UserObjectWatchEditor;

/**
 * Imports watched objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class AbstractWatchedObjectImporter extends AbstractImporter {
	/**
	 * @see	\wcf\system\importer\AbstractImporter::$className
	 */
	protected $className = 'wcf\data\user\object\watch';
	
	/**
	 * object type id for watched objects
	 * @var	integer
	 */
	protected $objectTypeID = 0;
	
	/**
	 * @see	\wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		$data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
		if (!$data['userID']) return 0;
		
		$watch = UserObjectWatchEditor::create(array_merge($data, array('objectTypeID' => $this->objectTypeID)));
		return $watch->watchID;
	}
}
