<?php
namespace wcf\form;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IStorableObject;
use wcf\system\event\EventHandler;
use wcf\system\exception\UserInputException;
use wcf\system\form\builder\FormDocument;
use wcf\system\form\builder\IFormDocument;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Abstract implementation of a form using the form builder API.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 * @since	3.2
 */
abstract class AbstractFormBuilderForm extends AbstractForm {
	/**
	 * form document
	 * @var	IFormDocument
	 */
	public $form;
	
	/**
	 * action performed by the form
	 * by default `create` and `edit` is supported
	 * @var	string
	 */
	public $formAction = 'create';
	
	/**
	 * updated object, not relevant for form action `create`
	 * @var	IStorableObject
	 */
	public $formObject;
	
	/**
	 * name of the object action performing the form action
	 * if not set, `$formAction` is sued
	 * @var	null|string
	 */
	public $objectActionName;
	
	/**
	 * name of the object action class performing the form action
	 * @var	string
	 */
	public $objectActionClass;
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'action' => $this->formAction === 'create' ? 'add' : 'edit',
			'form' => $this->form
		]);
	}
	
	/**
	 * Builds the form.
	 */
	public function buildForm() {
		$this->createForm();
		
		EventHandler::getInstance()->fireAction($this, 'createForm');
		
		$this->form->build();
		
		$this->finalizeForm();
		
		EventHandler::getInstance()->fireAction($this, 'buildForm');
	}
	
	/**
	 * Creates the form object.
	 * 
	 * This is the method that is intended to be overwritten by child classes
	 * to add the form containers and fields.
	 */
	protected function createForm() {
		$classNamePieces = explode('\\', get_class($this));
		$controller = preg_replace('~Form$~', '', end($classNamePieces));
		
		$this->form = FormDocument::create(lcfirst($controller));
		
		if ($this->formObject !== null) {
			$this->form->formMode(IFormDocument::FORM_MODE_UPDATE);
		}
	}
	
	/**
	 * Finalizes the form after it has been successfully built.
	 * 
	 * This method can be used to add form field dependencies.
	 */
	protected function finalizeForm() {
		// does nothing
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		if ($this->formObject !== null) {
			$this->setFormObjectData();
		}
		else if ($this->formAction === 'edit') {
			throw new \UnexpectedValueException("Missing form object to update.");
		}
		
		parent::readData();
		
		$this->setFormAction();
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
	public function save() {
		parent::save();
		
		$action = $this->formAction;
		if ($this->objectActionName) {
			$action = $this->objectActionName;
		}
		else if ($this->formAction === 'edit') {
			$action = 'update';
		}
		
		/** @var AbstractDatabaseObjectAction objectAction */
		$this->objectAction = new $this->objectActionClass(
			array_filter([$this->formObject]),
			$action,
			$this->form->getData()
		);
		$this->objectAction->executeAction();
		
		$this->saved();
		
		// re-build form after having created a new object
		if ($this->formAction === 'create') {
			$this->buildForm();
		}
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * Sets the action of the form.
	 */
	protected function setFormAction() {
		$classNamePieces = explode('\\', get_class($this));
		$application = $classNamePieces[0];
		$controller = preg_replace('~Form$~', '', end($classNamePieces));
		
		$parameters = ['application' => $application];
		if ($this->formObject !== null) {
			if ($this->formObject instanceof IRouteController) {
				$parameters['object'] = $this->formObject;
			}
			else {
				$object = $this->formObject;
				
				$parameters['id'] = $object->{$object::getDatabaseTableIndexName()};
			}
		}
		
		$this->form->action(LinkHandler::getInstance()->getLink($controller, $parameters));
	}
	
	/**
	 * Sets the form data based on the current form object.
	 */
	protected function setFormObjectData() {
		$this->form->loadValuesFromObject($this->formObject);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		$this->buildForm();
		
		return parent::show();
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		$this->form->validate();
		
		if ($this->form->hasValidationErrors()) {
			throw new UserInputException($this->form->getPrefixedId());
		}
	}
}
