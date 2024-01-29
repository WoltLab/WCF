<?php

namespace wcf\acp\form;

use wcf\data\IStorableObject;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\data\template\group\TemplateGroup;
use wcf\data\template\group\TemplateGroupNodeTree;
use wcf\data\template\Template;
use wcf\data\template\TemplateAction;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\cache\builder\TemplateGroupCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\data\processor\VoidFormDataProcessor;
use wcf\system\form\builder\field\HiddenFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\TemplateFormNode;
use wcf\system\template\TemplateEngine;
use wcf\system\WCF;

/**
 * Shows the form for adding new templates.
 *
 * @author  Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TemplateAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.template.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.template.canManageTemplate'];

    /**
     * @inheritDoc
     */
    public $objectActionClass = TemplateAction::class;

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = TemplateEditForm::class;

    /**
     * id of copied template
     * @var int
     */
    public int $copy = 0;

    /**
     * copied template object
     * @var Template
     */
    public Template $copiedTemplate;

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $this->form->appendChildren([
            FormContainer::create('general')
                ->appendChildren([
                    SingleSelectionFormField::create('templateGroupID')
                        ->label('wcf.acp.template.group')
                        ->options(new TemplateGroupNodeTree(), true)
                        ->addValidator(
                            new FormFieldValidator('sharedTemplate', function (SingleSelectionFormField $formField) {
                                $templateGroupID = $formField->getSaveValue();
                                $templateGroup = TemplateGroupCacheBuilder::getInstance()->getData(
                                    [],
                                    $templateGroupID
                                );
                                \assert($templateGroup instanceof TemplateGroup);

                                $tplNameFormField = $formField->getDocument()->getNodeById('templateName');
                                \assert($tplNameFormField instanceof TextFormField);
                                $tplName = $tplNameFormField->getSaveValue();

                                if (
                                    TemplateEngine::isSharedTemplate($tplName)
                                    && $templateGroup->templateGroupFolderName !== '_wcf_shared/'
                                ) {
                                    $formField->addValidationError(
                                        new FormFieldValidationError(
                                            'invalid',
                                            'wcf.acp.template.group.error.notShared'
                                        )
                                    );
                                } elseif (
                                    !TemplateEngine::isSharedTemplate($tplName)
                                    && $templateGroup->templateGroupFolderName === '_wcf_shared/'
                                ) {
                                    $formField->addValidationError(
                                        new FormFieldValidationError(
                                            'invalid',
                                            'wcf.acp.template.group.error.shared'
                                        )
                                    );
                                }
                            })
                        ),
                    TextFormField::create('templateName')
                        ->required()
                        ->label('wcf.global.name')
                        ->addValidator(
                            new FormFieldValidator('fileName', function (TextFormField $formField) {
                                $tplName = $formField->getSaveValue();
                                if (!\preg_match('/^[a-z0-9_\-]+$/i', $tplName)) {
                                    $formField->addValidationError(
                                        new FormFieldValidationError('invalid', 'wcf.acp.template.name.error.invalid')
                                    );
                                }
                            })
                        )
                        ->addValidator(
                            new FormFieldValidator('unique', function (TextFormField $formField) {
                                $templateGroupIDFormField = $formField->getDocument()->getNodeById('templateGroupID');
                                \assert($templateGroupIDFormField instanceof SingleSelectionFormField);

                                $conditionBuilder = new PreparedStatementConditionBuilder();
                                $conditionBuilder->add('templateName = ?', [$formField->getSaveValue()]);
                                $conditionBuilder->add('templateGroupID = ?', [
                                    $templateGroupIDFormField->getSaveValue()
                                ]);

                                if (isset($this->copiedTemplate)) {
                                    $conditionBuilder->add(
                                        '(packageID = ? OR application = ?)',
                                        [$this->copiedTemplate->packageID, $this->copiedTemplate->application]
                                    );
                                } else {
                                    $conditionBuilder->add('packageID = ?', [1]);
                                }

                                if ($this->formAction === 'edit') {
                                    $conditionBuilder->add('templateID <> ?', [$this->formObject->getObjectID()]);
                                }

                                $sql = "SELECT  COUNT(*)
                                        FROM    wcf" . WCF_N . "_template
                                        " . $conditionBuilder;
                                $statement = WCF::getDB()->prepareStatement($sql);
                                $statement->execute($conditionBuilder->getParameters());

                                if ($statement->fetchSingleColumn()) {
                                    $formField->addValidationError(
                                        new FormFieldValidationError(
                                            'notUnique',
                                            'wcf.acp.template.name.error.notUnique'
                                        )
                                    );
                                }
                            })
                        ),
                ]),
            FormContainer::create('source')
                ->label('wcf.acp.template.source')
                ->appendChildren([
                    MultilineTextFormField::create('templateSource')
                        ->label('wcf.acp.template.source'),
                    TemplateFormNode::create('codemirror')
                        ->templateName('shared_codemirror')
                        ->variables([
                            'codemirrorMode' => 'smarty',
                            'codemirrorSelector' => '#templateSource'
                        ])
                ]),
            HiddenFormField::create('copy')
                ->value($this->copy),
        ]);

        $this->form->getDataHandler()
            ->addProcessor(
                new CustomFormDataProcessor(
                    'source',
                    static function (IFormDocument $document, array $parameters) {
                        $parameters['source'] = $parameters['data']['templateSource'];

                        return $parameters;
                    },
                    function (IFormDocument $document, array $data, IStorableObject $object) {
                        \assert($object instanceof Template);
                        $data['templateSource'] = $object->getSource();

                        return $data;
                    }
                )
            )
            ->addProcessor(new VoidFormDataProcessor('copy'))
            ->addProcessor(new VoidFormDataProcessor('templateSource'));

        if ($this->formAction === 'create') {
            $this->form->getDataHandler()
                ->addProcessor(
                    new CustomFormDataProcessor(
                        'application',
                        function (IFormDocument $document, array $parameters) {
                            if (isset($this->copiedTemplate)) {
                                $parameters['data']['application'] = $this->copiedTemplate->application;
                            } else {
                                $sql = "SELECT  packageID
                                    FROM    wcf" . WCF_N . "_template
                                    WHERE   templateName = ?
                                        AND templateGroupID IS NULL";
                                $statement = WCF::getDB()->prepareStatement($sql);
                                $statement->execute([
                                    $parameters['data']['templateName']
                                ]);
                                $packageID = $statement->fetchSingleRow() ?: 1;

                                $parameters['data']['application'] = Package::getAbbreviation(
                                    PackageCache::getInstance()->getPackage($packageID)->package
                                );
                            }

                            return $parameters;
                        }
                    )
                );
        }
    }

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();
        if (!empty($_REQUEST['copy'])) {
            $this->copy = \intval($_REQUEST['copy']);
            $this->copiedTemplate = new Template($this->copy);
            if (!$this->copiedTemplate->templateID) {
                throw new IllegalLinkException();
            }
        }
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        if ($_POST === [] && isset($this->copiedTemplate)) {
            $this->form->getNodeById('templateSource')->value($this->copiedTemplate->getSource());
            $this->form->getNodeById('templateName')->value($this->copiedTemplate->templateName);
        }
    }
}
