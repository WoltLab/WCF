<?php
namespace wcf\data\cronjob;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\cronjob\log\CronjobLogEditor;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Executes cronjob-related actions.
 * 
 * @author	Tim Düsterhus, Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.cronjob
 * @category 	Community Framework
 */
class CronjobAction extends AbstractDatabaseObjectAction {
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\cronjob\CronjobEditor';
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.system.cronjob.canAddCronjob');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.system.cronjob.canDeleteCronjob');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.system.cronjob.canEditCronjob');
	
	/**
	 * Validates permissions and parameters
	 */
	public function validateDelete() {
		parent::validateDelete();
		
		foreach ($this->objects as $cronjob) {
			if (!$cronjob->isDeletable()) {
				throw new ValidateActionException('Insufficient permissions');
			}
		}
	}
	
	/**
	 * Validates permissions and parameters
	 */
	public function validateUpdate() {
		parent::validateUpdate();
		
		foreach ($this->objects as $cronjob) {
			if (!$cronjob->isEditable()) {
				throw new ValidateActionException('Insufficient permissions');
			} 
		}
	}
	
	/**
	 * Validates permissions and parameters
	 */
	public function validateToggle() {
		parent::validateUpdate();
		
		foreach ($this->objects as $cronjob) {
			if (!$cronjob->canBeDisabled()) {
				throw new ValidateActionException('Insufficient permissions');
			} 
		}
	}
	
	/**
	 * Toggles status.
	 */
	public function toggle() {
		foreach ($this->objects as $cronjob) {
			$newStatus = ($cronjob->active) ? 0 : 1;
			$cronjob->update(array('active' => $newStatus));
		}
	}
	
	/**
	 * Validates permissions and parameters
	 */
	public function validateExecute() {
		parent::validateUpdate();
	}
	
	/**
	 * Executes cronjobs.
	 */
	public function execute() {
		$cronjob = $return = array();
		
		foreach ($this->objects as $key => $cronjob) {
			// skip jobs that are already being processed
			if ($cronjob->state == Cronjob::PENDING || $cronjob->state == Cronjob::EXECUTING) {
				unset($this->objects[$key]);
				continue;
			}
			
			// mark them as pending
			$cronjob->update(array('state' => Cronjob::PENDING));
		}
		
		foreach ($this->objects as $cronjob) {
			// it now time for executing
			$cronjob->update(array('state' => Cronjob::EXECUTING));
			$className = $cronjob->className;
			$executable = new $className();
			
			// execute cronjob
			$error = '';
			try {
				$executable->execute(new Cronjob($cronjob->cronjobID));
			}
			catch (\Exception $e) {
				$error = $e->getMessage();
			}
			
			CronjobLogEditor::create(array(
				'cronjobID' => $cronjob->cronjobID,
				'execTime' => TIME_NOW,
				'success' => (int) ($error != ''),
				'error' => $error
			));
				
			// calculate next exec-time
			$nextExec = $cronjob->getNextExec();
			$cronjob->update(array(
				'nextExec' => $nextExec, 
				'afterNextExec' => $cronjob->getNextExec(($nextExec + 120))
			));
			
			// build the return value
			$dateTime = DateUtil::getDateTimeByTimestamp($nextExec);
			$return[$cronjob->cronjobID] = array(
				'time' => $nextExec,
				'formatted' => str_replace(
					'%time%', 
					DateUtil::format($dateTime, DateUtil::TIME_FORMAT), 
					str_replace(
						'%date%', 
						DateUtil::format($dateTime, DateUtil::DATE_FORMAT), 
						WCF::getLanguage()->get('wcf.global.date.dateTimeFormat')
					)
				)
			);
			
			// we are finished
			$cronjob->update(array('state' => Cronjob::READY));
		}
		
		return $return;
	}
}
