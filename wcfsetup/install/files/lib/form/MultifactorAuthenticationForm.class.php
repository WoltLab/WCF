<?php
namespace wcf\form;
use wcf\data\object\type\ObjectType;
use wcf\data\user\User;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\user\multifactor\IMultifactorMethod;
use wcf\system\user\multifactor\Setup;
use wcf\system\WCF;

/**
 * Represents the multi-factor authentication form.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 * @since	5.4
 */
class MultifactorAuthenticationForm extends AbstractFormBuilderForm {
	const AVAILABLE_DURING_OFFLINE_MODE = true;
	
	/**
	 * @inheritDoc
	 */
	public $formAction = 'authenticate';
	
	/**
	 * @var User
	 */
	private $user;
	
	/**
	 * @var Setup[]
	 */
	private $setups;
	
	/**
	 * @var ObjectType
	 */
	private $method;
	
	/**
	 * @var IMultifactorMethod
	 */
	private $processor;
	
	/**
	 * @var Setup
	 */
	private $setup;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$userId = WCF::getSession()->getVar('__changeUserAfterMultifactor__');
		if (!$userId) {
			throw new PermissionDeniedException();
		}
		$this->user = new User($userId);
		if (!$this->user->userID) {
			throw new PermissionDeniedException();
		}
		
		$this->setups = Setup::getAllForUser($this->user);
		
		if (empty($this->setups)) {
			throw new \LogicException('Unreachable');
		}
		
		\uasort($this->setups, function (Setup $a, Setup $b) {
			return $b->getObjectType()->priority <=> $a->getObjectType()->priority;
		});
		
		$setupId = \array_keys($this->setups)[0];
		if (isset($_GET['id'])) {
			$setupId = $_GET['id'];
		}
		
		if (!isset($this->setups[$setupId])) {
			throw new IllegalLinkException();
		}
		
		$this->setup = $this->setups[$setupId];
		$this->method = $this->setup->getObjectType();
		\assert($this->method->getDefinition()->definitionName === 'com.woltlab.wcf.multifactor');
		
		$this->processor = $this->method->getProcessor();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function createForm() {
		parent::createForm();
		
		$this->processor->createAuthenticationForm($this->form, $this->setup);
	}
	
	public function save() {
		AbstractForm::save();
		
		WCF::getDB()->beginTransaction();
		
		$setup = $this->setup->lock();
		
		$this->returnData = $this->processor->processAuthenticationForm($this->form, $setup);
		
		WCF::getDB()->commitTransaction();
		
		WCF::getSession()->changeUser($this->user);
		
		$this->saved();
	}
	
	/**
	 * @inheritDoc
	 */
	public function saved() {
		AbstractForm::saved();
		
		$this->form->cleanup();
		$this->buildForm();
		
		// TODO: Proper success message and hiding of the form.
		$this->form->showSuccessMessage(true);
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
			'setups' => $this->setups,
			'user' => $this->user,
			'setup' => $this->setup,
		]);
	}
}
