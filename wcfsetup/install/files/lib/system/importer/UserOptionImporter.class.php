<?php

namespace wcf\system\importer;

use wcf\data\user\option\category\UserOptionCategoryEditor;
use wcf\data\user\option\category\UserOptionCategoryList;
use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionBuilder;
use wcf\system\l10n\L10nStorage;

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
     * @var string[]
     */
    protected $categoryCache;

    #[\Override]
    public function import(mixed $oldID, array $data, array $additionalData = [])
    {
        if ($data['optionType'] == 'boolean' || $data['optionType'] == 'integer') {
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
            ->setOptionType($data['optionType'])
            ->setCategoryName($data['categoryName'])
            ->setL10nTitle([L10nStorage::MONOLINGUAL => (string)$additionalData['name']])
            ->setL10nDescription([L10nStorage::MONOLINGUAL => (string)($additionalData['description'] ?? '')]);

        // carry over the remaining columns of the imported option
        $handledColumns = ['optionName', 'packageID', 'optionType', 'categoryName', 'l10nIdentifier'];
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

        if (!\in_array($name, $this->categoryCache)) {
            // create category
            UserOptionCategoryEditor::create([
                'packageID' => 1,
                'categoryName' => $name,
                'parentCategoryName' => 'profile',
            ]);

            $this->categoryCache[] = $name;
        }
    }
}
