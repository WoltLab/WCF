<?php

namespace wcf\system\package\plugin;

use wcf\data\bbcode\media\provider\BBCodeMediaProviderEditor;
use wcf\system\bbcode\media\provider\IBBCodeMediaProvider;
use wcf\system\cache\builder\BBCodeMediaProviderCacheBuilder;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes media providers.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 */
class MediaProviderPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements
    IGuiPackageInstallationPlugin,
    IUniqueNameXMLPackageInstallationPlugin
{
    use TXmlGuiPackageInstallationPlugin;

    /**
     * @inheritDoc
     */
    public $className = BBCodeMediaProviderEditor::class;

    /**
     * @inheritDoc
     */
    public $tagName = 'provider';

    /**
     * @inheritDoc
     */
    protected function handleDelete(array $items)
    {
        $sql = "DELETE FROM wcf1_" . $this->tableName . "
                WHERE       packageID = ?
                        AND name = ?";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($items as $item) {
            $statement->execute([
                $this->installation->getPackageID(),
                $item['attributes']['name'],
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    protected function prepareImport(array $data)
    {
        return [
            'name' => $data['attributes']['name'],
            'html' => $data['elements']['html'] ?? '',
            'className' => $data['elements']['className'] ?? '',
            'title' => $data['elements']['title'],
            'regex' => StringUtil::unifyNewlines($data['elements']['regex']),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getNameByData(array $data): string
    {
        return $data['name'];
    }

    /**
     * @inheritDoc
     */
    protected function findExistingItem(array $data)
    {
        $sql = "SELECT  *
                FROM    wcf1_" . $this->tableName . "
                WHERE   packageID = ?
                    AND name = ?";
        $parameters = [
            $this->installation->getPackageID(),
            $data['name'],
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
        BBCodeMediaProviderCacheBuilder::getInstance()->reset();
    }

    /**
     * @inheritDoc
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
        /** @var FormContainer $dataContainer */
        $dataContainer = $form->getNodeById('data');

        $dataContainer->appendChildren([
            TextFormField::create('name')
                ->label('wcf.acp.pip.mediaProvider.name')
                ->description('wcf.acp.pip.mediaProvider.name.description')
                ->addValidator(new FormFieldValidator('format', static function (TextFormField $formField) {
                    if (!\preg_match('~^[a-z][A-z0-9-]+$~', $formField->getSaveValue())) {
                        $formField->addValidationError(
                            new FormFieldValidationError(
                                'format',
                                'wcf.acp.pip.mediaProvider.name.error.format'
                            )
                        );
                    }
                })),

            TitleFormField::create()
                ->required(),

            MultilineTextFormField::create('regex')
                ->label('wcf.acp.pip.mediaProvider.regex')
                ->description('wcf.acp.pip.mediaProvider.regex.description')
                ->required()
                ->addValidator(new FormFieldValidator('format', static function (MultilineTextFormField $formField) {
                    $value = \explode("\n", StringUtil::unifyNewlines($formField->getValue()));

                    $invalidRegex = [];
                    foreach ($value as $regex) {
                        if (!Regex::compile($regex)->isValid()) {
                            $invalidRegex[] = $regex;
                        }
                    }

                    if (!empty($invalidRegex)) {
                        $formField->addValidationError(
                            new FormFieldValidationError(
                                'format',
                                'wcf.acp.pip.mediaProvider.regex.error.format',
                                ['invalidRegex' => $invalidRegex]
                            )
                        );
                    }
                })),

            ClassNameFormField::create()
                ->implementedInterface(IBBCodeMediaProvider::class),

            MultilineTextFormField::create('html')
                ->label('wcf.acp.pip.mediaProvider.html')
                ->description('wcf.acp.pip.mediaProvider.html.description')
                ->addValidator(new FormFieldValidator('className', static function (MultilineTextFormField $formField) {
                    /** @var ClassNameFormField $className */
                    $className = $formField->getDocument()->getNodeById('className');

                    if ($formField->getSaveValue() && $className->getSaveValue()) {
                        $formField->addValidationError(
                            new FormFieldValidationError(
                                'className',
                                'wcf.acp.pip.mediaProvider.html.error.className'
                            )
                        );
                    }
                }))
                ->addValidator(new FormFieldValidator(
                    'noClassName',
                    static function (MultilineTextFormField $formField) {
                        /** @var ClassNameFormField $className */
                        $className = $formField->getDocument()->getNodeById('className');

                        if ($formField->getSaveValue() === '' && $className->getSaveValue() === '') {
                            $formField->addValidationError(
                                new FormFieldValidationError(
                                    'noClassName',
                                    'wcf.acp.pip.mediaProvider.html.error.noClassName'
                                )
                            );
                        }
                    }
                )),
        ]);

        $form->getDataHandler()->addProcessor(new CustomFormDataProcessor(
            'unifyNewlines',
            static function (IFormDocument $document, array $parameters) {
                $parameters['data']['regex'] = StringUtil::unifyNewlines(StringUtil::escapeCDATA($parameters['data']['regex']));
                $parameters['data']['html'] = StringUtil::unifyNewlines(StringUtil::escapeCDATA($parameters['data']['html']));

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
            'name' => $element->getAttribute('name'),
            'packageID' => $this->installation->getPackage()->packageID,
            'title' => $element->getElementsByTagName('title')->item(0)->nodeValue,
            'regex' => $element->getElementsByTagName('regex')->item(0)->nodeValue,
        ];

        $html = $element->getElementsByTagName('html')->item(0);
        if ($html !== null) {
            $data['html'] = $html->nodeValue;
        } elseif ($saveData) {
            $data['html'] = '';
        }

        $className = $element->getElementsByTagName('className')->item(0);
        if ($className !== null) {
            $data['className'] = $className->nodeValue;
        } elseif ($saveData) {
            $data['className'] = '';
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
            'name' => 'wcf.acp.pip.mediaProvider.name',
            'title' => 'wcf.global.title',
        ]);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $data = $form->getData()['data'];

        $provider = $document->createElement($this->tagName);
        $provider->setAttribute('name', $data['name']);

        $this->appendElementChildren(
            $provider,
            [
                'title',
                'regex' => [
                    'cdata' => true,
                ],
                'html' => [
                    'cdata' => true,
                    'defaultValue' => '',
                ],
                'className' => '',
            ],
            $form
        );

        return $provider;
    }
}
