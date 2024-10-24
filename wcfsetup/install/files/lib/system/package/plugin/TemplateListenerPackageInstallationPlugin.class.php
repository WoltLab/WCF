<?php

namespace wcf\system\package\plugin;

use wcf\data\acp\template\ACPTemplate;
use wcf\data\acp\template\ACPTemplateList;
use wcf\data\template\listener\TemplateListenerEditor;
use wcf\data\template\listener\TemplateListenerList;
use wcf\data\template\Template;
use wcf\data\template\TemplateList;
use wcf\system\cache\builder\TemplateListenerCodeCacheBuilder;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\option\OptionFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\SourceCodeFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\user\group\option\UserGroupOptionFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes template listeners.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TemplateListenerPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements
    IGuiPackageInstallationPlugin
{
    use TXmlGuiPackageInstallationPlugin {
        setEntryData as defaultSetEntryData;
        editEntry as private traitEditEntry;
    }

    /**
     * @inheritDoc
     */
    public $className = TemplateListenerEditor::class;

    /**
     * @inheritDoc
     */
    protected function handleDelete(array $items)
    {
        $sql = "DELETE FROM wcf1_" . $this->tableName . "
                WHERE       packageID = ?
                        AND environment = ?
                        AND eventName = ?
                        AND name = ?
                        AND templateName = ?";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($items as $item) {
            $statement->execute([
                $this->installation->getPackageID(),
                $item['elements']['environment'],
                $item['elements']['eventname'],
                $item['attributes']['name'],
                $item['elements']['templatename'],
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    protected function prepareImport(array $data)
    {
        $niceValue = isset($data['elements']['nice']) ? \intval($data['elements']['nice']) : 0;
        if ($niceValue < -128) {
            $niceValue = -128;
        } elseif ($niceValue > 127) {
            $niceValue = 127;
        }

        return [
            'environment' => $data['elements']['environment'],
            'eventName' => $data['elements']['eventname'],
            'niceValue' => $niceValue,
            'name' => $data['attributes']['name'],
            'options' => isset($data['elements']['options']) ? StringUtil::normalizeCsv($data['elements']['options']) : '',
            'permissions' => isset($data['elements']['permissions']) ? StringUtil::normalizeCsv($data['elements']['permissions']) : '',
            'templateCode' => $data['elements']['templatecode'],
            'templateName' => $data['elements']['templatename'],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function findExistingItem(array $data)
    {
        $sql = "SELECT  *
                FROM    wcf1_" . $this->tableName . "
                WHERE   packageID = ?
                    AND name = ?
                    AND templateName = ?
                    AND eventName = ?
                    AND environment = ?";
        $parameters = [
            $this->installation->getPackageID(),
            $data['name'],
            $data['templateName'],
            $data['eventName'],
            $data['environment'],
        ];

        return [
            'sql' => $sql,
            'parameters' => $parameters,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function cleanup()
    {
        // clear cache immediately
        TemplateListenerCodeCacheBuilder::getInstance()->reset();
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
        $ldq = \preg_quote(WCF::getTPL()->getCompiler()->getLeftDelimiter(), '~');
        $rdq = \preg_quote(WCF::getTPL()->getCompiler()->getRightDelimiter(), '~');

        $getEvents = static function ($templateList) use ($ldq, $rdq) {
            $templateEvents = [];
            /** @var ACPTemplate|Template $template */
            foreach ($templateList as $template) {
                if (
                    \preg_match_all(
                        "~{$ldq}event\\ name\\=\\'(?<event>[\\w]+)\\'{$rdq}~",
                        $template->getSource(),
                        $matches
                    )
                ) {
                    $templates[$template->templateName] = $template->templateName;

                    foreach ($matches['event'] as $event) {
                        if (!isset($templateEvents[$template->templateName])) {
                            $templateEvents[$template->templateName] = [];
                        }

                        $templateEvents[$template->templateName][] = $event;
                    }
                }
            }

            foreach ($templateEvents as &$events) {
                \sort($events);
            }
            unset($events);

            return $templateEvents;
        };

        $templateList = new TemplateList();
        $templateList->getConditionBuilder()->add(
            'template.packageID IN (?)',
            [
                \array_merge(
                    [$this->installation->getPackage()->packageID],
                    \array_keys($this->installation->getPackage()->getAllRequiredPackages())
                ),
            ]
        );
        $templateList->getConditionBuilder()->add('template.templateGroupID IS NULL');
        $templateList->sqlOrderBy = 'template.templateName ASC';
        $templateList->readObjects();

        $templateEvents = $getEvents($templateList);

        $acpTemplateList = new ACPTemplateList();
        $acpTemplateList->getConditionBuilder()->add(
            'acp_template.packageID IN (?)',
            [
                \array_merge(
                    [$this->installation->getPackage()->packageID],
                    \array_keys($this->installation->getPackage()->getAllRequiredPackages())
                ),
            ]
        );
        $acpTemplateList->sqlOrderBy = 'acp_template.templateName ASC';
        $acpTemplateList->readObjects();

        $acpTemplateEvents = $getEvents($acpTemplateList);

        /** @var FormContainer $dataContainer */
        $dataContainer = $form->getNodeById('data');

        $dataContainer->appendChildren([
            TextFormField::create('name')
                ->label('wcf.acp.pip.templateListener.name')
                ->description('wcf.acp.pip.templateListener.name.description')
                ->required()
                ->addValidator(new FormFieldValidator('format', static function (TextFormField $formField) {
                    if (!\preg_match('~^[a-z][A-z]+$~', $formField->getValue())) {
                        $formField->addValidationError(
                            new FormFieldValidationError(
                                'format',
                                'wcf.acp.pip.templateListener.name.error.format'
                            )
                        );
                    }
                })),

            SingleSelectionFormField::create('environment')
                ->label('wcf.acp.pip.templateListener.environment')
                ->description('wcf.acp.pip.templateListener.environment.description')
                ->required()
                ->options([
                    'admin' => 'admin',
                    'user' => 'user',
                ])
                ->value('user')
                ->addValidator(new FormFieldValidator('uniqueness', function (SingleSelectionFormField $formField) {
                    /** @var TextFormField $nameField */
                    $nameField = $formField->getDocument()->getNodeById('name');

                    /** @var SingleSelectionFormField $templateNameFormField */
                    $templateNameFormField = $formField->getDocument()->getNodeById('frontendTemplateName');

                    /** @var SingleSelectionFormField $acpTemplateNameFormField */
                    $acpTemplateNameFormField = $formField->getDocument()->getNodeById('acpTemplateName');

                    if (
                        $formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE
                        || $this->editedEntry->getAttribute('name') !== $nameField->getSaveValue()
                        || $this->editedEntry->getElementsByTagName('environment')->item(0)->nodeValue !== $formField->getSaveValue()
                        || (
                            $formField->getSaveValue() === 'admin'
                            && $this->editedEntry->getElementsByTagName('templatename')->item(0)->nodeValue !== $acpTemplateNameFormField->getSaveValue()
                        )
                        || (
                            $formField->getSaveValue() === 'user'
                            && $this->editedEntry->getElementsByTagName('templatename')->item(0)->nodeValue !== $templateNameFormField->getSaveValue()
                        )
                    ) {
                        $listenerList = new TemplateListenerList();
                        $listenerList->getConditionBuilder()->add(
                            'name = ?',
                            [$nameField->getSaveValue()]
                        );

                        if ($formField->getSaveValue() === 'admin') {
                            /** @var SingleSelectionFormField $templateNameField */
                            $templateNameField = $formField->getDocument()->getNodeById('acpTemplateName');

                            /** @var SingleSelectionFormField $eventNameField */
                            $eventNameField = $formField->getDocument()->getNodeById('acp_' . $templateNameField->getSaveValue() . '_eventName');
                        } else {
                            /** @var SingleSelectionFormField $templateNameField */
                            $templateNameField = $formField->getDocument()->getNodeById('frontendTemplateName');

                            /** @var SingleSelectionFormField $eventNameField */
                            $eventNameField = $formField->getDocument()->getNodeById($templateNameField->getSaveValue() . '_eventName');
                        }

                        $templateName = $templateNameField->getSaveValue();
                        $eventName = $eventNameField->getSaveValue();

                        $listenerList->getConditionBuilder()->add('templateName = ?', [$templateName]);
                        $listenerList->getConditionBuilder()->add('eventName = ?', [$eventName]);
                        $listenerList->getConditionBuilder()->add('environment = ?', [$formField->getSaveValue()]);

                        if ($listenerList->countObjects() > 0) {
                            $nameField->addValidationError(
                                new FormFieldValidationError(
                                    'notUnique',
                                    'wcf.acp.pip.templateListener.name.error.notUnique'
                                )
                            );
                        }
                    }
                })),

            SingleSelectionFormField::create('frontendTemplateName')
                ->objectProperty('templatename')
                ->label('wcf.acp.pip.templateListener.templateName')
                ->description('wcf.acp.pip.templateListener.templateName.description')
                ->required()
                ->options(\array_combine(\array_keys($templateEvents), \array_keys($templateEvents)))
                ->filterable()
                ->addDependency(
                    ValueFormFieldDependency::create('environment')
                        ->fieldId('environment')
                        ->values(['user'])
                ),

            SingleSelectionFormField::create('acpTemplateName')
                ->objectProperty('templatename')
                ->label('wcf.acp.pip.templateListener.templateName')
                ->description('wcf.acp.pip.templateListener.templateName.description')
                ->required()
                ->options(\array_combine(\array_keys($acpTemplateEvents), \array_keys($acpTemplateEvents)))
                ->filterable()
                ->addDependency(
                    ValueFormFieldDependency::create('environment')
                        ->fieldId('environment')
                        ->values(['admin'])
                ),
        ]);

        /** @var SingleSelectionFormField $frontendTemplateName */
        $frontendTemplateName = $form->getNodeById('frontendTemplateName');
        foreach ($templateEvents as $templateName => $events) {
            $dataContainer->appendChild(
                SingleSelectionFormField::create($templateName . '_eventName')
                    ->objectProperty('eventname')
                    ->label('wcf.acp.pip.templateListener.eventName')
                    ->description('wcf.acp.pip.templateListener.eventName.description')
                    ->required()
                    ->options(\array_combine($events, $events))
                    ->addDependency(
                        ValueFormFieldDependency::create('templateName')
                            ->field($frontendTemplateName)
                            ->values([$templateName])
                    )
            );
        }

        /** @var SingleSelectionFormField $acpTemplateName */
        $acpTemplateName = $form->getNodeById('acpTemplateName');
        foreach ($acpTemplateEvents as $templateName => $events) {
            $dataContainer->appendChild(
                SingleSelectionFormField::create('acp_' . $templateName . '_eventName')
                    ->objectProperty('eventname')
                    ->label('wcf.acp.pip.templateListener.eventName')
                    ->description('wcf.acp.pip.templateListener.eventName.description')
                    ->required()
                    ->options(\array_combine($events, $events))
                    ->addDependency(
                        ValueFormFieldDependency::create('acpTemplateName')
                            ->field($acpTemplateName)
                            ->values([$templateName])
                    )
            );
        }

        $dataContainer->appendChildren([
            SourceCodeFormField::create('templateCode')
                ->objectProperty('templatecode')
                ->label('wcf.acp.pip.templateListener.templateCode')
                ->description('wcf.acp.pip.templateListener.templateCode.description')
                ->required()
                ->language('smartymixed'),

            IntegerFormField::create('niceValue')
                ->objectProperty('nice')
                ->label('wcf.acp.pip.templateListener.niceValue')
                ->description('wcf.acp.pip.templateListener.niceValue.description')
                ->nullable()
                ->minimum(-128)
                ->maximum(127),

            OptionFormField::create()
                ->description('wcf.acp.pip.templateListener.options.description')
                ->packageIDs(\array_merge(
                    [$this->installation->getPackage()->packageID],
                    \array_keys($this->installation->getPackage()->getAllRequiredPackages())
                ))
                ->available($form->getFormMode() !== IFormDocument::FORM_MODE_CREATE),

            UserGroupOptionFormField::create()
                ->description('wcf.acp.pip.templateListener.permissions.description')
                ->packageIDs(\array_merge(
                    [$this->installation->getPackage()->packageID],
                    \array_keys($this->installation->getPackage()->getAllRequiredPackages())
                ))
                ->available($form->getFormMode() !== IFormDocument::FORM_MODE_CREATE),
        ]);

        // ensure proper normalization of template code
        $form->getDataHandler()->addProcessor(new CustomFormDataProcessor(
            'templateCode',
            static function (IFormDocument $document, array $parameters) {
                $parameters['data']['templatecode'] = StringUtil::unifyNewlines(StringUtil::escapeCDATA($parameters['data']['templatecode']));

                return $parameters;
            }
        ));
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function fetchElementData(\DOMElement $element, $saveData)
    {
        $data = [
            'environment' => $element->getElementsByTagName('environment')->item(0)->nodeValue,
            'eventName' => $element->getElementsByTagName('eventname')->item(0)->nodeValue,
            'name' => $element->getAttribute('name'),
            'packageID' => $this->installation->getPackage()->packageID,
            'templateCode' => $element->getElementsByTagName('templatecode')->item(0)->nodeValue,
            'templateName' => $element->getElementsByTagName('templatename')->item(0)->nodeValue,
        ];

        $nice = $element->getElementsByTagName('nice')->item(0);
        if ($nice !== null) {
            $data['niceValue'] = $nice->nodeValue;
        } elseif ($saveData) {
            $data['niceValue'] = 0;
        }

        foreach (['options', 'permissions'] as $elementName) {
            $optionalElement = $element->getElementsByTagName($elementName)->item(0);
            if ($optionalElement !== null) {
                $data[$elementName] = $optionalElement->nodeValue;
            } elseif ($saveData) {
                $data[$elementName] = '';
            }
        }

        return $data;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    public function getElementIdentifier(\DOMElement $element)
    {
        return \sha1(
            $element->getElementsByTagName('templatename')->item(0)->nodeValue . '/'
            . $element->getElementsByTagName('eventname')->item(0)->nodeValue . '/'
            . $element->getElementsByTagName('environment')->item(0)->nodeValue . '/'
            . $element->getAttribute('name')
        );
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    public function setEntryData($identifier, IFormDocument $document)
    {
        if ($this->defaultSetEntryData($identifier, $document)) {
            $options = $document->getNodeById('options');
            \assert($options instanceof OptionFormField);
            $permissions = $document->getNodeById('permissions');
            \assert($permissions instanceof UserGroupOptionFormField);
    
            if (!$options->getValue()) {
                $options->available(false);
            }
            if (!$permissions->getValue()) {
                $permissions->available(false);
            }

            $data = $this->getElementData($this->getElementByIdentifier($this->getProjectXml(), $identifier));

            switch ($data['environment']) {
                case 'admin':
                    $templateName = $document->getNodeById('acpTemplateName');
                    $eventName = $document->getNodeById('acp_' . $data['templateName'] . '_eventName');
                    break;

                case 'user':
                    $templateName = $document->getNodeById('frontendTemplateName');
                    $eventName = $document->getNodeById($data['templateName'] . '_eventName');
                    break;

                default:
                    throw new \LogicException("Unknown environment '{$data['environment']}'.");
            }

            \assert($templateName instanceof SingleSelectionFormField);
            \assert($eventName instanceof SingleSelectionFormField);
            $templateName->value($data['templateName']);
            $eventName->value($data['eventName']);

            return true;
        }

        return false;
    }

    /**
     * Shows options and permissions if already specified.
     */
    public function editEntry(IFormDocument $form, $identifier)
    {
        $options = $form->getNodeById('options');
        \assert($options instanceof OptionFormField);
        $permissions = $form->getNodeById('permissions');
        \assert($permissions instanceof UserGroupOptionFormField);

        $result = $this->traitEditEntry($form, $identifier);

        if (!$options->getValue()) {
            $options->available(false);
        }
        if (!$permissions->getValue()) {
            $permissions->available(false);
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function setEntryListKeys(IDevtoolsPipEntryList $entryList)
    {
        $entryList->setKeys([
            'name' => 'wcf.acp.pip.templateListener.name',
            'templateName' => 'wcf.acp.pip.templateListener.templateName',
            'eventName' => 'wcf.acp.pip.templateListener.eventName',
            'environment' => 'wcf.acp.pip.templateListener.environment',
        ]);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $data = $form->getData()['data'];

        $listener = $document->createElement($this->tagName);
        $listener->setAttribute('name', $data['name']);

        $this->appendElementChildren(
            $listener,
            [
                'environment',
                'templatename',
                'eventname',
                'templatecode' => [
                    'cdata' => true,
                ],
                'nice' => 0,
                'options' => '',
                'permissions' => '',
            ],
            $form
        );

        return $listener;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareDeleteXmlElement(\DOMElement $element)
    {
        $templateListener = $element->ownerDocument->createElement($this->tagName);
        $templateListener->setAttribute('name', $element->getAttribute('name'));

        foreach (['environment', 'templatename', 'eventname'] as $childElement) {
            $templateListener->appendChild($element->ownerDocument->createElement(
                $childElement,
                $element->getElementsByTagName($childElement)->item(0)->nodeValue
            ));
        }

        return $templateListener;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function deleteObject(\DOMElement $element)
    {
        $elements = [];
        foreach (['environment', 'templatename', 'eventname'] as $childElement) {
            $elements[$childElement] = $element->getElementsByTagName($childElement)->item(0)->nodeValue;
        }

        $this->handleDelete([
            [
                'attributes' => ['name' => $element->getAttribute('name')],
                'elements' => $elements,
            ],
        ]);
    }
}
