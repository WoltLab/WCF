<?php
namespace wcf\form;
use wcf\data\object\type\ObjectType;
use wcf\data\user\UserEditor;
use wcf\page\AccountSecurityPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\TemplateFormNode;
use wcf\system\menu\user\UserMenu;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\TReauthenticationCheck;
use wcf\system\user\multifactor\Setup;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Represents the multi-factor disable form.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 * @since	5.4
 */
class MultifactorDisableForm extends AbstractFormBuilderForm {
	use TReauthenticationCheck;
	
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @var ObjectType
	 */
	private $method;
	
	/**
	 * @var Setup
	 */
	private $setup;
	
	/**
	 * @var Setup[]
	 */
	private $setups;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!isset($_GET['id'])) {
			throw new IllegalLinkException();
		}
		
		$this->setups = Setup::getAllForUser(WCF::getUser());
		
		if (empty($this->setups)) {
			throw new IllegalLinkException();
		}
		
		if (!isset($this->setups[$_GET['id']])) {
			throw new IllegalLinkException();
		}
		
		$this->setup = $this->setups[$_GET['id']];
		$this->method = $this->setup->getObjectType();
		\assert($this->method->getDefinition()->definitionName === 'com.woltlab.wcf.multifactor');
		
		$this->requestReauthentication(LinkHandler::getInstance()->getControllerLink(static::class, [
			'object' => $this->setup,
		]));
		
		// Backup codes may not be disabled.
		if ($this->method->objectType === 'com.woltlab.wcf.multifactor.backup') {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function createForm() {
		parent::createForm();
		
		$this->form->appendChildren([
			TemplateFormNode::create('explanation')
				->templateName('__multifactorDisableExplanation')
				->variables([
					'remaining' =>	$this->setupsWithoutDisableRequest(
						$this->setupsWithoutBackupCodes($this->setups)
					),
					'setup' => $this->setup,
				]),
			BooleanFormField::create('confirm')
				->label('wcf.user.security.multifactor.disable.confirm', [
					'remaining' =>	$this->setupsWithoutDisableRequest(
						$this->setupsWithoutBackupCodes($this->setups)
					),
					'setup' => $this->setup,
				])
				->addValidator(new FormFieldValidator('confirm', function(BooleanFormField $formField) {
					if (!$formField->getValue()) {
						$formField->addValidationError(
							new FormFieldValidationError(
								'required',
								'wcf.user.security.multifactor.disable.confirm.required'
							)
						);
					}
				})),
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		WCF::getDB()->beginTransaction();
		
		$this->form->successMessage('wcf.user.security.multifactor.disable.success', [
			'setup' => $this->setup,
		]);
		$this->setup->delete();
		
		$setups = Setup::getAllForUser(WCF::getUser());
		$remaining = $this->setupsWithoutBackupCodes($setups);
		
		if (empty($remaining)) {
			foreach ($setups as $setup) {
				$setup->delete();
			}
			$this->disableMultifactorAuth();
			$this->form->successMessage('wcf.user.security.multifactor.disable.success.full');
		}
		
		WCF::getDB()->commitTransaction();
		
		$this->saved();
	}
	
	/**
	 * @inheritDoc
	 */
	public function saved() {
		AbstractForm::saved();
		
		HeaderUtil::delayedRedirect(
			LinkHandler::getInstance()
				->getControllerLink(AccountSecurityPage::class),
			$this->form->getSuccessMessage()
		);
		exit;
	}
	
	/**
	 * Returns the active setups without the backup codes.
	 * 
	 * @param Setup[] $setups
	 * @return Setup[]
	 */
	protected function setupsWithoutBackupCodes(array $setups): array {
		return array_filter($setups, function (Setup $setup) {
			return $setup->getObjectType()->objectType !== 'com.woltlab.wcf.multifactor.backup';
		});
	}
	
	/**
	 * Returns the active setups without the setup that is going to be disabled.
	 * 
	 * @param Setup[] $setups
	 * @return Setup[]
	 */
	protected function setupsWithoutDisableRequest(array $setups): array {
		return array_filter($setups, function (Setup $setup) {
			return $setup->getId() !== $this->setup->getId();
		});
	}
	
	/**
	 * Disables multifactor authentication for the user.
	 */
	protected function disableMultifactorAuth(): void {
		// This method intentionally does not use UserAction to prevent
		// events from firing.
		//
		// This method is being run from within a transaction to ensure
		// a consistent database state in case any part of the MFA setup
		// fails. Event listeners could run complex logic, including
		// queries that modify the database state, possibly leading to
		// a very large transaction and much more surface area for
		// unexpected failures.
		//
		// Use the saved@MultifactorDisableForm event if you need to run
		// logic in response to a user disabling MFA.
		$editor = new UserEditor(WCF::getUser());
		$editor->update([
			'multifactorActive' => 0,
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function setFormAction() {
		$this->form->action(LinkHandler::getInstance()->getControllerLink(static::class, [
			'object' => $this->setup,
		]));
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'method' => $this->method,
			'setups' => $this->setups,
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.profile.security');
		
		parent::show();
	}
}
