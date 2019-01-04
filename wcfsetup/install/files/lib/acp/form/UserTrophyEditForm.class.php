<?php
namespace wcf\acp\form;
use wcf\data\user\trophy\UserTrophy;
use wcf\data\user\trophy\UserTrophyAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Represents the user trophy edit form.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	3.1
 */
class UserTrophyEditForm extends UserTrophyAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.userTrophy.list';
	
	/**
	 * @inheritDoc
	 */
	public $action = 'edit';
	
	/**
	 * user trophy id
	 * @var int
	 */
	public $userTrophyID = 0;
	
	/**
	 * user trophy object
	 * @var UserTrophy
	 */
	public $userTrophy;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		if (!empty($_REQUEST['id'])) $this->userTrophyID = intval($_REQUEST['id']);
		$this->userTrophy = new UserTrophy($this->userTrophyID);
		
		if (!$this->userTrophy->userTrophyID) {
			throw new IllegalLinkException();
		}
		
		if ($this->userTrophy->getTrophy()->awardAutomatically) {
			throw new IllegalLinkException(); 
		}
		
		parent::readParameters();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		// the user can't change these values
		$this->trophyID = $this->userTrophy->trophyID;
		$this->trophy = $this->userTrophy->getTrophy(); 
		$this->userIDs = [$this->userTrophy->userID];
		$this->user = $this->userTrophy->getUserProfile()->getUsername();
		
		if (empty($_POST)) {
			$this->readDataI18n($this->userTrophy);
			
			$this->useCustomDescription = $this->userTrophy->useCustomDescription;
			$this->trophyUseHtml = $this->userTrophy->trophyUseHtml;
		}
		
		parent::readData();
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractAcpForm::save();
		
		$this->beforeSaveI18n($this->userTrophy);
		
		$this->objectAction = new UserTrophyAction([$this->userTrophy], 'update', [
			'data' => array_merge($this->additionalFields, [
				'useCustomDescription' => $this->useCustomDescription, 
				'description' => $this->description,
				'trophyUseHtml' => $this->trophyUseHtml
			])	
		]);
		$this->objectAction->executeAction(); 
		
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign([
			'userTrophy' => $this->userTrophy
		]);
	}
}
