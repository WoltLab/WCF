<?php

namespace wcf\system\condition;

use wcf\data\condition\Condition;
use wcf\util\StringUtil;

/**
 * Abstract implementation of a text condition.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractTextCondition extends AbstractSingleFieldCondition
{
    /**
     * name of the field
     * @var string
     */
    protected $fieldName = '';

    /**
     * entered condition field value
     * @var string
     */
    protected $fieldValue = '';

    #[\Override]
    public function getData()
    {
        if (\mb_strlen($this->fieldValue)) {
            return [$this->fieldName => $this->fieldValue];
        }

        return null;
    }

    #[\Override]
    protected function getFieldElement()
    {
        return '<input type="text" name="' . $this->fieldName . '" value="' . StringUtil::encodeHTML($this->fieldValue) . '" class="long">';
    }

    #[\Override]
    public function readFormParameters()
    {
        if (isset($_POST[$this->fieldName])) {
            $this->fieldValue = StringUtil::trim($_POST[$this->fieldName]);
        }
    }

    #[\Override]
    public function reset()
    {
        $this->fieldValue = '';
    }

    #[\Override]
    public function setData(Condition $condition)
    {
        $this->fieldValue = $condition->conditionData[$this->fieldName];
    }
}
