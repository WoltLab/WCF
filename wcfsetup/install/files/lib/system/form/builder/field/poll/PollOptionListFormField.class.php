<?php

namespace wcf\system\form\builder\field\poll;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use wcf\data\poll\option\PollOption;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\l10n\L10nStorage;
use wcf\system\language\LanguageFactory;
use wcf\util\StringUtil;

/**
 * Form field to manage the options of a poll, each option can either have a
 * single value or a value per language.
 *
 * In contrast to `PollOptionsFormField`, this field does not depend on a
 * wysiwyg field and is intended for use with the `PollBuilder`.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @phpstan-import-type L10nValue from L10nStorage
 * @phpstan-type PollOptionValue array{optionID: int, optionValue: L10nValue}
 */
final class PollOptionListFormField extends AbstractFormField
{
    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/PollOptionList';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_pollOptionListFormField';

    /**
     * @inheritDoc
     */
    protected $value = [];

    private bool $invalidValue = false;

    public function __construct()
    {
        $this->label('wcf.poll.options')
            ->description('wcf.poll.options.description');
    }

    #[\Override]
    public function readValue()
    {
        if (!$this->getDocument()->hasRequestData($this->getPrefixedId())) {
            return $this;
        }

        $this->value = [];
        $this->invalidValue = false;

        $value = $this->getDocument()->getRequestData($this->getPrefixedId());
        if (!\is_string($value) || $value === '') {
            return $this;
        }

        try {
            $options = new MapperBuilder()->mapper()->map(
                <<<'EOT'
                    list<array{
                        optionID: int,
                        optionValue: array<non-negative-int, string>,
                    }>
                    EOT,
                Source::json($value)
            );
        } catch (MappingError) {
            $this->invalidValue = true;

            return $this;
        }

        foreach ($options as $option) {
            $optionValue = self::normalizeValue($option['optionValue']);
            if ($optionValue === []) {
                continue;
            }

            $this->value[] = [
                'optionID' => \max($option['optionID'], 0),
                'optionValue' => $optionValue,
            ];
        }

        $this->value = \array_slice($this->value, 0, \POLL_MAX_OPTIONS);

        return $this;
    }

    /**
     * Trims the values and removes empty values. A value per language is
     * discarded entirely if it has no value for any language, a monolingual
     * value must not be combined with values per language.
     *
     * @param array<int, string> $values
     * @return L10nValue
     */
    private static function normalizeValue(array $values): array
    {
        if (isset($values[L10nStorage::MONOLINGUAL])) {
            $value = \mb_substr(StringUtil::trim($values[L10nStorage::MONOLINGUAL]), 0, 255);

            return $value === '' ? [] : [L10nStorage::MONOLINGUAL => $value];
        }

        $languageIDs = \array_map(
            static fn($language) => $language->languageID,
            LanguageFactory::getInstance()->getLanguages()
        );

        $normalizedValues = [];
        foreach ($values as $languageID => $value) {
            if (!\in_array($languageID, $languageIDs, true)) {
                continue;
            }

            $normalizedValues[$languageID] = \mb_substr(StringUtil::trim($value), 0, 255);
        }

        foreach ($normalizedValues as $value) {
            if ($value !== '') {
                return $normalizedValues;
            }
        }

        return [];
    }

    /**
     * Accepts a list of either `PollOption` objects or `PollOptionValue` arrays.
     */
    #[\Override]
    public function value(mixed $value)
    {
        $options = [];
        foreach ($value as $option) {
            if ($option instanceof PollOption) {
                $options[] = [
                    'optionID' => $option->optionID,
                    'optionValue' => $option->getL10nValues('optionValue'),
                ];
            } elseif (\is_array($option) && isset($option['optionID'], $option['optionValue'])) {
                $options[] = [
                    'optionID' => $option['optionID'],
                    'optionValue' => $option['optionValue'],
                ];
            } else {
                throw new \InvalidArgumentException(
                    "Given value array contains invalid value of type " . \gettype($option) . " for field '{$this->getId()}'."
                );
            }
        }

        return parent::value($options);
    }

    /**
     * @return list<PollOptionValue>
     */
    #[\Override]
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns true if at least one option has a value per language.
     */
    public function hasL10nValues(): bool
    {
        foreach ($this->value as $option) {
            if (!isset($option['optionValue'][L10nStorage::MONOLINGUAL])) {
                return true;
            }
        }

        return false;
    }

    #[\Override]
    public function validate()
    {
        if ($this->invalidValue) {
            $this->addValidationError(new FormFieldValidationError('invalid'));

            return;
        }

        if ($this->isRequired() && $this->value === []) {
            $this->addValidationError(new FormFieldValidationError('empty'));
        }

        parent::validate();
    }

    #[\Override]
    public function getHtmlVariables()
    {
        return [
            'availableLanguages' => \array_map(
                static fn($language) => $language->__toString(),
                LanguageFactory::getInstance()->getLanguages()
            ),
        ];
    }
}
