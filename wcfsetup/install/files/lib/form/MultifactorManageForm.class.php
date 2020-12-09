<?php
namespace wcf\form;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserEditor;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\background\BackgroundQueueHandler;
use wcf\system\email\SimpleEmail;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\FormDocument;
use wcf\system\form\builder\IFormParentNode;
use wcf\system\form\builder\TemplateFormNode;
use wcf\system\menu\user\UserMenu;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\TReauthenticationCheck;
use wcf\system\user\multifactor\IMultifactorMethod;
use wcf\system\user\multifactor\Setup;
use wcf\system\WCF;

/**
 * Represents the multi-factor setup form.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 * @since	5.4
 */
class MultifactorManageForm extends AbstractFormBuilderForm {
	use TReauthenticationCheck;
	
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @inheritDoc
	 */
	public $formAction = 'setup';
	
	/**
	 * @var ObjectType
	 */
	private $method;
	
	/**
	 * @var IMultifactorMethod
	 */
	private $processor;
	
	/**
	 * @var ?Setup
	 */
	private $setup;
	
	/**
	 * @var mixed
	 */
	private $returnData = null;
	
	/**
	 * @var IFormDocument
	 */
	private $backupForm;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!isset($_GET['id'])) {
			throw new IllegalLinkException();
		}
		
		$objectType = ObjectTypeCache::getInstance()->getObjectType(intval($_GET['id']));
		
		if (!$objectType) {
			throw new IllegalLinkException();
		}
		if ($objectType->getDefinition()->definitionName !== 'com.woltlab.wcf.multifactor') {
			throw new IllegalLinkException();
		}
		
		$this->method = $objectType;
		$this->processor = $this->method->getProcessor();
		$this->setup = Setup::find($this->method, WCF::getUser());
		
		$this->requestReauthentication(LinkHandler::getInstance()->getControllerLink(static::class, [
			'object' => $this->method,
		]));
		
		// Backup codes may not be managed if they are not yet set up.
		if ($this->method->objectType === 'com.woltlab.wcf.multifactor.backup' && !$this->setup) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function createForm() {
		parent::createForm();
		
		$this->processor->createManagementForm($this->form, $this->setup, $this->returnData);
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		WCF::getDB()->beginTransaction();
		
		/** @var Setup|null $setup */
		$setup = null;
		if ($this->setup) {
			$setup = $this->setup->lock();
		}
		else {
			$setup = Setup::allocateSetUpId($this->method, WCF::getUser());
		}
		
		if (!$setup) {
			throw new \RuntimeException("Multifactor setup disappeared");
		}
		
		$this->returnData = $this->processor->processManagementForm($this->form, $setup);
		
		$this->setup = $setup;
		
		$sendEmail = false;
		if (!$this->hasBackupCodes()) {
			$this->generateBackupCodes();
			$sendEmail = true;
		}
		
		$this->enableMultifactorAuth();
		
		WCF::getDB()->commitTransaction();
		
		// Send the email outside of the transaction.
		// 
		// Sending the email is not an absolute requirement and queueing some external
		// process that might need to be rolled back is a bit wonky from an UX perspective.
		if ($sendEmail) {
			$this->sendEmail();
		}
		
		$this->saved();
	}
	
	/**
	 * Returns the Object type representing backup codes.
	 */
	protected function getBackupCodesObjectType(): ObjectType {
		return ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.multifactor', 'com.woltlab.wcf.multifactor.backup');
	}
	
	/**
	 * Returns whether backup codes are set up yet.
	 */
	protected function hasBackupCodes(): bool {
		$setup = Setup::find($this->getBackupCodesObjectType(), WCF::getUser());
		
		return $setup !== null;
	}
	
	/**
	 * Generates the backup codes after initial setup.
	 */
	protected function generateBackupCodes(): void {
		$backupMethod = $this->getBackupCodesObjectType();
		$backupProcessor = $backupMethod->getProcessor();

		// Create Form
		$form = FormDocument::create('backupCodes');
		$backupProcessor->createManagementForm($form, null, []);
		$form->build();
		
		// Process Form
		$form->requestData([
			'generateCodes' => 'generateCodes',
		]);
		$form->readValues();
		$backupSetupId = Setup::allocateSetUpId($backupMethod, WCF::getUser());
		$returnData = $backupProcessor->processManagementForm($form, $backupSetupId);
		$form->cleanup();
		
		// Re-create form
		$form = FormDocument::create('backupCodes');
		$backupProcessor->createManagementForm($form, $backupSetupId, $returnData);
		/** @var IFormParentNode $container */
		$container = $form->getNodeById('existingCodesContainer');
		$container->insertBefore(
			TemplateFormNode::create('initialBackup')
				->templateName('__multifactorManageInitialBackup'),
			'existingCodes'
		);
		$form->build();
		$this->backupForm = $form;
	}
	
	/**
	 * Enables multi-factor authentication for the user.
	 */
	protected function enableMultifactorAuth(): void {
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
		// Use the saved@MultifactorManageForm event if you need to run
		// logic in response to a user enabling MFA.
		$editor = new UserEditor(WCF::getUser());
		$editor->update([
			'multifactorActive' => 1,
		]);
	}
	
	/**
	 * Sends an email letting the user know that multi-factor authentication
	 * is enabled now.
	 */
	protected function sendEmail(): void {
		$email = new SimpleEmail();
		$email->setRecipient(WCF::getUser());
		
		$email->setSubject(
			WCF::getLanguage()->getDynamicVariable('wcf.user.security.multifactor.setupEmail.subject', [
				'user' => WCF::getUser(),
				'method' => $this->method,
				'backupMethod' => $this->getBackupCodesObjectType(),
			])
		);
		$email->setHtmlMessage(
			WCF::getLanguage()->getDynamicVariable('wcf.user.security.multifactor.setupEmail.body.html', [
				'user' => WCF::getUser(),
				'method' => $this->method,
				'backupMethod' => $this->getBackupCodesObjectType(),
			])
		);
		$email->setMessage(
			WCF::getLanguage()->getDynamicVariable('wcf.user.security.multifactor.setupEmail.body.plain', [
				'user' => WCF::getUser(),
				'method' => $this->method,
				'backupMethod' => $this->getBackupCodesObjectType(),
			])
		);
		
		$jobs = $email->getEmail()->getJobs();
		foreach ($jobs as $job) {
			// Wait 15 minutes to give the user a bit of time to complete the process of
			// storing their backup codes somewhere without distracting them by causing
			// a notification to arrive.
			// It also allows us to remind the user of the importance of the backup codes
			// in case they simply close the tab without reading, similar to remarketing
			// emails.
			BackgroundQueueHandler::getInstance()->enqueueIn(
				$job,
				15 * 60
			);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function saved() {
		AbstractForm::saved();
		
		$this->form->cleanup();
		$this->buildForm();
		
		$this->form->showSuccessMessage(true);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function setFormAction() {
		$this->form->action(LinkHandler::getInstance()->getControllerLink(static::class, [
			'object' => $this->method,
		]));
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'method' => $this->method,
			'backupForm' => $this->backupForm,
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
