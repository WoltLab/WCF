<?php

namespace wcf\system\form\builder\field;

use wcf\system\captcha\ICaptchaHandler;
use wcf\system\exception\UserInputException;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\IFormFieldValidationError;
use wcf\system\form\builder\IObjectTypeFormNode;
use wcf\system\form\builder\TObjectTypeFormNode;

/**
 * Implementation of a form field for a captcha.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class CaptchaFormField extends AbstractFormField implements IObjectTypeFormNode
{
    use TDefaultIdFormField;
    use TObjectTypeFormNode {
        objectType as defaultObjectType;
    }

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Captcha';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_captchaFormField';

    /**
     * exception thrown by the captcha API during validation
     * @var null|UserInputException
     */
    protected $validationException;

    /**
     * @var IFormFieldValidationError
     */
    protected $validationError;

    /**
     * @inheritDoc
     */
    public function cleanup(): static
    {
        try {
            /** @var ICaptchaHandler $captcha */
            $captcha = $this->getObjectType()->getProcessor();

            $captcha->reset();
        } catch (\BadMethodCallException $e) {
            // ignore
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHtmlVariables()
    {
        $variables = [
            'ajaxCaptcha' => $this->getDocument()->isAjax(),
            'captchaID' => $this->getPrefixedId(),
        ];

        if ($this->validationException !== null) {
            $variables['errorField'] = $this->validationException->getField();
            $variables['errorType'] = $this->validationException->getType();
        }

        // The error message is shown by the captcha handler itself but up until now, the form
        // builder API needs at least one validation error present to detect validation errors.
        // Now, however, we can remove the validation error again.
        if ($this->validationError) {
            $this->validationErrors = \array_filter(
                $this->validationErrors,
                function (IFormFieldValidationError $validationError) {
                    return $validationError !== $this->validationError;
                }
            );
        }

        return $variables;
    }

    /**
     * @inheritDoc
     */
    public function getObjectTypeDefinition()
    {
        return 'com.woltlab.wcf.captcha';
    }

    /**
     * @inheritDoc
     */
    public function isAvailable()
    {
        return $this->objectType !== null && parent::isAvailable();
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
    public function objectType($objectType)
    {
        // ignore empty object type which is the case if no captcha has been set
        if ($objectType === '') {
            return $this;
        }

        return $this->defaultObjectType($objectType);
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        /** @var ICaptchaHandler $captcha */
        $captcha = $this->getObjectType()->getProcessor();

        // the captcha API relies on `$_POST` thus make sure that request data is in `$_POST`,
        // at least temporarily
        $requestData = $this->getDocument()->getRequestData();
        $post = null;
        if ($requestData !== $_POST) {
            $post = $_POST;
            $_POST = $requestData;
        }

        $captcha->readFormParameters();

        // restore `$_POST`
        if ($post !== null) {
            $_POST = $post;
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        /** @var ICaptchaHandler $captcha */
        $captcha = $this->getObjectType()->getProcessor();

        try {
            $captcha->validate();
        } catch (UserInputException $e) {
            $this->validationException = $e;
            $this->validationError = new FormFieldValidationError($e->getType());
            $this->addValidationError($this->validationError);
        }
    }

    /**
     * @inheritDoc
     */
    protected static function getDefaultId()
    {
        return 'captcha';
    }
}
