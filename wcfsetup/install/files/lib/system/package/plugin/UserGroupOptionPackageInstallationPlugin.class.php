<?php

namespace wcf\system\package\plugin;

use wcf\data\option\category\OptionCategory;
use wcf\data\option\Option;
use wcf\data\user\group\option\UserGroupOption;
use wcf\data\user\group\option\UserGroupOptionEditor;
use wcf\data\user\group\UserGroup;
use wcf\system\application\ApplicationHandler;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\exception\SystemException;
use wcf\system\form\builder\container\IFormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\option\IntegerOptionType;
use wcf\system\option\IOptionType;
use wcf\system\option\TextOptionType;
use wcf\system\option\user\group\UserGroupOptionHandler;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes user group options.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserGroupOptionPackageInstallationPlugin extends AbstractOptionPackageInstallationPlugin implements
    IGuiPackageInstallationPlugin
{
    /**
     * @inheritDoc
     */
    public $className = UserGroupOptionEditor::class;

    /**
     * list of group ids by type
     * @var int[][]
     */
    protected $groupIDs;

    /**
     * @inheritDoc
     */
    public $tableName = 'user_group_option';

    /**
     * @inheritDoc
     */
    public $tagName = 'option';

    /**
     * list of names of tags which aren't considered as additional data
     * @var string[]
     */
    public static $reservedTags = [
        'name',
        'optiontype',
        'defaultvalue',
        'admindefaultvalue',
        'userdefaultvalue',
        'moddefaultvalue',
        'validationpattern',
        'showorder',
        'categoryname',
        'selectoptions',
        'enableoptions',
        'permissions',
        'options',
        'attrs',
        'cdata',
        'usersonly',
    ];

    /**
     * @inheritDoc
     */
    protected function saveOption($option, $categoryName, $existingOptionID = 0)
    {
        // default values
        $optionName = $optionType = $defaultValue = $adminDefaultValue = $modDefaultValue = $userDefaultValue = $validationPattern = $enableOptions = $permissions = $options = '';
        $usersOnly = 0;
        $showOrder = null;

        // get values
        if (isset($option['name'])) {
            $optionName = $option['name'];
        }
        if (isset($option['optiontype'])) {
            $optionType = $option['optiontype'];
        }
        if (isset($option['defaultvalue'])) {
            $defaultValue = $option['defaultvalue'];
        }
        if (isset($option['admindefaultvalue'])) {
            $adminDefaultValue = $option['admindefaultvalue'];
        }
        if (isset($option['moddefaultvalue'])) {
            $modDefaultValue = $option['moddefaultvalue'];
        }
        if (isset($option['userdefaultvalue'])) {
            $userDefaultValue = $option['userdefaultvalue'];
        }
        if (isset($option['validationpattern'])) {
            $validationPattern = $option['validationpattern'];
        }
        if (!empty($option['showorder'])) {
            $showOrder = \intval($option['showorder']);
        }
        $showOrder = $this->getShowOrder($showOrder, $categoryName, 'categoryName');
        if (isset($option['enableoptions'])) {
            $enableOptions = StringUtil::normalizeCsv($option['enableoptions']);
        }
        if (isset($option['permissions'])) {
            $permissions = StringUtil::normalizeCsv($option['permissions']);
        }
        if (isset($option['options'])) {
            $options = StringUtil::normalizeCsv($option['options']);
        }
        if (isset($option['usersonly'])) {
            $usersOnly = $option['usersonly'];
        }

        if (empty($optionType)) {
            throw new SystemException("Expected a non-empty 'optiontype' value for the option  '" . $optionName . "'.");
        }

        // force the `html` bbcode to be disabled by default
        if ($optionType === 'BBCodeSelect') {
            $defaultValue .= (empty($defaultValue) ? '' : ',') . 'html';
            $adminDefaultValue .= (empty($adminDefaultValue) ? '' : ',') . 'html';
            $modDefaultValue .= (empty($modDefaultValue) ? '' : ',') . 'html';
            $userDefaultValue .= (empty($userDefaultValue) ? '' : ',') . 'html';
        }

        // collect additional tags and their values
        $additionalData = [];
        foreach ($option as $tag => $value) {
            if (!\in_array($tag, self::$reservedTags)) {
                $additionalData[$tag] = $value;
            }
        }

        // check if the option exist already and was installed by this package
        $sql = "SELECT  optionID
                FROM    wcf1_user_group_option
                WHERE   optionName = ?
                    AND packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $optionName,
            $this->installation->getPackageID(),
        ]);
        $row = $statement->fetchArray();

        $data = [
            'categoryName' => $categoryName,
            'optionType' => $optionType,
            'defaultValue' => isset($option['userdefaultvalue']) ? $userDefaultValue : $defaultValue,
            'validationPattern' => $validationPattern,
            'showOrder' => $showOrder,
            'enableOptions' => $enableOptions,
            'permissions' => $permissions,
            'options' => $options,
            'usersOnly' => $usersOnly,
            'additionalData' => \serialize($additionalData),
        ];

        if (!empty($row['optionID'])) {
            $groupOption = new UserGroupOption(null, $row);
            $groupOptionEditor = new UserGroupOptionEditor($groupOption);
            $groupOptionEditor->update($data);
        } else {
            // add new option
            $data['packageID'] = $this->installation->getPackageID();
            $data['optionName'] = $optionName;

            $groupOptionEditor = UserGroupOptionEditor::create($data);
            $optionID = $groupOptionEditor->optionID;

            $this->getGroupIDs();
            $values = [];
            foreach ($this->groupIDs['all'] as $groupID) {
                $values[$groupID] = $defaultValue;
            }
            if (isset($option['userdefaultvalue'])) {
                foreach ($this->groupIDs['registered'] as $groupID) {
                    $values[$groupID] = $userDefaultValue;
                }
            }
            if (isset($option['moddefaultvalue'])) {
                foreach ($this->groupIDs['mod'] as $groupID) {
                    $values[$groupID] = $modDefaultValue;
                }
            }
            if (isset($option['admindefaultvalue'])) {
                foreach ($this->groupIDs['admin'] as $groupID) {
                    $values[$groupID] = $adminDefaultValue;
                }
            }

            // save values
            $sql = "INSERT INTO wcf1_user_group_option_value
                                (groupID, optionID, optionValue)
                    VALUES      (?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);
            WCF::getDB()->beginTransaction();
            foreach ($values as $groupID => $value) {
                $statement->execute([
                    $groupID,
                    $optionID,
                    $value,
                ]);
            }
            WCF::getDB()->commitTransaction();
        }
    }

    /**
     * Returns a list of group ids by type.
     *
     * @return  int[][]
     */
    protected function getGroupIDs()
    {
        if ($this->groupIDs === null) {
            $this->groupIDs = [
                'admin' => [],
                'mod' => [],
                'all' => [],
                'registered' => [],
            ];

            $sql = "SELECT  groupID, groupType
                    FROM    wcf1_user_group";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute();
            while ($row = $statement->fetchArray()) {
                $group = new UserGroup(null, $row);
                $this->groupIDs['all'][] = $group->groupID;

                if ($group->groupType != UserGroup::EVERYONE && $group->groupType != UserGroup::GUESTS) {
                    $this->groupIDs['registered'][] = $group->groupID;

                    if ($group->isModGroup()) {
                        $this->groupIDs['mod'][] = $group->groupID;
                    }
                    if ($group->isAdminGroup()) {
                        $this->groupIDs['admin'][] = $group->groupID;
                    }
                }
            }
        }

        return $this->groupIDs;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function addFormFields(IFormDocument $form)
    {
        parent::addFormFields($form);

        if ($this->entryType === 'options') {
            /** @var IFormContainer $dataContainer */
            $dataContainer = $form->getNodeById('data');

            /** @var SingleSelectionFormField $optionType */
            $optionType = $form->getNodeById('optionType');

            $dataContainer->appendChildren([
                MultilineTextFormField::create('adminDefaultValue')
                    ->objectProperty('admindefaultvalue')
                    ->label('wcf.acp.pip.userGroupOption.options.adminDefaultValue')
                    ->description('wcf.acp.pip.userGroupOption.options.adminDefaultValue.description')
                    ->rows(5),

                MultilineTextFormField::create('modDefaultValue')
                    ->objectProperty('moddefaultvalue')
                    ->label('wcf.acp.pip.userGroupOption.options.modDefaultValue')
                    ->description('wcf.acp.pip.userGroupOption.options.modDefaultValue.description')
                    ->rows(5),

                MultilineTextFormField::create('userDefaultValue')
                    ->objectProperty('userdefaultvalue')
                    ->label('wcf.acp.pip.userGroupOption.options.userDefaultValue')
                    ->description('wcf.acp.pip.userGroupOption.options.userDefaultValue.description')
                    ->rows(5),

                BooleanFormField::create('usersOnly')
                    ->objectProperty('usersonly')
                    ->label('wcf.acp.pip.userGroupOption.options.usersOnly')
                    ->description('wcf.acp.pip.userGroupOption.options.usersOnly.description'),

                BooleanFormField::create('excludedInTinyBuild')
                    ->label('wcf.acp.pip.userGroupOption.options.excludedInTinyBuild')
                    ->description('wcf.acp.pip.userGroupOption.options.excludedInTinyBuild.description'),

                TextFormField::create('wildcard')
                    ->label('wcf.acp.pip.userGroupOption.options.wildcard')
                    ->description('wcf.acp.pip.userGroupOption.options.wildcard.description')
                    ->addDependency(
                        ValueFormFieldDependency::create('optionType')
                            ->field($optionType)
                            ->values(['textarea'])
                    ),
            ]);
        }
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function fetchElementData(\DOMElement $element, $saveData)
    {
        $data = parent::fetchElementData($element, $saveData);

        switch ($this->entryType) {
            case 'options':
                foreach (
                    [
                        'adminDefaultValue',
                        'modDefaultValue',
                        'userDefaultValue',
                        'usersOnly',
                        'excludedInTinyBuild',
                        'wildcard',
                    ] as $optionalPropertyName
                ) {
                    $elementName = \strtolower($optionalPropertyName);
                    if ($optionalPropertyName === 'excludedInTinyBuild') {
                        $elementName = 'excludedInTinyBuild';
                    }

                    $optionalProperty = $element->getElementsByTagName($elementName)->item(0);
                    if ($optionalProperty !== null) {
                        if ($saveData && $optionalPropertyName !== 'excludedInTinyBuild') {
                            $data[\strtolower($optionalPropertyName)] = $optionalProperty->nodeValue;
                        } else {
                            $data[$optionalPropertyName] = $optionalProperty->nodeValue;
                        }
                    } elseif ($saveData && $optionalPropertyName === 'usersOnly') {
                        // all of the other fields will be put in `additionalData`
                        // or not saved thus empty values are not necessary
                        $data['usersonly'] = 0;
                    }
                }

                break;
        }

        return $data;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function getSortOptionHandler()
    {
        // reuse UserGroupOptionHandler
        return new class(true) extends UserGroupOptionHandler {
            /**
             * @inheritDoc
             */
            protected function checkCategory(OptionCategory $category)
            {
                // we do not care for category checks here
                return true;
            }

            /**
             * @inheritDoc
             */
            protected function checkOption(Option $option)
            {
                // we do not care for option checks here
                return true;
            }

            /**
             * @inheritDoc
             */
            public function getCategoryOptions($categoryName = '', $inherit = true)
            {
                // we just need to ensure that the category is not empty
                return [new Option(null, [])];
            }
        };
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $option = parent::prepareXmlElement($document, $form);

        switch ($this->entryType) {
            case 'options':
                $this->appendElementChildren(
                    $option,
                    [
                        'admindefaultvalue' => '',
                        'moddefaultvalue' => '',
                        'userdefaultvalue' => '',
                        'usersonly' => 0,
                        'excludedInTinyBuild' => 0,
                        'wildcard' => '',
                    ],
                    $form
                );

                break;
        }

        return $option;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function getOptionTypeOptions()
    {
        $options = [];

        // consider all applications for potential object types
        foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
            $optionDir = $application->getPackage()->getAbsolutePackageDir() . 'lib/system/option/user/group/';
            if (!\is_dir($optionDir)) {
                continue;
            }
            $directoryUtil = DirectoryUtil::getInstance($optionDir);

            foreach ($directoryUtil->getFileObjects() as $fileObject) {
                if ($fileObject->isFile()) {
                    $optionTypePrefix = '';

                    $unqualifiedClassname = \str_replace('.class.php', '', $fileObject->getFilename());
                    $classname = $application->getAbbreviation() . '\\system\\option\\user\\group\\' . $unqualifiedClassname;

                    if (
                        !\is_subclass_of(
                            $classname,
                            IOptionType::class
                        ) || !(new \ReflectionClass($classname))->isInstantiable()
                    ) {
                        continue;
                    }

                    $optionType = \str_replace(
                        $optionTypePrefix . 'UserGroupOptionType.class.php',
                        '',
                        $fileObject->getFilename()
                    );

                    // only make first letter lowercase if the first two letters are not uppercase
                    // relevant cases: `URL` and the `WBB` prefix
                    if (!\preg_match('~^[A-Z]{2}~', $optionType)) {
                        $optionType = \lcfirst($optionType);
                    }

                    if (\is_subclass_of($classname, IntegerOptionType::class)) {
                        $this->integerOptionTypes[] = $optionType;
                    }

                    if (\is_subclass_of($classname, TextOptionType::class)) {
                        $this->textOptionTypes[] = $optionType;
                    }

                    $options[] = $optionType;
                }
            }
        }

        \natcasesort($options);

        return \array_combine($options, $options);
    }
}
