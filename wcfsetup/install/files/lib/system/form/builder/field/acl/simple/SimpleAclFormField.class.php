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

    /**
     * @var bool
     */
    protected $supportInvertedPermissions = false;

    /**
     * @inheritDoc
     */
    public function getHtmlVariables()
    {
        return [
            '__aclSimplePrefix' => $this->getPrefixedId(),
            '__aclInputName' => $this->getPrefixedId(),
            'aclValues' => SimpleAclHandler::getInstance()->getOutputValues($this->getValue() ?: []),
            '__supportsInvertedPermissions' => $this->supportInvertedPermissions,
            'invertPermissions' => $this->isInverted(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function hasSaveValue()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function populate()
    {
        parent::populate();

        $this->getDocument()->getDataHandler()->addProcessor(new CustomFormDataProcessor(
            'i18n',
            function (IFormDocument $document, array $parameters) {
                if ($this->checkDependencies() && \is_array($this->getValue()) && !empty($this->getValue())) {
                    $parameters[$this->getObjectProperty()] = $this->getValue();
                }

                return $parameters;
            }
        ));

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            if (\is_array($value)) {
                $this->value = $value;

                if ($this->supportInvertedPermissions) {
                    $requestData = $this->getDocument()->getRequestData();
                    $field = $this->getPrefixedId() . 'invertPermissions';
                    $this->value['invertPermissions'] = isset($requestData[$field]) && $requestData[$field];
                }
            }
        }

        return $this;
    }

    /**
     * Enables or disables the support for inverted permissions.
     *
     * @return  static      this field
     * @since   5.5
     */
    public function supportInvertedPermissions(bool $supportInvertedPermissions = true)
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
        return $this->supportInvertedPermissions && isset($this->value['invertPermissions']) && $this->value['invertPermissions'];
    }
}
