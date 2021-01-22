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
 * @author  Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Form
 * @since   5.2
 */
abstract class AbstractFormBuilderForm extends AbstractForm
{
    /**
     * form document
     * @var IFormDocument
     */
    public $form;

    /**
     * name of the form document class
     * @var string
     */
    public $formClassName = FormDocument::class;

    /**
     * action performed by the form
     * by default `create` and `edit` is supported
     * @var string
     */
    public $formAction = 'create';

    /**
     * updated object, not relevant for form action `create`
     * @var IStorableObject
     */
    public $formObject;

    /**
     * name of the object action performing the form action
     * if not set, `$formAction` is used
     * @var null|string
     */
    public $objectActionName;

    /**
     * name of the object action class performing the form action
     * @var string
     */
    public $objectActionClass;

    /**
     * name of the controller for the link to the edit form
     * @var string
     * @since 5.3
     */
    public $objectEditLinkController = '';

    /**
     * name of the application for the link to the edit form
     * @var string
     * @since 5.3
     */
    public $objectEditLinkApplication = 'wcf';

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'action' => $this->formAction === 'create' ? 'add' : 'edit',
            'form' => $this->form,
            'formObject' => $this->formObject,
        ]);
    }

    /**
     * Builds the form.
     */
    public function buildForm()
    {
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
    protected function createForm()
    {
        $classNamePieces = \explode('\\', \get_class($this));
        $controller = \preg_replace('~Form$~', '', \end($classNamePieces));

        $this->form = $this->formClassName::create(\lcfirst($controller));

        if ($this->formObject !== null) {
            $this->form->formMode(IFormDocument::FORM_MODE_UPDATE);
        }
    }

    /**
     * Finalizes the form after it has been successfully built.
     *
     * This method can be used to add form field dependencies.
     */
    protected function finalizeForm()
    {
        // does nothing
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        if ($this->formObject !== null) {
            $this->setFormObjectData();
        } elseif ($this->formAction === 'edit') {
            throw new \UnexpectedValueException("Missing form object to update.");
        }

        parent::readData();

        $this->setFormAction();
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        $this->form->readValues();
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        $action = $this->formAction;
        if ($this->objectActionName) {
            $action = $this->objectActionName;
        } elseif ($this->formAction === 'edit') {
            $action = 'update';
        }

        $formData = $this->form->getData();
        if (!isset($formData['data'])) {
            $formData['data'] = [];
        }
        $formData['data'] = \array_merge($this->additionalFields, $formData['data']);

        /** @var AbstractDatabaseObjectAction objectAction */
        $this->objectAction = new $this->objectActionClass(
            \array_filter([$this->formObject]),
            $action,
            $formData
        );
        $this->objectAction->executeAction();

        $this->saved();

        WCF::getTPL()->assign('success', true);

        if ($this->formAction === 'create' && $this->objectEditLinkController) {
            WCF::getTPL()->assign(
                'objectEditLink',
                LinkHandler::getInstance()->getControllerLink($this->objectEditLinkController, [
                    'application' => $this->objectEditLinkApplication,
                    'id' => $this->objectAction->getReturnValues()['returnValues']->getObjectID(),
                ])
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function saved()
    {
        parent::saved();

        // re-build form after having created a new object
        if ($this->formAction === 'create') {
            $this->form->cleanup();

            $this->buildForm();
        }

        $this->form->showSuccessMessage(true);
    }

    /**
     * Sets the action of the form.
     */
    protected function setFormAction()
    {
        $parameters = [];
        if ($this->formObject !== null) {
            if ($this->formObject instanceof IRouteController) {
                $parameters['object'] = $this->formObject;
            } else {
                $object = $this->formObject;

                $parameters['id'] = $object->{$object::getDatabaseTableIndexName()};
            }
        }

        $this->form->action(LinkHandler::getInstance()->getControllerLink(static::class, $parameters));
    }

    /**
     * Sets the form data based on the current form object.
     */
    protected function setFormObjectData()
    {
        $this->form->updatedObject($this->formObject, empty($_POST));
    }

    /**
     * @inheritDoc
     */
    public function checkPermissions()
    {
        parent::checkPermissions();

        $this->buildForm();
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        $this->form->validate();

        if ($this->form->hasValidationErrors()) {
            throw new UserInputException($this->form->getPrefixedId());
        }
    }

    /**
     * @inheritDoc
     */
    protected function validateSecurityToken()
    {
        // does nothing, is handled by `IFormDocument` object
    }
}
