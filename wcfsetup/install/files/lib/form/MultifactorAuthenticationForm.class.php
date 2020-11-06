<?php
namespace wcf\form;
use wcf\data\object\type\ObjectType;
use wcf\data\user\User;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\user\multifactor\IMultifactorMethod;
use wcf\system\WCF;

/**
 * Represents the multifactor authentication form.
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
	 * @var ObjectType[]
	 */
	private $methods;
	
	/**
	 * @var ObjectType
	 */
	private $method;
	
	/**
	 * @var IMultifactorMethod
	 */
	private $processor;
	
	/**
	 * @var int
	 */
	private $setupId;
	
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
		
		$this->methods = $this->user->getEnabledMultifactorMethods();
		
		if (empty($this->methods)) {
			throw new \LogicException('Unreachable');
		}
		
		uasort($this->methods, function (ObjectType $a, ObjectType $b) {
			return $b->priority <=> $a->priority;
		});
		
		$this->setupId = array_keys($this->methods)[0];
		if (isset($_GET['id'])) {
			$this->setupId = $_GET['id'];
		}
		
		if (!isset($this->methods[$this->setupId])) {
			throw new IllegalLinkException();
		}
		
		$this->method = $this->methods[$this->setupId];
		assert($this->method->getDefinition()->definitionName === 'com.woltlab.wcf.multifactor');
		
		$this->processor = $this->method->getProcessor();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function createForm() {
		parent::createForm();
		
		$this->processor->createAuthenticationForm($this->form, $this->setupId);
	}
	
	public function save() {
		AbstractForm::save();
		
		WCF::getDB()->beginTransaction();
		
		$this->returnData = $this->processor->processAuthenticationForm($this->form, $this->setupId);
		
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
			'id' => $this->setupId,
		]));
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'method' => $this->method,
			'methods' => $this->methods,
			'user' => $this->user,
			'setupId' => $this->setupId,
		]);
	}
}
