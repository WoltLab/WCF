<?php
namespace wcf\system\worker;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Worker implementation for updating user activity point events.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	system.worker
 * @category	Community Framework
 */
class UserActivityPointUpdateEventsWorker extends AbstractWorker {
	/**
	 * Limiting is dependent on the actual processors.
	 * @see	wcf\system\worker\AbstractWorker::$limit
	 */
	protected $limit = 1;
	
	/**
	 * object types
	 * @var	array<wcf\data\object\type\ObjectType>
	 */
	public $objectTypes = array();
	
	public function __construct(array $parameters) {
		parent::__construct($parameters);
		
		$this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.user.activityPointEvent');
	}
	
	/**
	 * @see	wcf\system\worker\IWorker::validate()
	 */
	public function validate() {
		WCF::getSession()->checkPermissions(array('admin.user.canEditActivityPoints'));
	}
	
	/**
	 * @see	wcf\system\worker\IWorker::countObjects()
	 */
	public function countObjects() {
		$this->count = 0;
		foreach ($this->objectTypes as $objectType) {
			$objectType->requests = $objectType->getProcessor()->countRequests();
			$this->count += $objectType->requests;
		}
	}
	
	/**
	 * @see	wcf\system\worker\IWorker::execute()
	 */
	public function execute() {
		$loopCount = $this->loopCount;
		foreach ($this->objectTypes as $objectType) {
			if ($loopCount < $objectType->requests) {
				$objectType->getProcessor()->updateActivityPointEvents($loopCount);
				return;
			}
			$loopCount -= $objectType->requests;
		}
	}
	
	/**
	 * @see	wcf\system\worker\IWorker::getProceedURL()
	 */
	public function getProceedURL() {
		return LinkHandler::getInstance()->getLink('UserActivityPointOption');
	}
}
