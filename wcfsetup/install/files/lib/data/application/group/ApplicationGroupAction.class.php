<?php
namespace wcf\data\application\group;
use wcf\data\application\ApplicationAction;
use wcf\data\application\ApplicationList;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\cache\CacheHandler;
use wcf\system\language\LanguageFactory;

/**
 * Executes application group-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.application.group
 * @category	Community Framework
 */
class ApplicationGroupAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\application\group\ApplicationGroupEditor';
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.system.canManageApplication');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::create()
	 */
	public function create() {
		$applicationGroup = parent::create();
		
		$applicationAction = new ApplicationAction($this->parameters['applications'], 'group', array(
			'groupID' => $applicationGroup->groupID,
			'primaryApplication' => $this->parameters['primaryApplication']
		));
		$applicationAction->executeAction();
		
		return $applicationGroup;
	}
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::update()
	 */
	public function update() {
		parent::update();
		
		// read list of currently associated applications
		$applicationGroup = current(reset($this->objects));
		$applicationList = new ApplicationList();
		$applicationList->getConditionBuilder()->add("application.groupID = ?", array($applicationGroup->groupID));
		$applicationList->sqlLimit = 0;
		$applicationList->readObjects();
		
		$updateApplications = $removeApplications = array();
		foreach ($applicationList as $application) {
			$index = array_search($application->packageID, $this->parameters['applications']);
			if ($index === false) {
				$removeApplications[] = $application;
			}
			else {
				// already existing
				$updateApplications[] = $application;
				unset($this->parameters['applications'][$index]);
			}
		}
		
		if (!empty($this->parameters['applications'])) {
			$applicationList = new ApplicationList();
			$applicationList->getConditionBuilder()->add("application.packageID IN (?)", $this->parameters['applications']);
			$applicationList->sqlLimit = 0;
			$applicationList->readObjects();
			$updateApplications = array_merge($updateApplications, $applicationList->getObjects());
		}
		
		// rebuild current group
		$applicationAction = new ApplicationAction($updateApplications, 'group', array(
			'groupID' => $applicationGroup->groupID,
			'primaryApplication' => $this->parameters['primaryApplication']
		));
		$applicationAction->executeAction();
		
		// remove applications from group
		if (!empty($removeApplications)) {
			$applicationAction = new ApplicationAction($removeApplications, 'ungroup');
			$applicationAction->executeAction();
		}
		
		$this->clearCache();
	}
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::delete()
	 */
	public function delete() {
		// read all associated applications
		$applicationGroup = current($this->objects);
		$applicationList = new ApplicationList();
		$applicationList->getConditionBuilder()->add("application.groupID = ?", array($applicationGroup->groupID));
		$applicationList->sqlLimit = 0;
		$applicationList->readObjects();
		
		$applicationAction = new ApplicationAction($applicationList->getObjects(), 'ungroup');
		$applicationAction->executeAction();
		
		$this->clearCache();
		
		return parent::delete();
	}
	
	/**
	 * Clears WCF cache.
	 */
	protected function clearCache() {
		// delete language cache and compiled templates
		LanguageFactory::getInstance()->deleteLanguageCache();
		
		// delete WCF cache
		CacheHandler::getInstance()->clear(WCF_DIR.'cache', '*.php');
		CacheHandler::getInstance()->clear(WCF_DIR.'cache/templateListener', '*.php');
	}
}
