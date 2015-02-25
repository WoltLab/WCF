<?php
namespace wcf\data\smiley;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes smiley-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.smiley
 * @category	Community Framework
 */
class SmileyAction extends AbstractDatabaseObjectAction implements ISortableAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\smiley\SmileyEditor';
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.content.smiley.canManageSmiley');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.content.smiley.canManageSmiley');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('delete', 'update', 'updatePosition');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::create()
	 */
	public function create() {
		$smiley = parent::create();
		
		if (!empty($this->parameters['fileLocation'])) {
			$smileyFilename = 'smiley'.$smiley->smileyID.'.'.mb_strtolower(mb_substr($this->parameters['fileLocation'], mb_strrpos($this->parameters['fileLocation'], '.') + 1));
			@rename($this->parameters['fileLocation'], WCF_DIR.'images/smilies/'.$smileyFilename);
			
			$smileyEditor = new SmileyEditor($smiley);
			$smileyEditor->update(array(
				'smileyPath' => 'images/smilies/'.$smileyFilename
			));
			
			$smiley = new Smiley($smiley->smileyID);
		}
		
		return $smiley;
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::update()
	 */
	public function update() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		if (count($this->objects) == 1 && !empty($this->parameters['fileLocation'])) {
			$smiley = reset($this->objects);
			$smileyFilename = 'smiley'.$smiley->smileyID.'.'.mb_strtolower(mb_substr($this->parameters['fileLocation'], mb_strrpos($this->parameters['fileLocation'], '.') + 1));
			@rename($this->parameters['fileLocation'], WCF_DIR.'images/smilies/'.$smileyFilename);
			
			$this->parameters['data']['smileyPath'] = 'images/smilies/'.$smileyFilename;
		}
		
		parent::update();
	}
	
	/**
	 * @see	\wcf\data\ISortableAction::validateUpdatePosition()
	 */
	public function validateUpdatePosition() {
		// validate permissions
		if (is_array($this->permissionsUpdate) && count($this->permissionsUpdate)) {
			WCF::getSession()->checkPermissions($this->permissionsUpdate);
		}
		else {
			throw new PermissionDeniedException();
		}
		
		if (!isset($this->parameters['data']['structure'])) {
			throw new UserInputException('structure');
		}
		
		$this->readInteger('offset', true, 'data');
	}
	
	/**
	 * @see	\wcf\data\ISortableAction::updatePosition()
	 */
	public function updatePosition() {
		$smileyList = new SmileyList();
		$smileyList->readObjects();
		
		$i = $this->parameters['data']['offset'];
		WCF::getDB()->beginTransaction();
		foreach ($this->parameters['data']['structure'][0] as $smileyID) {
			$smiley = $smileyList->search($smileyID);
			if ($smiley === null) continue;
			
			$editor = new SmileyEditor($smiley);
			$editor->update(array('showOrder' => $i++));
		}
		WCF::getDB()->commitTransaction();
	}
}
