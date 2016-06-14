<?php
namespace wcf\system\importer;
use wcf\data\user\object\watch\UserObjectWatch;
use wcf\data\user\object\watch\UserObjectWatchEditor;
use wcf\system\database\DatabaseException;

/**
 * Imports watched objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class AbstractWatchedObjectImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = UserObjectWatch::class;
	
	/**
	 * object type id for watched objects
	 * @var	integer
	 */
	protected $objectTypeID = 0;
	
	/**
	 * @inheritDoc
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
