<?php

namespace wcf\form;

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
 * @since   5.2
 *
 * @template TIStorableObject of IStorableObject|null
 */
abstract class AbstractFormBuilderForm extends AbstractForm
{
    /**
     * @var IFormDocument
     */
    public $form;

    /**
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
     * @var ?TIStorableObject
     */
    public $formObject;

    /**
     * name of the object action performing the form action
     * if not set, `$formAction` is used
     * @var ?string
     */
    public $objectActionName;

    /**
     * name of the object action class performing the form action
     *
     * Note: Do not insert `AbstractDatabaseObjectAction` in the class-string,
     * the generics will be extremely annoying in deriving classes.
     *
     * @var class-string
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
     * @deprecated 6.2 No longer in use.
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
     *
     * @return void
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
     *
     * @return void
     */
    protected function createForm()
    {
        $classNamePieces = \explode('\\', static::class);
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
     *
     * @return void
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
     *
     * @return void
     */
    protected function setFormAction()
    {
        $parameters = [];
        if ($this->formObject !== null) {
            if ($this->formObject instanceof IRouteController) {
                $parameters['object'] = $this->formObject;
            } else {
                $object = $this->formObject;
                // @phpstan-ignore function.alreadyNarrowedType, instanceof.alwaysTrue
                \assert($object instanceof IStorableObject);

                $parameters['id'] = $object->{$object::getDatabaseTableIndexName()};
            }
        }

        $this->form->action(LinkHandler::getInstance()->getControllerLink(static::class, $parameters));
    }

    /**
     * Sets the form data based on the current form object.
     *
     * @return void
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
