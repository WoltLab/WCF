<?php

namespace wcf\system\condition;

use wcf\data\condition\Condition;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Abstract implementation of a condition with select options.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractSelectCondition extends AbstractSingleFieldCondition
{
    /**
     * name of the field
     * @var string
     */
    protected $fieldName = '';

    /**
     * value of the selected option
     * @var string
     */
    protected $fieldValue = self::NO_SELECTION_VALUE;

    /**
     * value of the "no selection" option
     * @var string
     */
    const NO_SELECTION_VALUE = '-1';

    #[\Override]
    public function getData()
    {
        if ($this->fieldValue !== self::NO_SELECTION_VALUE) {
            return [$this->fieldName => $this->fieldValue];
        }

        return null;
    }

    #[\Override]
    protected function getFieldElement()
    {
        $options = $this->getOptions();

        $fieldElement = '<select name="' . $this->fieldName . '">';
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
    public function getHTML()
    {
        if ($this->getOptions() === []) {
            return '';
        }

        return parent::getHTML();
    }

    /**
     * Returns the html code for an opt group.
     *
     * @param string[] $options
     * @return  string
     */
    protected function getOptGroupCode(string $label, array $options)
    {
        $html = '<optgroup label="' . StringUtil::encodeHTML($label) . '">';
        foreach ($options as $key => $value) {
            $html .= $this->getOptionCode($key, $value);
        }
        $html .= '</optgroup>';

        return $html;
    }

    /**
     * Returns the html code for an option.
     *
     * @return  string
     */
    protected function getOptionCode(string $value, string $label)
    {
        return '<option value="' . $value . '"' . ($this->fieldValue === $value ? ' selected' : '') . '>' . StringUtil::encodeHTML(WCF::getLanguage()->get($label)) . '</option>';
    }

    /**
     * Returns the selectable options.
     *
     * @return array<string|int, string|mixed[]>
     */
    abstract protected function getOptions();

    #[\Override]
    public function readFormParameters()
    {
        if (isset($_POST[$this->fieldName])) {
            $this->fieldValue = (string)\intval($_POST[$this->fieldName]);
        }
    }

    #[\Override]
    public function reset()
    {
        $this->fieldValue = self::NO_SELECTION_VALUE;
    }

    #[\Override]
    public function setData(Condition $condition)
    {
        $this->fieldValue = ($condition->conditionData[$this->fieldName] ?? self::NO_SELECTION_VALUE);
    }

    #[\Override]
    public function validate()
    {
        if ($this->fieldValue !== self::NO_SELECTION_VALUE) {
            $options = $this->getOptions();

            if (!isset($options[$this->fieldValue])) {
                foreach ($options as $value) {
                    if (\is_array($value) && isset($value[$this->fieldValue])) {
                        return;
                    }
                }

                $this->errorMessage = 'wcf.global.form.error.noValidSelection';

                throw new UserInputException($this->fieldName, 'noValidSelection');
            }
        }
    }
}
