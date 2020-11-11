<?php
namespace wcf\form;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\menu\user\UserMenu;
use wcf\system\request\LinkHandler;
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
	private $returnData;
	
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
	}
	
	/**
	 * @inheritDoc
	 */
	protected function createForm() {
		parent::createForm();
		
		$this->processor->createManagementForm($this->form, $this->setup, $this->returnData);
	}
	
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
		WCF::getDB()->commitTransaction();
		
		$this->saved();
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
			'id' => $this->method->objectTypeID,
		]));
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'method' => $this->method,
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
