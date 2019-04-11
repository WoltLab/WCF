<?php
namespace wcf\acp\form;
use wcf\data\user\group\UserGroup;
use wcf\data\user\group\UserGroupAction;
use wcf\form\AbstractForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\FormDocument;
use wcf\system\form\builder\IFormDocument;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Promotes a user group to be the owner group.
 * 
 * @author      ALexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Acp\Form
 * @since       5.2
 */
class UserGroupPromoteOwnerForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.group.list';
	
	/**
	 * @var IFormDocument
	 */
	public $form;
	
	/**
	 * @var UserGroup
	 */
	public $groups = [];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.package.canInstallPackage'];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->groups = UserGroup::getGroupsByType([UserGroup::OTHER]);
		$this->groups = array_filter($this->groups, function (UserGroup $group) {
			return $group->isAdminGroup();
		});
		uasort($this->groups, function(UserGroup $a, UserGroup $b) {
			return $a->getName() <=> $b->getName();
		});
		
		$this->form = FormDocument::create('promoteGroup')
			->appendChild(
				FormContainer::create('groupSection')
					->appendChild(
						RadioButtonFormField::create('groupID')
							->label('wcf.acp.group.promoteOwner.group')
							->required()
							->options($this->groups)
					)
			);
		$this->form->action(LinkHandler::getInstance()->getLink('UserGroupPromoteOwner'));
		$this->form->build();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->form->readValues();
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		$this->form->validate();
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$groupID = $this->form->getData()['data']['groupID'];
		
		$this->objectAction = new UserGroupAction([$this->groups[$groupID]], 'promoteOwner');
		$this->objectAction->executeAction();
		
		$this->saved();
		
		HeaderUtil::redirect(LinkHandler::getInstance()->getLink());
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'form' => $this->form,
			// Hide the notice on this page only.
			'__wscMissingOwnerGroup' => false,
		]);
	}
}
