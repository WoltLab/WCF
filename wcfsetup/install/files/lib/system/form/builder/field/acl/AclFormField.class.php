<?php

namespace wcf\system\form\builder\field\acl;

use wcf\data\acl\option\ACLOption;
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

    private bool $valuesRead = false;

    /**
     * keys of the documents and object types for which acl values have already
     * been read by a field, the values of further fields are merged
     * @var array<string, true>
     */
    private static array $readDocumentObjectTypes = [];

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
        $objectTypeID = $this->getObjectType()->objectTypeID;
        if (!$this->valuesRead) {
            $this->preloadValues($objectTypeID);
        }

        ACLHandler::getInstance()->assignVariables($objectTypeID);

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
                // Ajax forms submit the values as objects instead of the JSON
                // strings of the hidden inputs that `ACLHandler` expects.
                $requestData = $this->getDocument()->getRequestData($this->getPrefixedId());
                foreach (['group', 'user'] as $type) {
                    if (isset($requestData[$type]) && \is_array($requestData[$type])) {
                        $valueSource[$type] = \json_encode($requestData[$type], \JSON_THROW_ON_ERROR);
                    }
                }
            }
        }

        $objectTypeID = $this->getObjectType()->objectTypeID;
        if ($this->categoryName !== null) {
            $valueSource = $this->filterValuesByCategory($objectTypeID, $valueSource);
        }

        // Multiple fields for the same object type, for example showing different
        // option categories, provide parts of the values that are merged.
        $key = $this->getDocumentObjectTypeKey($objectTypeID);
        ACLHandler::getInstance()->readValues(
            $objectTypeID,
            $valueSource,
            isset(self::$readDocumentObjectTypes[$key])
        );
        self::$readDocumentObjectTypes[$key] = true;
        $this->valuesRead = true;

        return $this;
    }

    /**
     * Removes the values of options that do not belong to the categories shown
     * by this field. The acl list submits the values of all options, including
     * the values of options handled by other fields.
     *
     * @param mixed[] $valueSource
     * @return mixed[]
     */
    private function filterValuesByCategory(int $objectTypeID, array $valueSource): array
    {
        $optionIDs = [];
        foreach (ACLOption::getOptions($objectTypeID)->getObjects() as $option) {
            if ($this->matchesCategory($option->categoryName)) {
                $optionIDs[$option->optionID] = true;
            }
        }

        foreach (['group', 'user'] as $type) {
            if (!isset($valueSource[$type]) || !\is_string($valueSource[$type])) {
                continue;
            }

            $typeData = \json_decode($valueSource[$type], true, flags: \JSON_THROW_ON_ERROR);
            if (!\is_array($typeData)) {
                continue;
            }

            foreach ($typeData as $typeID => $optionData) {
                if (\is_array($optionData)) {
                    $typeData[$typeID] = \array_intersect_key($optionData, $optionIDs);
                }
            }

            $valueSource[$type] = \json_encode($typeData, \JSON_THROW_ON_ERROR);
        }

        return $valueSource;
    }

    private function matchesCategory(string $categoryName): bool
    {
        if (\str_ends_with($this->categoryName, '.*')) {
            return \str_starts_with($categoryName, \substr($this->categoryName, 0, -1));
        }

        return $categoryName === $this->categoryName;
    }

    private function getDocumentObjectTypeKey(int $objectTypeID): string
    {
        return \spl_object_id($this->getDocument()) . '-' . $objectTypeID;
    }

    /**
     * Provides the stored values of the edited object to the acl list, which
     * otherwise requests them through an action that requires the ACP.
     *
     * @since 6.3
     */
    private function preloadValues(int $objectTypeID): void
    {
        $values = [
            'group' => [],
            'user' => [],
        ];

        if ($this->objectID !== null) {
            $permissions = ACLHandler::getInstance()->getPermissions($objectTypeID, [$this->objectID]);
            foreach (\array_keys($values) as $type) {
                $values[$type] = $permissions[$type][$this->objectID] ?? [];
            }
        }

        ACLHandler::getInstance()->readValues($objectTypeID, [
            'group' => \json_encode($values['group'], \JSON_THROW_ON_ERROR),
            'user' => \json_encode($values['user'], \JSON_THROW_ON_ERROR),
        ]);
    }

    #[\Override]
    public function cleanup(): static
    {
        ACLHandler::getInstance()->resetValues($this->getObjectType()->objectTypeID);
        unset(self::$readDocumentObjectTypes[$this->getDocumentObjectTypeKey($this->getObjectType()->objectTypeID)]);

        return $this;
    }

    #[\Override]
    public function getLabelTarget(): ?string
    {
        return null;
    }
}
