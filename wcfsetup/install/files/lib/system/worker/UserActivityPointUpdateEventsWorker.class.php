<?php
namespace wcf\system\worker;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Worker implementation for updating user activity point events.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.worker
 * @category	Community Framework
 */
class UserActivityPointUpdateEventsWorker extends AbstractWorker {
	/**
	 * @see	\wcf\system\worker\AbstractWorker::$limit
	 */
	protected $limit = 1;
	
	/**
	 * object types
	 * @var	ObjectType[]
	 */
	public $objectTypes = [];
	
	/**
	 * @see	\wcf\system\worker\IWorker
	 */
	public function __construct(array $parameters) {
		parent::__construct($parameters);
		
		$this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.user.activityPointEvent');
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::validate()
	 */
	public function validate() {
		WCF::getSession()->checkPermissions(['admin.user.canEditActivityPoints']);
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::countObjects()
	 */
	public function countObjects() {
		$this->count = count($this->objectTypes);
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::execute()
	 */
	public function execute() {
		$i = 0;
		foreach ($this->objectTypes as $objectType) {
			if ($i == $this->loopCount) {
				$sql = "UPDATE		wcf".WCF_N."_user_activity_point
					SET		activityPoints = items * ?
					WHERE		objectTypeID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([
					$objectType->points,
					$objectType->objectTypeID
				]);
			}
			
			$i++;
		}
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::getProceedURL()
	 */
	public function getProceedURL() {
		return LinkHandler::getInstance()->getLink('UserActivityPointOption');
	}
}
