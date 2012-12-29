<?php
namespace wcf\data\cronjob;
use wcf\data\cronjob\log\CronjobLogEditor;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\system\cronjob\CronjobScheduler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Executes cronjob-related actions.
 * 
 * @author	Tim Düsterhus, Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.cronjob
 * @category	Community Framework
 */
class CronjobAction extends AbstractDatabaseObjectAction implements IToggleAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\cronjob\CronjobEditor';
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.system.canManageCronjob');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.system.canManageCronjob');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.system.canManageCronjob');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('executeCronjobs');
	
	/**
	 * @see	wcf\data\IDeleteAction::validateDelete()
	 */
	public function validateDelete() {
		parent::validateDelete();
		
		foreach ($this->objects as $cronjob) {
			if (!$cronjob->isDeletable()) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @see	wcf\data\AbstractDatabaseAction::validateUpdate()
	 */
	public function validateUpdate() {
		parent::validateUpdate();
		
		foreach ($this->objects as $cronjob) {
			if (!$cronjob->isEditable()) {
				throw new PermissionDeniedException();
			} 
		}
	}
	
	/**
	 * @see	wcf\data\IToggleAction::validateToggle()
	 */
	public function validateToggle() {
		parent::validateUpdate();
		
		foreach ($this->objects as $cronjob) {
			if (!$cronjob->canBeDisabled()) {
				throw new PermissionDeniedException();
			} 
		}
	}
	
	/**
	 * @see	wcf\data\IToggleAction::toggle()
	 */
	public function toggle() {
		foreach ($this->objects as $cronjob) {
			$cronjob->update(array(
				'isDisabled' => $cronjob->isDisabled ? 0 : 1
			));
		}
	}
	
	/**
	 * Validates the 'execute' action.
	 */
	public function validateExecute() {
		// TODO: Fix this: We need update permissions for executing?
		parent::validateUpdate();
	}
	
	/**
	 * Executes cronjobs.
	 */
	public function execute() {
		$return = array();
		
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
				'success' => (int) ($error == ''),
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
						WCF::getLanguage()->get('wcf.date.dateTimeFormat')
					)
				)
			);
			
			// we are finished
			$cronjob->update(array('state' => Cronjob::READY));
		}
		
		return $return;
	}
	
	/**
	 * Validates the 'executeCronjobs' action.
	 */
	public function validateExecuteCronjobs() {
		// does nothing
	}
	
	/**
	 * Executes open cronjobs.
	 */
	public function executeCronjobs() {
		CronjobScheduler::getInstance()->executeCronjobs();
	}
}
