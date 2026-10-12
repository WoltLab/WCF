<?php

namespace wcf\system\importer;

use wcf\data\user\option\category\UserOptionCategoryBuilder;
use wcf\data\user\option\category\UserOptionCategoryList;
use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionBuilder;
use wcf\system\l10n\L10nStorage;
use wcf\util\StringUtil;

/**
 * Imports user options.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserOptionImporter extends AbstractImporter
{
    /**
     * @inheritDoc
     */
    protected $className = UserOption::class;

    /**
     * list of available user option categories
     * @var string[]|null
     */
    protected $categoryCache;

    #[\Override]
    public function import(mixed $oldID, array $data, array $additionalData = [])
    {
        if ($data['optionType'] === 'boolean' || $data['optionType'] === 'integer') {
            if (isset($data['defaultValue'])) {
                $data['defaultValue'] = \intval($data['defaultValue']);
            }
        }

        // create category
        $this->createCategory($data['categoryName']);

        // Imported options are owned by the administrator: they are not linked
        // to a language variable (`l10nIdentifier` stays `NULL`) and their
        // localized title/description are stored as monolingual values.
        $builder = UserOptionBuilder::forCreate()
            ->setGenericOptionName()
            ->setPackageID(1)
            ->setOptionType(UserOption::LEGACY_OPTION_TYPES[$data['optionType']] ?? $data['optionType'])
            ->setCategoryName($data['categoryName'])
            ->setL10nTitle([L10nStorage::MONOLINGUAL => (string)$additionalData['name']])
            ->setL10nDescription([L10nStorage::MONOLINGUAL => (string)($additionalData['description'] ?? '')]);

        // The select options and the required flag are converted into the
        // form option configuration.
        if (isset($data['selectOptions'])) {
            $builder->setSelectOptions((string)$data['selectOptions']);
        }
        if (isset($data['required'])) {
            $builder->setRequired((bool)$data['required']);
        }

        // Options of the legacy type `labeledUrl` become social media profiles,
        // the name pattern replaces the validation pattern.
        if ($data['optionType'] === 'labeledUrl') {
            $configuration = UserOptionBuilder::buildConfiguration(
                (string)($data['selectOptions'] ?? ''),
                (bool)($data['required'] ?? false)
            );
            $configuration = $configuration !== '' ? \json_decode($configuration, true, flags: \JSON_THROW_ON_ERROR) : [];
            $configuration['profileUrlPattern'] = (string)($data['labeledUrl'] ?? '');
            if (($data['validationPattern'] ?? '') !== '') {
                $configuration['profileNamePattern'] = $data['validationPattern'];
            }

            $builder->setConfiguration($configuration);
            unset($data['validationPattern']);
        }

        // Options of a source with form options provide their configuration,
        // which takes precedence over the legacy select options and required flag.
        if (($data['configuration'] ?? '') !== '') {
            $builder->setConfiguration(\json_decode($data['configuration'], true, flags: \JSON_THROW_ON_ERROR));
        }

        // The legacy multiple selection option types separate the selected
        // values by newlines, the `checkboxes` form option separates them by commas.
        if (
            (UserOption::LEGACY_OPTION_TYPES[$data['optionType']] ?? null) === 'checkboxes'
            && isset($data['defaultValue'])
        ) {
            $data['defaultValue'] = \str_replace("\n", ',', StringUtil::unifyNewlines((string)$data['defaultValue']));
        }

        // carry over the remaining columns of the imported option, skipping
        // columns of the legacy option system that no longer exist
        $handledColumns = [
            'optionName',
            'packageID',
            'optionType',
            'categoryName',
            'l10nIdentifier',
            'selectOptions',
            'required',
            'outputClass',
            'enableOptions',
            'labeledUrl',
            'additionalData',
            'configuration',
        ];
        foreach ($data as $key => $value) {
            if (\in_array($key, $handledColumns, true)) {
                continue;
            }
            if ($value !== null && !\is_string($value) && !\is_int($value) && !\is_float($value)) {
                continue;
            }

            $builder->setCustomProperty($key, $value);
        }

        $userOption = $builder->create();

        ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user.option', $oldID, $userOption->optionID);

        return $userOption->optionID;
    }

    /**
     * Creates the given category if necessary.
     *
     * @return void
     */
    protected function createCategory(string $name)
    {
        if ($this->categoryCache === null) {
            // get existing categories
            $list = new UserOptionCategoryList();
            $list->getConditionBuilder()->add('categoryName = ? OR parentCategoryName = ?', ['profile', 'profile']);
            $list->readObjects();
            foreach ($list->getObjects() as $category) {
                $this->categoryCache[] = $category->categoryName;
            }
        }

        if (!\in_array($name, $this->categoryCache, true)) {
            // Imported categories are owned by the administrator and use their
            // name as the monolingual title.
            UserOptionCategoryBuilder::forCreate()
                ->setPackageID(1)
                ->setCategoryName($name)
                ->setParentCategoryName('profile')
                ->setL10nTitle([L10nStorage::MONOLINGUAL => $name])
                ->create();

            $this->categoryCache[] = $name;
        }
    }
}
