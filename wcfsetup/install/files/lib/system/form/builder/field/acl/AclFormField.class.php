<?php

namespace wcf\system\form\builder\field\acl;

use wcf\data\IStorableObject;
use wcf\system\acl\ACLHandler;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\IObjectTypeFormNode;
use wcf\system\form\builder\TObjectTypeFormNode;

/**
 * Implementation of a form field for setting acl option values.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class AclFormField extends AbstractFormField implements IObjectTypeFormNode
{
    use TObjectTypeFormNode;

    /**
     * name of/filter for the name(s) of the shown acl option categories
     */
    protected ?string $categoryName = null;

    /**
     * @inheritDoc
     * @since   5.2.3
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Acl';

    /**
     * id of the edited object or `null` if no object is edited
     */
    protected ?int $objectID = null;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_aclFormField';

    /**
     * is `true` if acl-related global JavaScript code has already been included
     * and is `false` otherwise
     */
    protected static bool $includedAclJavaScript = false;

    /**
     * Sets the name of/filter for the name(s) of the shown acl option categories and
     * returns this field.
     *
     * The category name supports a `.*` suffix for filtering by multiple categories,
     * so that `user.*` matches all acl option categories beginning with `user.`, for example.
     *
     * @param string $categoryName name of/filter for the acl option categories
     *
     * @throws  \InvalidArgumentException   if given category name is invalid
     */
    public function categoryName(string $categoryName): static
    {
        if (
            !\is_string($categoryName)
            || !\preg_match('~^[A-Za-z0-9\-\_]+((\.[A-Za-z0-9\-\_]+)+|(\.[A-Za-z0-9\-\_]+)*?\.\*)$~', $categoryName)
        ) {
            throw new \InvalidArgumentException("Invalid category name given for field '{$this->getId()}'.");
        }

        $this->categoryName = $categoryName;

        return $this;
    }

    /**
     * Returns the name of/filter for the name(s) of the shown acl option categories
     * or returns `null` if no category name has been set.
     */
    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    #[\Override]
    public function getHtmlVariables(): array
    {
        ACLHandler::getInstance()->assignVariables($this->getObjectType()->objectTypeID);

        $includeAclJavaScript = !static::$includedAclJavaScript;
        if (!static::$includedAclJavaScript) {
            static::$includedAclJavaScript = true;
        }

        return [
            'includeAclJavaScript' => $includeAclJavaScript,
        ];
    }

    /**
     * Returns the id of the edited object or `null` if no object is edited.
     */
    public function getObjectID(): ?int
    {
        return $this->objectID;
    }

    #[\Override]
    public function getObjectTypeDefinition(): string
    {
        return 'com.woltlab.wcf.acl';
    }

    #[\Override]
    public function hasSaveValue(): bool
    {
        return false;
    }

    #[\Override]
    public function updatedObject(array $data, IStorableObject $object, bool $loadValues = true): static
    {
        $this->objectID = $object->{$object::getDatabaseTableIndexName()};

        if ($this->objectID === null) {
            throw new \UnexpectedValueException(
                "Cannot read object id from object of class '" . \get_class($object) . "' for field '{$this->getId()}'."
            );
        }

        return $this;
    }

    #[\Override]
    public function populate(): static
    {
        parent::populate();

        $this->getDocument()->getDataHandler()->addProcessor(new CustomFormDataProcessor(
            'acl',
            function (IFormDocument $document, array $parameters) {
                $parameters[$this->getObjectProperty() . '_aclObjectTypeID'] = $this->getObjectType()->objectTypeID;

                return $parameters;
            }
        ));

        return $this;
    }

    #[\Override]
    public function readValue(): static
    {
        $valueSource = $_POST[$this->getPrefixedId()] ?? [];
        if ($this->getDocument()->isAjax()) {
            $valueSource = [];
            if (
                $this->getDocument()->hasRequestData($this->getPrefixedId())
                && \is_array($this->getDocument()->getRequestData($this->getPrefixedId()))
            ) {
                $valueSource = $this->getDocument()->getRequestData($this->getPrefixedId());
            }
        }

        ACLHandler::getInstance()->readValues($this->getObjectType()->objectTypeID, $valueSource);

        return $this;
    }

    #[\Override]
    public function cleanup(): static
    {
        ACLHandler::getInstance()->resetValues($this->getObjectType()->objectTypeID);

        return $this;
    }
}
