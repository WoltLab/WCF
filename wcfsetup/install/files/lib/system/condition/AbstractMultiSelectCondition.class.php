<?php

namespace wcf\system\condition;

use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Abstract implementation of a condition with multi select options.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractMultiSelectCondition extends AbstractSelectCondition
{
    /**
     * selected values
     * @var mixed[]
     * @phpstan-ignore property.phpDocType
     */
    protected $fieldValue = [];

    #[\Override]
    public function getData()
    {
        if ($this->fieldValue !== []) {
            return [$this->fieldName => $this->fieldValue];
        }

        return null;
    }

    #[\Override]
    protected function getFieldElement()
    {
        $options = $this->getOptions();

        $fieldElement = '<select name="' . $this->fieldName . '[]" id="' . $this->fieldName . '" multiple size="' . (\count(
            $options,
            \COUNT_RECURSIVE
        ) > 10 ? 10 : \count($options, \COUNT_RECURSIVE)) . '">';
        foreach ($options as $key => $value) {
            if (\is_array($value)) {
                $fieldElement .= $this->getOptGroupCode($key, $value);
            } else {
                $fieldElement .= $this->getOptionCode($key, $value);
            }
        }
        $fieldElement .= "</select>";

        return $fieldElement;
    }

    #[\Override]
    protected function getOptionCode(string $value, string $label)
    {
        return '<option value="' . $value . '"' . (\in_array(
            $value,
            $this->fieldValue
        ) ? ' selected' : '') . '>' . StringUtil::encodeHTML(WCF::getLanguage()->get($label)) . '</option>';
    }

    #[\Override]
    public function readFormParameters()
    {
        if (isset($_POST[$this->fieldName]) && \is_array($_POST[$this->fieldName])) {
            $this->fieldValue = ArrayUtil::toIntegerArray($_POST[$this->fieldName]);
        }
    }

    #[\Override]
    public function reset()
    {
        $this->fieldValue = [];
    }

    #[\Override]
    public function validate()
    {
        $options = $this->getOptions();
        foreach ($this->fieldValue as $value) {
            if (!isset($options[$value])) {
                foreach ($options as $optionValue) {
                    if (\is_array($optionValue) && isset($optionValue[$value])) {
                        return;
                    }
                }

                $this->errorMessage = 'wcf.global.form.error.noValidSelection';

                throw new UserInputException($this->fieldName, 'noValidSelection');
            }
        }
    }
}
