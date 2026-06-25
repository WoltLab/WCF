<?php

namespace wcf\form;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\IStorableObject;
use wcf\system\event\EventHandler;
use wcf\system\exception\UserInputException;
use wcf\system\form\builder\DatabaseObjectBuilderFormDocument;
use wcf\system\form\builder\IFormDocument;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Abstract implementation of a form using the form builder API that persists
 * its data through a `DatabaseObjectBuilder` and a command instead of a
 * database object action.
 *
 * Deriving classes provide the builder via `getDatabaseObjectBuilder()`. The
 * form's fields write their save values into the builder by registering a
 * callback via `IFormField::saveValueCallback()`. Saving is performed by the
 * command returned by `getCommand()`, which defaults to `SaveDatabaseObject`.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @template TIStorableObject of IStorableObject|null
 * @template TDatabaseObjectBuilder of DatabaseObjectBuilder
 */
abstract class AbstractDatabaseObjectBuilderForm extends AbstractForm
{
    public DatabaseObjectBuilderFormDocument $form;

    /**
     * Action performed by the form by default `create` and `edit` is supported.
     */
    public string $formAction = 'create';

    /**
     * updated object, not relevant for form action `create`
     * @var ?TIStorableObject
     */
    public ?IStorableObject $formObject = null;

    /**
     * name of the controller for the link to the edit form
     */
    public string $objectEditLinkController = '';

    /**
     * object persisted by the most recent `save()` call
     */
    public ?DatabaseObject $object = null;

    /**
     * Returns the builder used to persist the form data.
     *
     * For the `create` action a builder obtained via `forCreate()` is expected,
     * for the `edit` action a builder obtained via `forUpdate($this->formObject)`.
     *
     * @return TDatabaseObjectBuilder
     */
    abstract protected function getDatabaseObjectBuilder(): DatabaseObjectBuilder;

    /**
     * Returns the invokable command that persists the given builder and returns
     * the resulting database object.
     *
     * The default command simply calls `DatabaseObjectBuilder::save()`. Override
     * this method to wrap saving in a command that performs additional side
     * effects.
     *
     * @param TDatabaseObjectBuilder $builder
     * @return callable(): DatabaseObject
     */
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        return static fn() => $builder->save();
    }

    #[\Override]
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
    public function buildForm(): void
    {
        $classNamePieces = \explode('\\', static::class);
        $controller = \preg_replace('~Form$~', '', \end($classNamePieces));

        $this->form = DatabaseObjectBuilderFormDocument::create(\lcfirst($controller));

        if ($this->formObject !== null) {
            $this->form->formMode(IFormDocument::FORM_MODE_UPDATE);
        }

        $this->createForm();

        EventHandler::getInstance()->fireAction($this, 'createForm');

        $this->form->build();

        $this->finalizeForm();

        EventHandler::getInstance()->fireAction($this, 'buildForm');
    }

    /**
     * Creates the form.
     *
     * This is the method that is intended to be overwritten by child classes
     * to add the form containers and fields.
     */
    protected function createForm(): void
    {
        // does nothing
    }

    /**
     * Finalizes the form after it has been successfully built.
     *
     * This method can be used to add form field dependencies.
     */
    protected function finalizeForm(): void
    {
        // does nothing
    }

    #[\Override]
    public function readData(): void
    {
        if ($this->formObject !== null) {
            $this->setFormObjectData();
        } elseif ($this->formAction === 'edit') {
            throw new \UnexpectedValueException("Missing form object to update.");
        }

        parent::readData();

        $this->setFormAction();
    }

    #[\Override]
    public function readFormParameters(): void
    {
        parent::readFormParameters();

        $this->form->readValues();
    }

    #[\Override]
    public function save(): void
    {
        parent::save();

        $builder = $this->getDatabaseObjectBuilder();
        $this->form->applyValuesToBuilder($builder);

        foreach ($this->additionalFields as $name => $value) {
            $builder->setCustomProperty($name, $value);
        }

        $this->object = ($this->getCommand($builder))();

        $this->saved();

        WCF::getTPL()->assign('success', true);

        if ($this->formAction === 'create' && $this->objectEditLinkController) {
            WCF::getTPL()->assign(
                'objectEditLink',
                LinkHandler::getInstance()->getControllerLink($this->objectEditLinkController, [
                    'id' => $this->object->getObjectID(),
                ])
            );
        }
    }

    #[\Override]
    public function saved(): void
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
    protected function setFormAction(): void
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
     */
    protected function setFormObjectData(): void
    {
        $this->form->updatedObject($this->formObject, empty($_POST));
    }

    #[\Override]
    public function checkPermissions(): void
    {
        parent::checkPermissions();

        $this->buildForm();
    }

    #[\Override]
    public function validate(): void
    {
        parent::validate();

        $this->form->validate();

        if ($this->form->hasValidationErrors()) {
            throw new UserInputException($this->form->getPrefixedId());
        }
    }

    #[\Override]
    protected function validateSecurityToken(): void
    {
        // does nothing, is handled by `IFormDocument` object
    }
}
