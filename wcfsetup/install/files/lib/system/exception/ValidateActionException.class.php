<?php

namespace wcf\system\exception;

use wcf\system\WCF;

/**
 * Simple exception for AJAX-driven requests.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.1 No longer in use, migrate to more specific exceptions
 */
class ValidateActionException extends \Exception
{
    /**
     * error message
     * @var string
     */
    protected $errorMessage = '';

    /**
     * erroneous field name
     * @var string
     */
    protected $fieldName = '';

    /**
     * @inheritDoc
     */
    public function __construct($fieldName, $errorMessage = 'empty', array $variables = [])
    {
        $this->errorMessage = $errorMessage;
        if (!\str_contains($this->errorMessage, '.')) {
            if (\preg_match('~^[a-zA-Z0-9-_]+$~', $this->errorMessage)) {
                $this->errorMessage = WCF::getLanguage()
                    ->getDynamicVariable('wcf.global.form.error.' . $this->errorMessage);
            }
        } else {
            $this->errorMessage = WCF::getLanguage()->getDynamicVariable($this->errorMessage, $variables);
        }

        $this->fieldName = $fieldName;
        $this->message = WCF::getLanguage()->getDynamicVariable(
            'wcf.ajax.error.invalidParameter',
            ['fieldName' => $this->fieldName]
        );
    }

    /**
     * Returns error message.
     *
     * @return  string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Returns erroneous field name.
     *
     * @return  string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->message;
    }
}
