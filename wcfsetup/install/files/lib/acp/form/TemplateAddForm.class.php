<?php

namespace wcf\acp\form;

use wcf\data\DatabaseObject;
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
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\SourceCodeFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\template\TemplateEngine;
use wcf\system\WCF;

/**
 * Shows the form for adding new templates.
 *
 * @author  Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormBuilderForm<Template>
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

        $specialTemplateGroups = $this->getSpecialTemplateGroups();
        $fixedTemplateGroup = $this->getFixedTemplateGroup();

        $templateGroupFormField = SingleSelectionFormField::create('templateGroupID')
            ->label('wcf.acp.template.group');

        $templateNameFormField = TextFormField::create('templateName')
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
                new FormFieldValidator('systemCritical', static function (TextFormField $formField) {
                    if (Template::isSystemCritical($formField->getSaveValue())) {
                        $formField->addValidationError(
                            new FormFieldValidationError(
                                'systemCritical',
                                'wcf.acp.template.name.error.systemCritical'
                            )
                        );
                    }
                })
            );

        if ($fixedTemplateGroup !== null) {
            $templateGroupFormField
                ->options([$fixedTemplateGroup->templateGroupID => $fixedTemplateGroup->templateGroupName])
                ->immutable()
                ->value($fixedTemplateGroup->templateGroupID);

            $templateNameFormField->immutable();
            if (isset($this->copiedTemplate)) {
                // Immutable fields are not read from the request.
                $templateNameFormField->value($this->copiedTemplate->templateName);
            }
        } else {
            $templateGroupFormField->options(
                new TemplateGroupNodeTree(0, \array_keys($specialTemplateGroups)),
                true
            );

            $templateNameFormField->addValidator(
                new FormFieldValidator('reservedPrefix', function (TextFormField $formField) {
                    if ($this->getRequiredTemplateGroup($formField->getSaveValue()) !== null) {
                        $formField->addValidationError(
                            new FormFieldValidationError(
                                'reservedPrefix',
                                'wcf.acp.template.name.error.reservedPrefix'
                            )
                        );
                    }
                })
            );
        }

        $templateNameFormField->addValidator(
            new FormFieldValidator('unique', function (TextFormField $formField) {
                $templateGroupIDFormField = $formField->getDocument()->getFormField('templateGroupID');
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
                    \assert($this->formObject instanceof DatabaseObject);
                    $conditionBuilder->add('templateID <> ?', [$this->formObject->getObjectID()]);
                }

                $sql = "SELECT  COUNT(*)
                        FROM    wcf1_template
                        " . $conditionBuilder;
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute($conditionBuilder->getParameters());

                if ($statement->fetchSingleColumn() > 0) {
                    $formField->addValidationError(
                        new FormFieldValidationError(
                            'notUnique',
                            'wcf.acp.template.name.error.notUnique'
                        )
                    );
                }
            })
        );

        $this->form->appendChildren([
            FormContainer::create('general')
                ->appendChildren([
                    $templateGroupFormField,
                    $templateNameFormField,
                ]),
            FormContainer::create('source')
                ->label('wcf.acp.template.source')
                ->appendChildren([
                    SourceCodeFormField::create('templateSource')
                        ->language('smarty')
                        ->label('wcf.acp.template.source'),
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
            ->addProcessor(
                new CustomFormDataProcessor(
                    'lastModificationTime',
                    static function (IFormDocument $document, array $parameters) {
                        $parameters['data']['lastModificationTime'] = \TIME_NOW;

                        return $parameters;
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
                                        FROM    wcf1_template
                                        WHERE   templateName = ?
                                            AND templateGroupID IS NULL";
                                $statement = WCF::getDB()->prepare($sql);
                                $statement->execute([
                                    $parameters['data']['templateName']
                                ]);
                                $packageID = $statement->fetchSingleColumn() ?: 1;

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
        $copy = (int)($_REQUEST['copy'] ?? 0);
        if ($copy !== 0) {
            $this->copy = $copy;
            $this->copiedTemplate = new Template($this->copy);
            if ($this->copiedTemplate->isNil() || !$this->copiedTemplate->canCopy()) {
                throw new IllegalLinkException();
            }
        }
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        if ($_POST === [] && isset($this->copiedTemplate)) {
            $templateSource = $this->form->getFormField('templateSource');
            $templateSource->value($this->copiedTemplate->getSource());

            $templateName = $this->form->getFormField('templateName');
            $templateName->value($this->copiedTemplate->templateName);
        }
    }

    #[\Override]
    public function validate()
    {
        // An unavailable selection is skipped entirely, which would save the
        // template without a template group, shadowing the default template.
        if (!$this->hasTemplateGroups()) {
            throw new IllegalLinkException();
        }

        parent::validate();
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'hasTemplateGroups' => $this->hasTemplateGroups(),
        ]);
    }

    private function hasTemplateGroups(): bool
    {
        return $this->form->getFormField('templateGroupID')->isAvailable();
    }

    /**
     * Returns the template group that the template is bound to, or `null` if
     * any regular template group may be selected.
     */
    private function getFixedTemplateGroup(): ?TemplateGroup
    {
        if (isset($this->copiedTemplate)) {
            return $this->getRequiredTemplateGroup($this->copiedTemplate->templateName);
        }

        if ($this->formObject instanceof Template) {
            return $this->getSpecialTemplateGroups()[$this->formObject->templateGroupID] ?? null;
        }

        return null;
    }

    /**
     * Returns the special template group that templates with the given name
     * must be placed in, or `null` for regular templates.
     */
    private function getRequiredTemplateGroup(string $templateName): ?TemplateGroup
    {
        $folderName = match (true) {
            \str_starts_with($templateName, 'email_') => '_wcf_email/',
            TemplateEngine::isSharedTemplate($templateName) => '_wcf_shared/',
            default => null,
        };

        if ($folderName === null) {
            return null;
        }

        return \array_find(
            $this->getSpecialTemplateGroups(),
            static fn(TemplateGroup $templateGroup) => $templateGroup->templateGroupFolderName === $folderName
        );
    }

    /**
     * @return array<int, TemplateGroup>
     */
    private function getSpecialTemplateGroups(): array
    {
        return \array_filter(
            TemplateGroupCacheBuilder::getInstance()->getData(),
            static fn(TemplateGroup $templateGroup) => $templateGroup->isImmutable()
        );
    }
}
