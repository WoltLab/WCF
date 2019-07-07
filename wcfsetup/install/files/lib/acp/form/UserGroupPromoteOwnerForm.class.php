<?php
namespace wcf\acp\form;
use wcf\data\user\group\UserGroup;
use wcf\data\user\group\UserGroupAction;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\RadioButtonFormField;
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
class UserGroupPromoteOwnerForm extends AbstractFormBuilderForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.group.list';
	
	/**
	 * user groups that can be promoted to owner group
	 * @var	UserGroup[]
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
		
		// owner user groups cannot be modified
		if (UserGroup::getOwnerGroupID() !== null) {
			throw new IllegalLinkException();
		}
		
		$this->groups = UserGroup::getGroupsByType([UserGroup::OTHER]);
		$this->groups = array_filter($this->groups, function (UserGroup $group) {
			return $group->isAdminGroup();
		});
		
		if (empty($this->groups)) {
			// fallback for broken installations without an admin group
			$this->groups = UserGroup::getGroupsByType([UserGroup::OTHER]);
		}
		
		uasort($this->groups, function(UserGroup $a, UserGroup $b) {
			return $a->getName() <=> $b->getName();
		});
	}
	
	/**
	 * @inheritDoc
	 */
	protected function createForm() {
		parent::createForm();
		
		$this->form->appendChild(
			FormContainer::create('groupSection')
				->appendChild(
					RadioButtonFormField::create('groupID')
						->label('wcf.acp.group.promoteOwner.group')
						->required()
						->options($this->groups)
				)
		);
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		$groupID = $this->form->getData()['data']['groupID'];
		
		$this->objectAction = new UserGroupAction([$this->groups[$groupID]], 'promoteOwner');
		$this->objectAction->executeAction();
		
		AbstractForm::saved();
		
		HeaderUtil::redirect(LinkHandler::getInstance()->getLink());
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			// Hide the notice on this page only.
			'__wscMissingOwnerGroup' => false,
		]);
	}
}
