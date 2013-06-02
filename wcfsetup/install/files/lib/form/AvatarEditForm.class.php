<?php
namespace wcf\form;
use wcf\data\user\avatar\Gravatar;
use wcf\data\user\avatar\UserAvatarAction;
use wcf\data\user\UserEditor;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\menu\user\UserMenu;
use wcf\system\WCF;

/**
 * Shows the avatar edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	form
 * @category	Community Framework
 */
class AvatarEditForm extends AbstractForm {
	/**
	 * @see	wcf\page\AbstractPage::$enableTracking
	 */
	public $enableTracking = true;
	
	/**
	 * @see	wcf\page\AbstractPage::$loginRequired
	 */
	public $loginRequired = true;
	
	/**
	 * @see	wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'avatarEdit';
	
	/**
	 * avatar type
	 * @var	string
	 */
	public $avatarType = 'none';
	
	/**
	 * @see	wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['avatarType'])) $this->avatarType = $_POST['avatarType'];
	}
	
	/**
	 * @see	wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (WCF::getUser()->disableAvatar) throw new PermissionDeniedException();
		
		if ($this->avatarType != 'custom' && $this->avatarType != 'gravatar') $this->avatarType = 'none';
		
		switch ($this->avatarType) {
			case 'custom':
				if (!WCF::getUser()->avatarID) {
					throw new UserInputException('custom');
				}
			break;
				
			case 'gravatar':
				if (!MODULE_GRAVATAR) {
					$this->avatarType = 'none';
					break;
				}
				
				// test gravatar
				if (!Gravatar::test(WCF::getUser()->email)) {
					throw new UserInputException('gravatar', 'notFound');
				}
			break;
		}
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		if ($this->avatarType != 'custom') {
			// delete custom avatar
			if (WCF::getUser()->avatarID) {
				$action = new UserAvatarAction(array(WCF::getUser()->avatarID), 'delete');
				$action->executeAction();
			}
		}
		
		// update user
		switch ($this->avatarType) {
			case 'none':
				$editor = new UserEditor(WCF::getUser());
				$editor->update(array(
					'avatarID' => null,
					'enableGravatar' => 0
				));
			break;
				
			case 'custom':
				$editor = new UserEditor(WCF::getUser());
				$editor->update(array(
					'enableGravatar' => 0
				));
			break;
				
			case 'gravatar':
				$editor = new UserEditor(WCF::getUser());
				$editor->update(array(
					'avatarID' => null,
					'enableGravatar' => 1
				));
			break;
		}
		
		$this->saved();
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			if (WCF::getUser()->avatarID) $this->avatarType = 'custom';
			else if (MODULE_GRAVATAR && WCF::getUser()->enableGravatar) $this->avatarType = 'gravatar';
		}
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'avatarType' => $this->avatarType
		));
	}
	
	/**
	 * @see	wcf\page\IPage::show()
	 */
	public function show() {
		// set active tab
		UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.profile.avatar');
		
		parent::show();
	}
}
