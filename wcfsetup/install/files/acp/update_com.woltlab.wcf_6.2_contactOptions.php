<?php

/**
 * This script converts existing contact options to the new form option system.
 */

use wcf\data\contact\option\ContactOptionEditor;
use wcf\data\contact\option\ContactOptionList;
use wcf\util\JSON;
use wcf\util\OptionUtil;

$contactOptionList = new ContactOptionList();
$contactOptionList->readObjects();
$contactOptionList->getConditionBuilder()->add('configuration IS NULL');

foreach ($contactOptionList as $contactOption) {
    $configuration = [];
    $optionType = '';
    $optionType = match ($contactOption->optionType) {
        'multiSelect' => 'checkboxes',
        'message' => 'wysiwyg',
        'URL' => 'url',
        default => $contactOption->optionType,
    };

    // @phpstan-ignore property.notFound
    if ($contactOption->required) {
        $configuration['required'] = 1;
    }
    // @phpstan-ignore property.notFound
    if ($contactOption->defaultValue && $contactOption->optionType == 'text') {
        $configuration['defaultValue'] = $contactOption->defaultValue;
    }
    // @phpstan-ignore property.notFound
    if ($contactOption->selectOptions) {
        $configuration['required'] = convertSelectOptions($contactOption->selectOptions);
    }

    $editor = new ContactOptionEditor($contactOption);
    $editor->update([
        'optionType' => $optionType,
        'configuration' => JSON::encode($configuration),
    ]);
}

function convertSelectOptions(string $selectOptions): string
{
    $options = [];

    $parsedSelectOptions = OptionUtil::parseSelectOptions($selectOptions);
    foreach ($parsedSelectOptions as $key => $value) {
        $options[] = [
            'key' => $key,
            'value' => [
                0 => $value
            ]
        ];
    }

    return JSON::encode($options);
}
