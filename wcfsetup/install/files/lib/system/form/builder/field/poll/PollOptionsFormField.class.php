<?php

namespace wcf\system\form\builder\field\poll;

use wcf\data\poll\option\PollOption;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\TWysiwygFormNode;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Represents the form field to manage poll options/answers.
 *
 * This form field should not be used idenpendently but only via `WysiwygPollFormContainer`.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class PollOptionsFormField extends AbstractFormField
{
    use TWysiwygFormNode;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Wysiwyg/Poll';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_pollOptionsFormField';

    /**
     * @inheritDoc
     */
    protected $value = [];

    /**
     * Creates a new instance of `PollOptionsFormField`.
     */
    public function __construct()
    {
        $this->label('wcf.poll.options')
            ->description('wcf.poll.options.description')
            ->addClass('pollOptionContainer');
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if (
            $this->getDocument()->hasRequestData($this->getPrefixedId())
            && \is_array($this->getDocument()->getRequestData($this->getPrefixedId()))
        ) {
            $value = \array_slice(
                ArrayUtil::trim($this->getDocument()->getRequestData($this->getPrefixedId())),
                0,
                POLL_MAX_OPTIONS
            );

            $this->value = [];
            foreach ($value as $showOrder => $option) {
                [$optionID, $optionValue] = \explode('_', $option, 2);
                $this->value[$showOrder] = [
                    'optionID' => \intval($optionID),
                    'optionValue' => StringUtil::trim($optionValue),
                ];
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function value($value)
    {
        $pollOptions = [];

        foreach ($value as $pollOption) {
            if ($pollOption instanceof PollOption) {
                $pollOptions[] = [
                    'optionID' => $pollOption->optionID,
                    'optionValue' => $pollOption->optionValue,
                ];
            } elseif (\is_array($pollOption) && isset($pollOption['optionID']) && isset($pollOption['optionValue'])) {
                $pollOptions[] = [
                    'optionID' => $pollOption['optionID'],
                    'optionValue' => $pollOption['optionValue'],
                ];
            } else {
                throw new \InvalidArgumentException(
                    "Given value array contains invalid value of type " . \gettype($pollOption) . " for field '{$this->getId()}'."
                );
            }
        }

        return parent::value($pollOptions);
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        // ensure maximum length that is already validated via JavaScript
        foreach ($this->value as &$value) {
            $value['optionValue'] = \mb_substr($value['optionValue'], 0, 255);
        }
        unset($value);
    }
}
