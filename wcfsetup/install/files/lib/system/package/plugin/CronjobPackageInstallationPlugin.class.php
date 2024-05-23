<?php

namespace wcf\system\package\plugin;

use Cron\CronExpression;
use Cron\FieldFactory;
use wcf\data\cronjob\CronjobEditor;
use wcf\data\cronjob\CronjobList;
use wcf\system\cronjob\ICronjob;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\form\builder\container\IFormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\option\OptionFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes cronjobs.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class CronjobPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements
    IGuiPackageInstallationPlugin,
    IUniqueNameXMLPackageInstallationPlugin
{
    use TXmlGuiPackageInstallationPlugin;

    /**
     * @inheritDoc
     */
    public $className = CronjobEditor::class;

    /**
     * @inheritDoc
     */
    protected function getElement(\DOMXPath $xpath, array &$elements, \DOMElement $element)
    {
        switch ($element->tagName) {
            case 'description':
                if (!isset($elements['description'])) {
                    $elements['description'] = [];
                }

                $elements['description'][$element->getAttribute('language')] = $element->nodeValue;
                break;
            case 'expression':
                $elements['expression'] = [
                    'type' => $element->getAttribute('type') ?? '',
                    'value' => $element->nodeValue,
                ];
                break;
            default:
                parent::getElement($xpath, $elements, $element);
                break;
        }
    }

    /**
     * @inheritDoc
     */
    protected function handleDelete(array $items)
    {
        // read cronjobs from database because deleting the language items requires the
        // cronjob id
        $cronjobs = $legacyCronjobs = [];
        foreach ($items as $item) {
            if (!isset($item['attributes']['name'])) {
                $legacyCronjobs[] = $item['elements']['classname'];
            } else {
                $cronjobs[] = $item['attributes']['name'];
            }
        }

        if (empty($cronjobs) && empty($legacyCronjobs)) {
            return;
        }

        $cronjobList = new CronjobList();
        $cronjobList->getConditionBuilder()->add(
            'packageID = ?',
            [$this->installation->getPackageID()]
        );

        $conditionBuilder = new PreparedStatementConditionBuilder(false, 'OR');
        if (!empty($cronjobs)) {
            $conditionBuilder->add('cronjobName IN (?)', [$cronjobs]);
        }
        if (!empty($legacyCronjobs)) {
            $conditionBuilder->add('className IN (?)', [$legacyCronjobs]);
        }
        $cronjobList->getConditionBuilder()->add($conditionBuilder, $conditionBuilder->getParameters());
        $cronjobList->readObjectIDs();

        if (!empty($cronjobList->getObjectIDs())) {
            CronjobEditor::deleteAll($cronjobList->getObjectIDs());
        }
    }

    private function getRandomExpression(string $name, string $expression): CronExpression
    {
        if (\class_exists(\Random\Engine\Xoshiro256StarStar::class, false)) {
            // Generate stable, but differing values for each (instance, cronjob) pair.
            $randomizer = new \Random\Randomizer(new \Random\Engine\Xoshiro256StarStar(
                \hash('sha256', \sprintf(
                    '%s:%s:%d:%s',
                    \WCF_UUID,
                    self::class,
                    $this->installation->getPackageID(),
                    $name
                ), true)
            ));
            $engine = static fn (int $min, int $max) => $randomizer->getInt($min, $max);
        } else {
            // A seedable engine is not available, use completely random values.
            $engine = \random_int(...);
        }

        return new CronExpression(match ($expression) {
            '@hourly' => \sprintf('%d * * * *', $engine(0, 59)),
            '@daily' => \sprintf('%d %d * * *', $engine(0, 59), $engine(0, 23)),
            '@weekly' => \sprintf('%d %d * * %d', $engine(0, 59), $engine(0, 23), $engine(0, 6)),
            '@monthly' => \sprintf('%d %d %d * *', $engine(0, 59), $engine(0, 23), $engine(1, 28)),
        });
    }

    /**
     * @inheritDoc
     */
    protected function prepareImport(array $data)
    {
        if (isset($data['elements']['expression'])) {
            $expression = match ($data['elements']['expression']['type']) {
                '' => new CronExpression($data['elements']['expression']['value']),
                'random' => $this->getRandomExpression(
                    $data['attributes']['name'],
                    $data['elements']['expression']['value']
                ),
            };

            $data['elements']['startdom'] = $expression->getExpression(CronExpression::DAY);
            $data['elements']['startdow'] = $expression->getExpression(CronExpression::WEEKDAY);
            $data['elements']['starthour'] = $expression->getExpression(CronExpression::HOUR);
            $data['elements']['startminute'] = $expression->getExpression(CronExpression::MINUTE);
            $data['elements']['startmonth'] = $expression->getExpression(CronExpression::MONTH);
        }

        return [
            'canBeDisabled' => isset($data['elements']['canbedisabled']) ? \intval($data['elements']['canbedisabled']) : 1,
            'canBeEdited' => isset($data['elements']['canbeedited']) ? \intval($data['elements']['canbeedited']) : 1,
            'className' => $data['elements']['classname'],
            'cronjobName' => $data['attributes']['name'],
            'description' => $data['elements']['description'] ?? '',
            'isDisabled' => isset($data['elements']['isdisabled']) ? \intval($data['elements']['isdisabled']) : 0,
            'options' => isset($data['elements']['options']) ? StringUtil::normalizeCsv($data['elements']['options']) : '',
            'startDom' => $data['elements']['startdom'],
            'startDow' => $data['elements']['startdow'],
            'startHour' => $data['elements']['starthour'],
            'startMinute' => $data['elements']['startminute'],
            'startMonth' => $data['elements']['startmonth'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getNameByData(array $data): string
    {
        return $data['cronjobName'];
    }

    /**
     * @inheritDoc
     */
    protected function validateImport(array $data)
    {
        // The constructor will throw if the expression is not valid.
        new CronExpression(\sprintf(
            '%s %s %s %s %s',
            $data['startMinute'],
            $data['startHour'],
            $data['startDom'],
            $data['startMonth'],
            $data['startDow']
        ));
    }

    /**
     * @inheritDoc
     */
    protected function findExistingItem(array $data)
    {
        $sql = "SELECT  *
                FROM    wcf" . WCF_N . "_" . $this->tableName . "
                WHERE   packageID = ?
                    AND cronjobName = ?";
        $parameters = [
            $this->installation->getPackageID(),
            $data['cronjobName'],
        ];

        return [
            'sql' => $sql,
            'parameters' => $parameters,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function prepareCreate(array &$data)
    {
        parent::prepareCreate($data);

        $data['nextExec'] = TIME_NOW;
    }

    /**
     * @inheritDoc
     * @since   3.1
     */
    public static function getSyncDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function addFormFields(IFormDocument $form)
    {
        /** @var IFormContainer $dataContainer */
        $dataContainer = $form->getNodeById('data');

        $dataContainer->appendChildren([
            TextFormField::create('cronjobName')
                ->objectProperty('name')
                ->label('wcf.acp.pip.cronjob.cronjobName')
                ->description('wcf.acp.pip.cronjob.cronjobName.description')
                ->required(),

            TextFormField::create('description')
                ->label('wcf.global.description')
                ->description('wcf.acp.pip.cronjob.description.description')
                ->i18n()
                ->languageItemPattern('__NONE__'),

            ClassNameFormField::create()
                ->objectProperty('classname')
                ->implementedInterface(ICronjob::class)
                ->required(),

            SingleSelectionFormField::create('expressionType')
                ->label('wcf.acp.cronjob.expressionType')
                ->required()
                ->options([
                    '' => 'wcf.acp.cronjob.expressionType.default',
                    'random' => 'wcf.acp.cronjob.expressionType.random',
                ]),
            SingleSelectionFormField::create('expression')
                ->label('wcf.acp.cronjob.expressionRandom')
                ->required()
                ->options([
                    '@hourly' => '@hourly',
                    '@daily' => '@daily',
                    '@weekly' => '@weekly',
                    '@monthly' => '@monthly',
                ])
                ->addDependency(
                    (new ValueFormFieldDependency())
                        ->fieldId('expressionType')
                        ->values(['random'])
                ),

            OptionFormField::create()
                ->description('wcf.acp.pip.cronjob.options.description'),

            BooleanFormField::create('isDisabled')
                ->objectProperty('isdisabled')
                ->label('wcf.acp.pip.cronjob.isDisabled')
                ->description('wcf.acp.pip.cronjob.isDisabled.description'),

            BooleanFormField::create('canBeEdited')
                ->objectProperty('canbeedited')
                ->label('wcf.acp.pip.cronjob.canBeEdited')
                ->description('wcf.acp.pip.cronjob.canBeEdited.description')
                ->value(true),

            BooleanFormField::create('canBeDisabled')
                ->objectProperty('canbedisabled')
                ->label('wcf.acp.pip.cronjob.canBeDisabled')
                ->description('wcf.acp.pip.cronjob.canBeDisabled.description')
                ->value(true),
        ]);

        $fieldFactory = new FieldFactory();
        foreach (['startMinute', 'startHour', 'startDom', 'startMonth', 'startDow'] as $timeProperty) {
            $dataContainer->insertBefore(
                TextFormField::create($timeProperty)
                    ->objectProperty(\strtolower($timeProperty))
                    ->label('wcf.acp.cronjob.' . $timeProperty)
                    ->description("wcf.acp.cronjob.{$timeProperty}.description")
                    ->required()
                    ->addDependency(
                        (new ValueFormFieldDependency())
                            ->fieldId('expressionType')
                            ->values([''])
                    )
                    ->addValidator(new FormFieldValidator(
                        'format',
                        static function (TextFormField $formField) use ($timeProperty, $fieldFactory) {
                            $position = match ($timeProperty) {
                                'startMinute' => CronExpression::MINUTE,
                                'startHour' => CronExpression::HOUR,
                                'startDom' => CronExpression::DAY,
                                'startMonth' => CronExpression::MONTH,
                                'startDow' => CronExpression::WEEKDAY,
                            };

                            if (!$fieldFactory->getField($position)->validate($formField->getSaveValue())) {
                                $formField->addValidationError(
                                    new FormFieldValidationError(
                                        'format',
                                        "wcf.acp.pip.cronjob.{$timeProperty}.error.format"
                                    )
                                );
                            }
                        }
                    )),
                'expression'
            );
        }
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function fetchElementData(\DOMElement $element, $saveData)
    {
        $data = [
            'className' => $element->getElementsByTagName('classname')->item(0)->nodeValue,
            'cronjobName' => $element->getAttribute('name'),
            'description' => [],
            'packageID' => $this->installation->getPackage()->packageID,
        ];

        $expressionItem = $element->getElementsByTagName('expression')->item(0);
        if ($expressionItem instanceof \DOMElement) {
            $expression = $expressionItem->nodeValue;
            $expressionType = $expressionItem->getAttribute('type');
            $cronExpression = match ($expressionType) {
                '' => new CronExpression($expression),
                'random' => $this->getRandomExpression(
                    $data['cronjobName'],
                    $expression
                ),
            };

            $data['startMinute'] = $cronExpression->getExpression(CronExpression::MINUTE);
            $data['startHour'] = $cronExpression->getExpression(CronExpression::HOUR);
            $data['startDom'] = $cronExpression->getExpression(CronExpression::DAY);
            $data['startMonth'] = $cronExpression->getExpression(CronExpression::MONTH);
            $data['startDow'] = $cronExpression->getExpression(CronExpression::WEEKDAY);
            $data['expressionType'] = $expressionType;

            if ($expressionType === 'random') {
                $data['expression'] = $expression;
            }
        } else {
            // Legacy cronjob definition, pre 6.0
            $data['startMinute'] = $element->getElementsByTagName('startminute')->item(0)->nodeValue;
            $data['startHour'] = $element->getElementsByTagName('starthour')->item(0)->nodeValue;
            $data['startDom'] = $element->getElementsByTagName('startdom')->item(0)->nodeValue;
            $data['startMonth'] = $element->getElementsByTagName('startmonth')->item(0)->nodeValue;
            $data['startDow'] = $element->getElementsByTagName('startdow')->item(0)->nodeValue;
        }

        $canBeDisabled = $element->getElementsByTagName('canbedisabled')->item(0);
        if ($canBeDisabled !== null) {
            $data['canBeDisabled'] = $canBeDisabled->nodeValue;
        }

        $descriptionElements = $element->getElementsByTagName('description');
        $descriptions = [];

        /** @var \DOMElement $description */
        foreach ($descriptionElements as $description) {
            $descriptions[$description->getAttribute('language')] = $description->nodeValue;
        }

        foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
            if (!empty($descriptions)) {
                if (isset($descriptions[$language->languageCode])) {
                    $data['description'][$language->languageID] = $descriptions[$language->languageCode];
                } elseif (isset($descriptions[''])) {
                    $data['description'][$language->languageID] = $descriptions[''];
                } elseif (isset($descriptions['en'])) {
                    $data['description'][$language->languageID] = $descriptions['en'];
                } elseif (isset($descriptions[WCF::getLanguage()->getFixedLanguageCode()])) {
                    $data['description'][$language->languageID] = $descriptions[WCF::getLanguage()->getFixedLanguageCode()];
                } else {
                    $data['description'][$language->languageID] = \reset($descriptions);
                }
            } else {
                $data['description'][$language->languageID] = '';
            }
        }

        $canBeEdited = $element->getElementsByTagName('canbeedited')->item(0);
        if ($canBeEdited !== null) {
            $data['canBeEdited'] = $canBeEdited->nodeValue;
        } elseif ($saveData) {
            $data['canBeEdited'] = 1;
        }

        $canBeDisabled = $element->getElementsByTagName('canbedisabled')->item(0);
        if ($canBeDisabled !== null) {
            $data['canBeDisabled'] = $canBeDisabled->nodeValue;
        } elseif ($saveData) {
            $data['canBeDisabled'] = 1;
        }

        $isDisabled = $element->getElementsByTagName('isdisabled')->item(0);
        if ($isDisabled !== null) {
            $data['isDisabled'] = $isDisabled->nodeValue;
        } elseif ($saveData) {
            $data['isDisabled'] = 0;
        }

        $isDisabled = $element->getElementsByTagName('options')->item(0);
        if ($isDisabled !== null) {
            $data['options'] = $isDisabled->nodeValue;
        } elseif ($saveData) {
            $data['options'] = '';
        }

        if ($saveData) {
            $descriptions = $data['description'];
            unset($data['description']);

            $data['description'] = [];
            foreach ($descriptions as $languageID => $description) {
                $data['description'][LanguageFactory::getInstance()->getLanguage($languageID)->languageCode] = $description;
            }

            unset($data['expressionType']);
            unset($data['expression']);
        }

        return $data;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    public function getElementIdentifier(\DOMElement $element)
    {
        return $element->getAttribute('name');
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function setEntryListKeys(IDevtoolsPipEntryList $entryList)
    {
        $entryList->setKeys([
            'cronjobName' => 'wcf.acp.pip.cronjob.cronjobName',
            'className' => 'wcf.form.field.className',
        ]);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $data = $form->getData();
        $formData = $form->getData()['data'];

        $cronjob = $document->createElement($this->tagName);
        $cronjob->setAttribute('name', $formData['name']);
        $cronjob->appendChild($document->createElement('classname', $formData['classname']));

        if (isset($formData['description'])) {
            if ($formData['description'] !== '') {
                $cronjob->appendChild($document->createElement('description', $formData['description']));
            }
        } elseif (isset($data['description_i18n'])) {
            /** @var \DOMElement $firstDescription */
            $firstDescription = null;
            foreach ($data['description_i18n'] as $languageItem => $description) {
                if ($description !== '') {
                    $descriptionElement = $document->createElement('description', $description);
                    $languageCode = LanguageFactory::getInstance()->getLanguage($languageItem)->languageCode;
                    if ($languageCode !== 'en') {
                        $descriptionElement->setAttribute('language', $languageCode);
                        $cronjob->appendChild($descriptionElement);
                    } elseif ($firstDescription === null) {
                        $cronjob->appendChild($descriptionElement);
                    } else {
                        // default description should be shown first
                        $cronjob->insertBefore($descriptionElement, $firstDescription);
                    }

                    if ($firstDescription === null) {
                        $firstDescription = $descriptionElement;
                    }
                }
            }
        }

        $expression = $document->createElement(
            'expression',
            $formData['expression'] ??
            \sprintf(
                '%s %s %s %s %s',
                $formData['startminute'],
                $formData['starthour'],
                $formData['startdom'],
                $formData['startmonth'],
                $formData['startdow']
            )
        );
        if ($formData['expressionType'] !== '') {
            $expression->setAttribute('type', $formData['expressionType']);
        }
        $cronjob->appendChild($expression);

        $this->appendElementChildren(
            $cronjob,
            [
                'options' => '',
                'canbeedited' => 1,
                'canbedisabled' => 1,
                'isdisabled' => 0,
            ],
            $form
        );

        return $cronjob;
    }
}
