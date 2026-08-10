<?php

namespace wcf\system\form\builder\field\acl\simple;

use wcf\system\acl\simple\SimpleAclHandler;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\IFormDocument;

/**
 * Implementation of a form field for setting simple acl.
 *
 * Note: This form field should not be put in a simple `FormContainer` element
 * as its output already generates `.section` elements.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class SimpleAclFormField extends AbstractFormField
{
    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/SimpleAcl';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_simpleAclFormField';

    protected bool $supportInvertedPermissions = false;

    #[\Override]
    public function getHtmlVariables(): array
    {
        return [
            '__aclSimplePrefix' => $this->getPrefixedId(),
            '__aclInputName' => $this->getPrefixedId(),
            'aclValues' => SimpleAclHandler::getInstance()->getOutputValues($this->getValue() ?: []),
            '__supportsInvertedPermissions' => $this->supportInvertedPermissions,
            'invertPermissions' => $this->isInverted(),
        ];
    }

    #[\Override]
    public function hasSaveValue(): bool
    {
        return false;
    }

    #[\Override]
    public function populate(): static
    {
        parent::populate();

        $this->getDocument()->getDataHandler()->addProcessor(new CustomFormDataProcessor(
            'i18n',
            function (IFormDocument $document, array $parameters) {
                if ($this->checkDependencies() && \is_array($this->getValue()) && $this->getValue() !== []) {
                    $parameters[$this->getObjectProperty()] = $this->getValue();
                }

                return $parameters;
            }
        ));

        return $this;
    }

    #[\Override]
    public function readValue(): static
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            if (\is_array($value)) {
                $this->value = $value;

                if ($this->supportInvertedPermissions) {
                    $requestData = $this->getDocument()->getRequestData();
                    $field = $this->getPrefixedId() . 'invertPermissions';
                    $this->value['invertPermissions'] = !empty($requestData[$field]);
                }
            }
        }

        return $this;
    }

    /**
     * Enables or disables the support for inverted permissions.
     *
     * @since   5.5
     */
    public function supportInvertedPermissions(bool $supportInvertedPermissions = true): static
    {
        $this->supportInvertedPermissions = $supportInvertedPermissions;

        return $this;
    }

    /**
     * @since 5.5
     */
    public function isSupportingInvertedPermissions(): bool
    {
        return $this->supportInvertedPermissions;
    }

    private function isInverted(): bool
    {
        return $this->supportInvertedPermissions && !empty($this->value['invertPermissions']);
    }
}
