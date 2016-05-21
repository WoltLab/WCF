<?php
namespace wcf\system\importer;
use wcf\data\user\object\watch\UserObjectWatchEditor;
use wcf\system\database\DatabaseException;

/**
 * Imports watched objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
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
	public function import($oldID, array $data, array $additionalData = []) {
		$data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
		if (!$data['userID']) return 0;
		
		try {
			$watch = UserObjectWatchEditor::create(array_merge($data, ['objectTypeID' => $this->objectTypeID]));
			return $watch->watchID;
		}
		catch (DatabaseException $e) {
			// 23000 = INTEGRITY CONSTRAINT VIOLATION a.k.a. duplicate key
			if ($e->getCode() != 23000) {
				throw $e;
			}
		}
		
		return 0;
	}
}
